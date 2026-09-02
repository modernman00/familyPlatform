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
      $commentNo = (int) (\cleanSession((string) ($_POST['comment_no'] ?? '')));
      $reactionType = \cleanSession((string) ($_POST['reaction'] ?? ''));
      $userId = \cleanSession((string) ($_SESSION['id'] ?? ''));
      $famCode = \cleanSession((string) ($_SESSION['famCode'] ?? ''));

      // ── Input guards ───────────────────────────────────────────────────
      if (!$commentNo || !$userId || !$famCode) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
        return;
      }

      $allowed = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
      if (!\in_array($reactionType, $allowed, true)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid reaction type.']);
        return;
      }

      $pdo = Db::connect2();

      // ── Verify the comment belongs to this user's family ───────────────
      $stmt = $pdo->prepare(
        "SELECT cr.comment_no FROM comment cr
         INNER JOIN post p ON cr.post_no = p.post_no
         WHERE cr.comment_no = :commentNo AND p.postFamCode = :famCode
         LIMIT 1"
      );
      $stmt->execute(['commentNo' => $commentNo, 'famCode' => $famCode]);

      if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Comment not found in your family.']);
        return;
      }

      // ── Toggle: if same reaction exists, remove it (un-react) ──────────
      $existing = $pdo->prepare(
        "SELECT id, label FROM comment_reactions
         WHERE comment_no = :commentNo AND id = :userId"
      );
      $existing->execute(['commentNo' => $commentNo, 'userId' => $userId]);
      $row = $existing->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        if ($row['label'] === $reactionType) {
          // Same emoji → toggle off
          $pdo->prepare("DELETE FROM comment_reactions WHERE id = :id AND comment_no = :commentNo")
              ->execute(['id' => $userId, 'commentNo' => $commentNo]);
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
      }

      // ── Return refreshed counts to the client ──────────────────────────
      $counts = self::fetchReactions($commentNo, false);
      \msgSuccess(200, ['status' => 'success', 'counts' => $counts]);

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
        'wow' => 'wow', 'sad' => 'sad', 'angry' => 'angry',
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

      if ($existing) {
        UpdateFn::updateMultiple(
          table: 'reaction_counts',
          identifier: 'comment_no',
          data: $countReactionArray
        );
      } else {
        SubmitForm::submitForm(
          table: 'reaction_counts',
          fields: $countReactionArray
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
