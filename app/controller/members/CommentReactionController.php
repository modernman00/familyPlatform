<?php
declare(strict_types=1);

namespace App\controller\members;

use PDO;

use Src\Exceptions\NotFoundException;
use Src\Exceptions\ForbiddenException;
use Src\Db;
use App\classes\{PushNotificationClass, Pusher};
use Src\{SelectFn, SubmitForm, UpdateFn, InnerJoin, CheckToken};

final class CommentReactionController
{
  private static ?bool $hasCelebrateColumn = null;

  /**
   * Defensive check and self-healing migration for reaction_counts.celebrate column
   */
  private static function hasCelebrateColumn(): bool
  {
    if (self::$hasCelebrateColumn !== null) {
      return self::$hasCelebrateColumn;
    }

    try {
      $pdo = Db::connect2();
      $stmt = $pdo->query("SHOW COLUMNS FROM `reaction_counts` LIKE 'celebrate'");
      $col = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
      if ($col) {
        self::$hasCelebrateColumn = true;
        return true;
      }

      // Self-heal: attempt to dynamically add the column if missing on production/staging
      $pdo->exec("ALTER TABLE `reaction_counts` ADD COLUMN `celebrate` INT NOT NULL DEFAULT '0' AFTER `angry`");
      self::$hasCelebrateColumn = true;
      return true;
    } catch (\Throwable $th) {
      error_log('[CommentReactionController] celebrate column check fallback: ' . $th->getMessage());
      self::$hasCelebrateColumn = false;
      return false;
    }
  }

  /**
   * Adds or toggles a reaction on a comment.
   *
   * Validates the comment_no and reaction_type, checks the comment belongs
   * to the user's family, then upserts into comment_reactions (toggles if
   * the same reaction is submitted twice). Returns the updated reaction counts.
   *
   * @return void
   */
  public static function addReaction(): void
  {
    try {
      CheckToken::tokenCheck();

      $commentNo = (int) (\cleanSession((string) ($_POST['comment_no'] ?? '')));
      $reactionType = \cleanSession((string) ($_POST['reaction'] ?? ''));
      $userId = \cleanSession((string) ($_SESSION['id'] ?? ''));
      $famCode = \cleanSession((string) ($_SESSION['famCode'] ?? ''));

      // SEC-5 — cap reaction spam per user (60 / 5 min via the shared limiter's
      // dedicated 'comment_reactions' profile).
      if ($userId !== '') {
        \Src\Limiter::limit($userId . ':reaction', 'comment_reactions');
      }

      // ── Input guards ───────────────────────────────────────────────────
      if (!$commentNo || !$userId || !$famCode) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
        return;
      }

      $allowed = ['like', 'love', 'haha', 'wow', 'shock', 'sad', 'angry', 'celebrate'];
      if (!\in_array($reactionType, $allowed, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid reaction type.']);
        return;
      }
      if ($reactionType === 'shock') {
        $reactionType = 'wow';
      }

      $pdo = Db::connect2();

      // ── Verify the comment belongs to this user's family or connected feed network ──
      $stmt = $pdo->prepare(
        "SELECT cr.comment_no, cr.post_no, cr.id AS comment_author_id, p.id AS post_author_id, p.postFamCode
         FROM comment cr
         INNER JOIN post p ON cr.post_no = p.post_no
         WHERE cr.comment_no = :commentNo
         LIMIT 1"
      );
      $stmt->execute(['commentNo' => $commentNo]);
      $commentRow = $stmt->fetch(PDO::FETCH_ASSOC);

      $hasAccess = false;
      if ($commentRow) {
        $postFamCode = trim((string) ($commentRow['postFamCode'] ?? ''));
        $postAuthorId = trim((string) ($commentRow['post_author_id'] ?? ''));

        // 0. Official FamilyPlatform broadcast post (universally accessible)
        if ($postAuthorId === \App\services\FamilyPlatformAccountService::getAccountId()) {
          $hasAccess = true;
        }

        // 1. Primary famCode or multi-family session codes
        $sessionFamCodes = is_array($_SESSION['famCodes'] ?? null) ? $_SESSION['famCodes'] : [];
        $sessionFamCodes[] = $famCode;
        foreach ($sessionFamCodes as $sfc) {
          $cleanSfc = trim((string) $sfc);
          if ($cleanSfc !== '' && strcasecmp($cleanSfc, $postFamCode) === 0) {
            $hasAccess = true;
            break;
          }
        }

        // 2. Approved secondary family membership in user_families
        if (!$hasAccess && $postFamCode !== '') {
          $ufStmt = $pdo->prepare(
            "SELECT 1 FROM user_families
             WHERE user_id = :uid AND UPPER(family_code) = UPPER(:famCode) AND status = 'approved'
             LIMIT 1"
          );
          $ufStmt->execute(['uid' => $userId, 'famCode' => $postFamCode]);
          if ($ufStmt->fetchColumn()) {
            $hasAccess = true;
          }
        }

        // 3. Connected family member via requestMgt (same visibility rule as FeedRepository)
        if (!$hasAccess && $postAuthorId !== '') {
          $rmStmt = $pdo->prepare(
            "SELECT 1 FROM requestMgt
             WHERE (requester_id = :uid1 AND approver_id = :author1)
                OR (approver_id = :uid2 AND requester_id = :author2)
                OR (:postFam1 != '' AND requester_id = :uid3 AND UPPER(requesterCode) = UPPER(:postFam2))
                OR (:postFam3 != '' AND approver_id = :uid4 AND UPPER(approverCode) = UPPER(:postFam4))
             LIMIT 1"
          );
          $rmStmt->execute([
            'uid1' => $userId,
            'author1' => $postAuthorId,
            'uid2' => $userId,
            'author2' => $postAuthorId,
            'postFam1' => $postFamCode,
            'uid3' => $userId,
            'postFam2' => $postFamCode,
            'postFam3' => $postFamCode,
            'uid4' => $userId,
            'postFam4' => $postFamCode,
          ]);
          if ($rmStmt->fetchColumn()) {
            $hasAccess = true;
          }
        }

        // 4. Tagged in the post
        if (!$hasAccess) {
          $tagStmt = $pdo->prepare(
            "SELECT 1 FROM post_tags WHERE post_no = :postNo AND user_id = :uid LIMIT 1"
          );
          $tagStmt->execute(['postNo' => (int) $commentRow['post_no'], 'uid' => $userId]);
          if ($tagStmt->fetchColumn()) {
            $hasAccess = true;
          }
        }

        // 5. Approved kinship network connection
        if (!$hasAccess && $postAuthorId !== '') {
          try {
            $kcStmt = $pdo->prepare(
              "SELECT 1 FROM kinship_connections
               WHERE ((user_a_id = :uid1 AND user_b_id = :author1) OR (user_b_id = :uid2 AND user_a_id = :author2))
                 AND status = 'approved'
               LIMIT 1"
            );
            $kcStmt->execute([
              'uid1' => $userId,
              'author1' => $postAuthorId,
              'uid2' => $userId,
              'author2' => $postAuthorId,
            ]);
            if ($kcStmt->fetchColumn()) {
              $hasAccess = true;
            }
          } catch (\Throwable) {
            // Ignore if table not present
          }
        }
      }

      if (!$commentRow || !$hasAccess) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Comment not found in your family.']);
        return;
      }

      $postNo = (int) ($commentRow['post_no'] ?? 0);
      $postFamCode = (string) ($commentRow['postFamCode'] ?? $famCode);

      // ── Toggle: if same reaction exists, remove it (un-react) ──────────
      $existing = $pdo->prepare(
        "SELECT id, label FROM comment_reactions
         WHERE comment_no = :commentNo AND id = :userId"
      );
      $existing->execute(['commentNo' => $commentNo, 'userId' => $userId]);
      $row = $existing->fetch(PDO::FETCH_ASSOC);

      $action = 'added';
      if ($row) {
        if ($row['label'] === $reactionType) {
          // Same emoji → toggle off
          $pdo->prepare("DELETE FROM comment_reactions WHERE id = :id AND comment_no = :commentNo")
              ->execute(['id' => $userId, 'commentNo' => $commentNo]);
          $action = 'removed';
        } else {
          // Different emoji → update. `updated_at` auto-bumps ON UPDATE.
          $pdo->prepare(
            "UPDATE comment_reactions SET reaction = :reaction, label = :label
             WHERE id = :id AND comment_no = :commentNo"
          )->execute([
            'reaction'  => $reactionType,
            'label'     => $reactionType,
            'id'        => $userId,
            'commentNo' => $commentNo,
          ]);
          $action = 'updated';
        }
      } else {
        // New reaction → insert. `created_at` defaults to CURRENT_TIMESTAMP.
        $pdo->prepare(
          "INSERT INTO comment_reactions (comment_no, id, reaction, label)
           VALUES (:commentNo, :userId, :reaction, :label)"
        )->execute([
          'commentNo' => $commentNo,
          'userId'    => $userId,
          'reaction'  => $reactionType,
          'label'     => $reactionType,
        ]);
        $action = 'added';
      }

      // ── Return refreshed counts to the client ──────────────────────────
      $counts = self::fetchReactions($commentNo, false);
      $reactionCounts = is_array($counts) && isset($counts['counts']) && is_array($counts['counts'])
        ? $counts['counts']
        : [];

      // Broadcast reaction update to family channel via Pusher
      try {
        $broadcastData = [
          'postNo'         => $postNo,
          'commentNo'      => $commentNo,
          'userId'         => $userId,
          'reactionType'   => $reactionType,
          'action'         => $action,
          'reactions'      => $reactionCounts,
          'totalReactions' => (int) ($reactionCounts['totalReactions'] ?? 0),
        ];

        Pusher::broadcastToFamily($postFamCode, 'comment-reaction', $broadcastData);
        $cleanFamCode = (string) $famCode;
        if ($cleanFamCode !== $postFamCode) {
          Pusher::broadcastToFamily($cleanFamCode, 'comment-reaction', $broadcastData);
        }
      } catch (\Throwable $pusherEx) {
        \error_log('[CommentReactionController] Pusher broadcast warning: ' . $pusherEx->getMessage());
      }

      // Notify comment author when a new reaction is added
      if ($action === 'added') {
        try {
          $cStmt = $pdo->prepare("SELECT id FROM comment WHERE comment_no = ? LIMIT 1");
          $cStmt->execute([$commentNo]);
          $commentAuthorId = (string)$cStmt->fetchColumn();

          if ($commentAuthorId !== '' && $commentAuthorId !== $userId) {
            $reactorName = trim(($_SESSION['fName'] ?? '') . ' ' . ($_SESSION['lName'] ?? ''));
            if ($reactorName === '') {
              $reactorName = 'A family member';
            }
            $targetUrl = "/profilePage?post_no={$postNo}&comment_no={$commentNo}#comment{$commentNo}";
            $notifMsg = "{$reactorName} reacted with {$reactionType} to your comment";

            $nStmt = $pdo->prepare("
              INSERT INTO notification 
              (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
              VALUES (?, ?, ?, 'Comment Reaction', 'Comment', ?, ?, 'new', NOW())
            ");
            $nStmt->execute([
              $userId,
              $commentAuthorId,
              $reactorName,
              $notifMsg,
              $targetUrl
            ]);

            PushNotificationClass::sendPushNotification(
              userId: $commentAuthorId,
              message: $notifMsg,
              url: $targetUrl,
              title: '💬 Comment Reaction',
              tag: 'comment-reaction-' . $commentNo
            );
          }
        } catch (\Throwable $reactNotifEx) {
          \error_log('[CommentReactionController notif warning] ' . $reactNotifEx->getMessage());
        }
      }

      \msgSuccess(200, ['status' => 'success', 'counts' => $counts, 'action' => $action]);

    } catch (\Throwable $e) {
      \showError($e);
    }
  }


  /**
   * Count the total number of reactions for a given comment
   *
   * @param int $commentNo The comment number to count reactions for
   *
   * @return array<string, int>|null An associative array with the count of each reaction type and the comment number
   *
   * @throws \Throwable If an error occurs while executing the query
   */
  private static function countReactions($commentNo)
  {
    try {

      $countReaction = SelectFn::selectColCountByIdGroup(
        table: 'comment_reactions',
        column: 'label',
        identifier: 'comment_no',
        value: (string)$commentNo
      );

      //   $countReaction returns (
      // [0] => Array
      //     (
      //         [label] => likes
      //         [total] => 1
      //     )

      // [1] => Array
      //     (
      //         [label] => sad
      //         [total] => 1
      //     )

      //   );
      $totalReactions = 0;

      $countReactionArray = [
        'likes' => 0,
        'love' => 0,
        'haha' => 0,
        'wow' => 0,
        'sad' => 0,
        'angry' => 0,
        'celebrate' => 0,
        'comment_no' => $commentNo,
        'totalReactions' => 0
      ];

      // Map a stored reaction label onto its reaction_counts column. The
      // "like" reaction lives in the `likes` column (and `like` is a reserved
      // word); anything unrecognised is ignored rather than fabricating a
      // column name that breaks the INSERT/UPDATE.
      $columnForLabel = [
        'like' => 'likes', 'likes' => 'likes',
        'love' => 'love', 'haha' => 'haha',
        'wow' => 'wow', 'shock' => 'wow',
        'sad' => 'sad', 'angry' => 'angry',
        'celebrate' => 'celebrate',
      ];

      foreach ($countReaction ?? [] as $count) {
        $column = $columnForLabel[strtolower((string) $count['label'])] ?? null;
        if ($column === null) {
          continue;
        }
        $countReactionArray[$column] = (int) $count['total'];
        $totalReactions += (int) $count['total'];
      }
      $countReactionArray['totalReactions'] = $totalReactions;

      // check if comment_no exist in reaction_count table and if yes, update and if no, insert
      $existing = SelectFn::selectAllRowsById(
        table: 'reaction_counts',
        identifier: 'comment_no',
        identifierAnswer: (string)$commentNo
      );

      // Strip celebrate from DB payload if database column does not exist yet to prevent 500 errors
      $dbPayload = $countReactionArray;
      if (!self::hasCelebrateColumn()) {
        unset($dbPayload['celebrate']);
      }

      if ($existing) {
        UpdateFn::updateMultiple(
          table: 'reaction_counts',
          identifier: 'comment_no',
          data: $dbPayload
        );
      } else {
        SubmitForm::submitForm(
          table: 'reaction_counts',
          fields: $dbPayload
        );
      }
      return $countReactionArray;
    } catch (\Throwable $e) {
      \showError($e);
      return null;
    }
  }

  // get who have reacted to a comment
  /**
   * @param int|string $commentNo
   *
   * @return array|bool|null
   */
  private static function getReactions(string|int $commentNo)
  {
    try {
      // Now fetch who reacted (limit for tooltip)
      $response = InnerJoin::joinParamSelect(
        para: 'id',
        paraWhere: 'comment_no',
        bind: $commentNo,
        table: ['comment_reactions', 'personal'],
        selectFields: 'personal.firstName, personal.lastName, comment_reactions.reaction, comment_reactions.label',
        limit: 5
      );

      // foreach ($response as $row) {
      //   $who = $row['firstName'] . ' ' . $row['lastName'];
      //   $row['fullName'] = $who;
      // }
      return $response;
    } catch (\Throwable $e) {
      \showError($e);
    }
  }

  /**
   * Fetch the counts of reactions for a comment and who have reacted
   * 
   * @param int $commentNo The comment number
   * @param bool $returnType Whether to return a JSON response or an array
   * @return array|void The counts of reactions and who have reacted
   * @throws \Throwable
   */
  public static function fetchReactions($commentNo, $returnType = true)
  {
    try {
      $counts = self::countReactions($commentNo);
      $who = self::getReactions($commentNo);
      if (!$returnType) {
        return ['counts' => $counts, 'who' => $who];
      }
      msgSuccess(200, ['counts' => $counts, 'who' => $who]);
    } catch (\Throwable $e) {
      \showError($e);
    }
  }
}
