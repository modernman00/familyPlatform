<?php

declare(strict_types=1);

namespace App\services;

use App\classes\PushNotificationClass;
use PDO;
use Src\Exceptions\ValidationException;

/**
 * CommentThreadService
 *
 * One-level threaded replies and @mentions on post comments. A reply to a reply is
 * attached to the top-level comment, so threads never nest deeper than one level and
 * the feed renders them as a flat, indented list.
 *
 * Mentions are resolved server-side from plain "@First Last" text against the post's
 * family roster, so a client can never name someone outside the family.
 */
final class CommentThreadService
{
    /** Cap per comment so a pasted roster cannot fan out into a notification storm. */
    public const MAX_MENTIONS = 10;

    /**
     * Resolve the top-level comment a reply should hang under.
     *
     * @return array{root: int, parentAuthorId: string}
     * @throws ValidationException when the parent is missing, deleted or on another post
     */
    public static function resolveParent(PDO $pdo, int $parentNo, string $postNo): array
    {
        $stmt = $pdo->prepare(
            'SELECT comment_no, parent_comment_no, post_no, id
             FROM comment
             WHERE comment_no = :no AND date_deleted IS NULL
             LIMIT 1'
        );
        $stmt->execute(['no' => $parentNo]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($parent) || (string) ($parent['post_no'] ?? '') !== $postNo) {
            throw new ValidationException('The comment you are replying to is no longer available.');
        }

        $root = (int) ($parent['parent_comment_no'] ?? 0) ?: (int) $parent['comment_no'];

        return ['root' => $root, 'parentAuthorId' => (string) ($parent['id'] ?? '')];
    }

    /**
     * Find which family members a comment mentions with "@First Last".
     *
     * @param array<int, array<string, mixed>> $members rows with id, firstName, lastName
     * @return list<string> unique member ids, never the author, capped at MAX_MENTIONS
     */
    public static function extractMentionedIds(string $text, array $members, string $authorId): array
    {
        if (!str_contains($text, '@')) {
            return [];
        }

        $ids = [];
        foreach ($members as $member) {
            $id = (string) ($member['id'] ?? '');
            $first = trim((string) ($member['firstName'] ?? ''));
            $last = trim((string) ($member['lastName'] ?? ''));
            if ($id === '' || $id === $authorId || $first === '' || $last === '' || isset($ids[$id])) {
                continue;
            }

            $pattern = '/(?<![\w@])@' . preg_quote($first, '/') . '\s+' . preg_quote($last, '/') . '(?![\w])/iu';
            if (preg_match($pattern, $text) === 1) {
                $ids[$id] = true;
                if (count($ids) >= self::MAX_MENTIONS) {
                    break;
                }
            }
        }

        return array_map('strval', array_keys($ids));
    }

    /**
     * Notify the parent comment's author and everyone mentioned (once each, never the
     * commenter). A person who is both is told about the mention. Failures are logged,
     * never thrown: a comment must post even when notifications are down.
     *
     * @param list<string> $mentionedIds
     * @return list<string> ids that were notified
     */
    public static function notify(
        PDO $pdo,
        string $authorId,
        string $authorName,
        string $postNo,
        array $mentionedIds,
        ?string $parentAuthorId,
        ?int $commentNo = null
    ): array {
        $targets = [];
        if ($parentAuthorId !== null && $parentAuthorId !== '') {
            $targets[$parentAuthorId] = "{$authorName} replied to your comment";
        }
        foreach ($mentionedIds as $id) {
            $targets[$id] = "{$authorName} mentioned you in a comment";
        }
        unset($targets[$authorId]);

        $cleanPostNo = (int) $postNo;
        $baseUrl = rtrim((string) (getenv('APP_URL') ?: (getenv('MIX_APP_URL') ?: '')), '/');
        $base = $baseUrl !== '' ? $baseUrl : '';
        if ($commentNo) {
            $cleanCommentNo = (int) $commentNo;
            $url = "{$base}/profilePage?post_no={$cleanPostNo}&comment_no={$cleanCommentNo}#comment{$cleanCommentNo}";
        } else {
            $url = "{$base}/profilePage?post_no={$cleanPostNo}#post{$cleanPostNo}";
        }
        $notified = [];

        foreach ($targets as $targetId => $message) {
            $targetId = (string) $targetId;
            try {
                $pdo->prepare(
                    "INSERT INTO notification
                     (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                     VALUES (?, ?, ?, ?, 'Post', ?, ?, 'new', NOW())"
                )->execute([$authorId, $targetId, $authorName, $message, $message, $url]);

                PushNotificationClass::sendPushNotification(
                    userId: $targetId,
                    message: $message,
                    url: $url,
                    title: 'Family Platform',
                    tag: 'comment-' . $postNo
                );
                $notified[] = $targetId;
            } catch (\Throwable $e) {
                error_log('[CommentThreadService] notify failed for ' . $targetId . ': ' . $e->getMessage());
            }
        }

        return $notified;
    }
}
