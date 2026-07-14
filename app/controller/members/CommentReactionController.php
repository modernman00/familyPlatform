<?php
namespace App\controller\members;

use PDO;

use Src\Exceptions\NotFoundException;
use Src\Exceptions\ForbiddenException;
use App\classes\{Db, PushNotificationClass, Pusher};
use Src\{SelectFn, SubmitForm, UpdateFn, InnerJoin, CheckToken};

final class CommentReactionController
{

  /**
   * Count the total number of reactions for a given comment
   *
   * @param int $commentNo The comment number to count reactions for
   *
   * @return int[]|null An associative array with the count of each reaction type and the comment number
   *
   * @throws \Throwable If an error occurs while executing the query
   *
   * @psalm-return array{totalReactions: int<min, max>,...}|null
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
