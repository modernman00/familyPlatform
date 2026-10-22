<?php

declare(strict_types=1);

namespace App\services;

use App\model\AllMembersData;
use App\model\PollData;
use App\model\Post;
use App\controller\members\PostMessage;
use App\services\NotificationOrchestrator;
use Src\Db;
use PDO;

/**
 * FamilyTriviaService
 *
 * Powers Sunday Family Trivia & "Guess Who" challenges for FamilyPlatform.
 * Generates ego-centric family trivia from ancestral records, creates weekly polls,
 * and sparks multi-generational conversation across the family network.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FamilyTriviaService
{
    /**
     * Check if an automated weekly trivia has already been published for this family in the last 7 days.
     */
    public static function hasActiveWeeklyTrivia(string $familyCode): bool
    {
        $db = Db::connect2();
        $stmt = $db->prepare(
            "SELECT post_no 
             FROM post 
             WHERE postFamCode = ? 
               AND postMessage LIKE '%Weekly Family Trivia%' 
               AND date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
             LIMIT 1"
        );
        $stmt->execute([$familyCode]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Generate and publish a dynamic heritage trivia challenge for a family.
     *
     * @param string $familyCode Target family code
     * @param string|null $actorUserId Optional user ID initiating or authoring
     * @return array{
     *     post_no: int,
     *     question: string,
     *     options: array<int, string>,
     *     category: string
     * }|null Returns null if an active trivia already exists
     */
    public static function generateWeeklyHeritageTrivia(string $familyCode, ?string $actorUserId = null): ?array
    {
        if (self::hasActiveWeeklyTrivia($familyCode)) {
            return null;
        }

        $db = Db::connect2();

        // 1. Fetch family nodes to formulate ground-truth questions
        $stmt = $db->prepare(
            'SELECT id, user_id, first_name, last_name, maiden_name, gender, birth_date, birth_year, birth_place, generation_level
             FROM family_nodes
             WHERE family_code = ?
             ORDER BY generation_level ASC, id ASC'
        );
        $stmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $nodes */
        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $triviaData = self::synthesizeTriviaQuestion($familyCode, $nodes);

        // 2. Insert the post into the family feed
        $authorId = !empty($actorUserId) ? $actorUserId : 'HERITAGE_BOT';
        $authorName = 'FamilyPlatform Heritage';
        $postMessage = "🧠 Weekly Family Trivia Challenge!\n\n" . $triviaData['question'] . "\n\nCast your vote below and share your family memories in the comments!";

        $postStmt = $db->prepare(
            'INSERT INTO post (id, fullName, postMessage, postFamCode, post_time, post_status, date_created)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $postTime = (string)milliSeconds();
        $postStmt->execute([
            $authorId,
            $authorName,
            $postMessage,
            $familyCode,
            $postTime,
            'publish',
        ]);

        $postNo = (int)$db->lastInsertId();

        // 3. Attach the poll with options
        PollData::createPoll($postNo, $triviaData['question'], $triviaData['options']);

        // Explicitly update status as published
        Post::updatePostByStatusAsPublished($postNo);

        // 4. Resolve featured member user_id if not directly on the family_node
        if (empty($triviaData['featured_user_id']) && !empty($triviaData['featured_first_name']) && !empty($triviaData['featured_last_name'])) {
            $normFam = strtoupper(trim(str_replace('#', '', $familyCode)));
            $uStmt = $db->prepare("
                SELECT id FROM personal 
                WHERE LOWER(firstName) = LOWER(?) 
                  AND LOWER(lastName) = LOWER(?) 
                  AND UPPER(TRIM(REPLACE(famCode, '#', ''))) = ? 
                LIMIT 1
            ");
            $uStmt->execute([$triviaData['featured_first_name'], $triviaData['featured_last_name'], $normFam]);
            $uid = $uStmt->fetchColumn();
            if ($uid) {
                $triviaData['featured_user_id'] = (string)$uid;
            }
        }

        // 5. Tag featured member in post_tags
        if (!empty($triviaData['featured_user_id'])) {
            try {
                $tagStmt = $db->prepare("INSERT IGNORE INTO post_tags (post_no, user_id, tagged_by, created_at) VALUES (?, ?, ?, NOW())");
                $tagStmt->execute([$postNo, $triviaData['featured_user_id'], $authorId]);
            } catch (\Throwable $e) {
                error_log('[FamilyTriviaService] post_tags tag insert failed: ' . $e->getMessage());
            }
        }

        // 6. Notify the featured member & the family network across all channels
        self::notifyFamilyOfTriviaPost($familyCode, $postNo, $triviaData, $authorId);

        return [
            'post_no'          => $postNo,
            'question'         => $triviaData['question'],
            'options'          => $triviaData['options'],
            'category'         => $triviaData['category'],
            'featured_user_id' => $triviaData['featured_user_id'] ?? null,
            'featured_name'    => $triviaData['featured_name'] ?? null,
        ];
    }

    /**
     * Dispatch multi-channel notifications (In-App, WebPush, Email, Realtime Pusher)
     * for newly published family trivia.
     *
     * @param string $familyCode
     * @param int $postNo
     * @param array{
     *     question: string,
     *     options: array<int, string>,
     *     category: string,
     *     featured_user_id?: string|null,
     *     featured_name?: string|null
     * } $triviaData
     * @param string $authorId
     */
    private static function notifyFamilyOfTriviaPost(
        string $familyCode,
        int $postNo,
        array $triviaData,
        string $authorId
    ): void {
        $cleanFam = strtoupper(trim(str_replace('#', '', $familyCode)));
        $featuredUid = !empty($triviaData['featured_user_id']) ? (string)$triviaData['featured_user_id'] : '';
        $postUrl = '/profilePage#post' . $postNo;

        // 1. Featured Member Spotlight Notification (In-App & OS WebPush)
        if ($featuredUid !== '') {
            try {
                NotificationOrchestrator::dispatch(
                    userId: $featuredUid,
                    category: 'trivia',
                    priority: 'high',
                    title: "🌟 You are featured in this week's Family Trivia Challenge!",
                    body: "{$triviaData['question']} See who in the family knows you best! Cast your vote and join the debate.",
                    actionUrl: $postUrl,
                    tag: "trivia-featured-{$postNo}",
                    familyCode: $cleanFam,
                    metadata: [
                        'sender_id' => $authorId,
                        'sender_name' => 'FamilyPlatform Heritage',
                        'post_no' => $postNo,
                        'featured' => true,
                    ]
                );
            } catch (\Throwable $e) {
                error_log('[FamilyTriviaService] Featured member notification failed: ' . $e->getMessage());
            }
        }

        // 2. Family Network Multi-Channel Notification (In-App & OS WebPush)
        try {
            $familyMembers = AllMembersData::AllMembersEmailByFamCode($familyCode, $authorId);
            foreach ($familyMembers as $member) {
                $targetUid = is_scalar($member['id'] ?? null) ? (string)$member['id'] : '';
                if ($targetUid === '' || ($featuredUid !== '' && $targetUid === $featuredUid)) {
                    continue;
                }

                NotificationOrchestrator::dispatch(
                    userId: $targetUid,
                    category: 'trivia',
                    priority: 'medium',
                    title: "🧠 Weekly Family Trivia Challenge!",
                    body: "{$triviaData['question']} Cast your vote below and debate in the comments!",
                    actionUrl: $postUrl,
                    tag: "trivia-post-{$postNo}",
                    familyCode: $cleanFam,
                    metadata: [
                        'sender_id' => $authorId,
                        'sender_name' => 'FamilyPlatform Heritage',
                        'post_no' => $postNo,
                    ]
                );
            }
        } catch (\Throwable $e) {
            error_log('[FamilyTriviaService] Family network notification failed: ' . $e->getMessage());
        }

        // 3. Realtime Pusher Feed Broadcast & Email Dispatch
        try {
            PostMessage::getNewPostPusher($postNo);
            PostMessage::getNewPostAndEmail($postNo);
        } catch (\Throwable $e) {
            error_log('[FamilyTriviaService] Pusher/Email dispatch failed: ' . $e->getMessage());
        }
    }

    /**
     * Synthesize an authentic trivia question from family nodes.
     *
     * @param string $familyCode
     * @param array<int, array<string, mixed>> $nodes
     * @return array{
     *     question: string,
     *     options: array<int, string>,
     *     category: string,
     *     featured_node_id?: int,
     *     featured_user_id?: string|null,
     *     featured_first_name?: string,
     *     featured_last_name?: string,
     *     featured_name?: string
     * }
     */
    private static function synthesizeTriviaQuestion(string $familyCode, array $nodes): array
    {
        // Category A: Birth Decade of an Elder / Ancestor
        foreach ($nodes as $node) {
            $year = 0;
            if (!empty($node['birth_year'])) {
                $year = (int)$node['birth_year'];
            } elseif (!empty($node['birth_date']) && is_string($node['birth_date'])) {
                $ts = strtotime($node['birth_date']);
                if ($ts !== false) {
                    $year = (int)date('Y', $ts);
                }
            }

            if ($year >= 1880 && $year <= 2010) {
                $decade = (int)(floor($year / 10) * 10);
                $relName = trim(($node['first_name'] ?? '') . ' ' . ($node['last_name'] ?? ''));

                $options = [
                    $decade . 's',
                    ($decade - 10) . 's',
                    ($decade + 10) . 's',
                    ($decade + 20) . 's',
                ];
                shuffle($options);

                return [
                    'question'            => "In which decade was {$relName} born?",
                    'options'             => array_values(array_unique($options)),
                    'category'            => 'ancestral_decade',
                    'featured_node_id'    => (int)($node['id'] ?? 0),
                    'featured_user_id'    => !empty($node['user_id']) ? (string)$node['user_id'] : null,
                    'featured_first_name' => (string)($node['first_name'] ?? ''),
                    'featured_last_name'  => (string)($node['last_name'] ?? ''),
                    'featured_name'       => $relName,
                ];
            }
        }

        // Category B: Ancestral Birth Place
        foreach ($nodes as $node) {
            $place = trim((string)($node['birth_place'] ?? ''));
            if ($place !== '' && strlen($place) >= 3) {
                $relName = trim(($node['first_name'] ?? '') . ' ' . ($node['last_name'] ?? ''));
                $distractors = ['London, UK', 'Lagos, Nigeria', 'Manchester, UK', 'Ibadan, Nigeria'];
                $options = array_slice(array_unique(array_merge([$place], $distractors)), 0, 4);
                shuffle($options);

                return [
                    'question'            => "Where was {$relName}'s ancestral birthplace recorded?",
                    'options'             => $options,
                    'category'            => 'ancestral_place',
                    'featured_node_id'    => (int)($node['id'] ?? 0),
                    'featured_user_id'    => !empty($node['user_id']) ? (string)$node['user_id'] : null,
                    'featured_first_name' => (string)($node['first_name'] ?? ''),
                    'featured_last_name'  => (string)($node['last_name'] ?? ''),
                    'featured_name'       => $relName,
                ];
            }
        }

        // Category C: Multi-Generational Depth
        $generations = [];
        foreach ($nodes as $node) {
            $gen = (int)($node['generation_level'] ?? 0);
            $generations[$gen] = true;
        }
        $genCount = count($generations);
        if ($genCount >= 2) {
            $options = [
                (string)$genCount . ' Generations',
                (string)max(1, $genCount - 1) . ' Generations',
                (string)($genCount + 1) . ' Generations',
                (string)($genCount + 2) . ' Generations',
            ];
            shuffle($options);

            return [
                'question' => "How many generations are currently recorded on our family tree?",
                'options'  => array_values(array_unique($options)),
                'category' => 'generation_depth',
            ];
        }

        // Category D: Fallback Clan Lore
        return [
            'question' => "Which family branch has the longest roots on our {$familyCode} tree?",
            'options'  => [
                'Paternal Ancestors',
                'Maternal Ancestors',
                'The Younger Generation',
                'Still Documenting!',
            ],
            'category' => 'clan_lore',
        ];
    }
}
