<?php

namespace App\Controller\members;

use PDO;

use Src\Exceptions\NotFoundException;
use Src\Exceptions\ForbiddenException;
use App\classes\{Db, PushNotificationClass, Pusher};
use Src\{SelectFn, SubmitForm, UpdateFn, InnerJoin};

class CommentReactionController
{

  /**
   * Expected JSON input:
   * {
   *   "comment_no": int,
   *   "reaction": string, // emoji or shortcode
   *   "label": string     // semantic label like 'love', 'sad'
   * }
   */
  public function addReaction()
  {
    try {
      $userId = $_SESSION['id'] ?? null;
      $input = json_decode(file_get_contents('php://input'), true);

      $commentNo = $input['comment_no'] ?? null;
      $reaction = $input['reaction'] ?? null;
      $label = $input['label'] ?? null;


      if (!$userId) {
        throw new ForbiddenException("User not authenticated");
      }

      if (!$commentNo || !$reaction) {
        throw new NotFoundException('Comment no or reaction not found');
      }
      preventAbuseTogglin();

      // 🧩 Check if user already reacted with the same emoji
      $existing = SelectFn::selectWhereBothIdentifiersMatch(
        table: 'comment_reactions',
        identifier1: 'comment_no',
        identifier2: 'id',
        identifier1Answer: $commentNo,
        identifier2Answer: $userId
      );

      if ($existing && $existing[0]['label'] === $label) {
        // 👀 Avoid redundant updates (same emoji again)
        msgSuccess(200, 'Reaction unchanged');
        return;
      }

      // 🧠 will only update if the emoji is differnt
      if ($existing && $existing[0]['label'] !== $label) {



        UpdateFn::makeUpdateFn(
          table: 'comment_reactions',
          identifier: ['comment_no', 'id'],
          data: [
            'reaction' => $reaction,
            'label' => $label,
            'id' => $userId,
            'comment_no' => $commentNo
          ],
          logic: 'AND'
        );
      } else {
        SubmitForm::submitForm(
          table: 'comment_reactions',
          fields: [
            'comment_no' => $commentNo,
            'id' => $userId,
            'reaction' => $reaction,
            'label' => $label
          ]
        );
      }

      // 🧮 Count total reactions and who reacted
      $countReaction = self::countReactions($commentNo);
      $whoReacted = self::getReactions($commentNo);

      // 🟢 Pusher broadcast payload
      $payload = [
        'commentNo' => $commentNo,
        'reaction' => $reaction,
        'label' => $label,
        'countReaction' => $countReaction,
        'whoReacted' => $whoReacted
      ];

      // 🚀 Send push notification
      PushNotificationClass::sendPushNotification(
        userId: $userId,
        message: "$whoReacted reacted to a comment"
      );
      Pusher::broadcast('comments-channel', 'reaction-updated', $payload);

      msgSuccess(200, $payload);
    } catch (\Throwable $e) {
      \showError($e);
    }
  }

  /**
   * Count the total number of reactions for a given comment
   *
   * @param int $commentNo The comment number to count reactions for
   * @return array An associative array with the count of each reaction type and the comment number
   * @throws \Throwable If an error occurs while executing the query
   */
  private static function countReactions($commentNo)
  {
    try {

      $countReaction = SelectFn::selectColCountByIdGroup(
        table: 'comment_reactions',
        column: 'label',
        identifier: 'comment_no',
        value: $commentNo
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

      foreach ($countReaction as $count) {
        // change $count['label'] to smaller case
        $label = strtolower($count['label']);
        $countReactionArray[$label] = (int)$count['total'];
        $totalReactions += (int)$count['total'];
        $countReactionArray['totalReactions'] = $totalReactions;
      }

      // check if comment_no exist in reaction_count table and if yes, update and if no, insert
      $existing = SelectFn::selectAllRowsById(
        table: 'reaction_counts',
        identifier: 'comment_no',
        identifierAnswer: $commentNo
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
    }
  }

  // get who have reacted to a comment
  private static function getReactions($commentNo)
  {
    try {
      // Now fetch who reacted (limit for tooltip)
      $response = InnerJoin::joinParamSelect(
        firstTable: 'comment_reactions',
        para: 'id',
        paraWhere: 'comment_no',
        bind: $commentNo,
        table: ['personal'],
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
