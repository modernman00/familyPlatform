<?php

namespace App\Controller\members;

use App\classes\{PushNotificationClass, Pusher};

use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\{SelectFn, SubmitForm, UpdateFn, InnerJoin};

class CommentReactionController
{

  public function addReaction()
  {
    try {
      $userId = $_SESSION['id'];

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


      $existing = SelectFn::selectWhereBothIdentifiersMatch(
        table: 'comment_reactions',
        identifier1: 'comment_no',
        identifier2: 'id',
        identifier1Answer: $commentNo,
        identifier2Answer: $userId
      );

      $update = false;
      $insert = false;

      if ($existing) {
        $update = UpdateFn::makeUpdateFn(
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
        $insert = SubmitForm::submitForm(
          table: 'comment_reactions',
          fields: [
            'comment_no' => $commentNo,
            'id' => $userId,
            'reaction' => $reaction,
            'label' => $label
          ]
        );
      }

      $countReaction = $this->countReactions($commentNo);

      $whoReacted = $this->getReactions($commentNo);

      PushNotificationClass::sendPushNotification(
        userId: $userId,
        message: "$whoReacted reacted to a comment"
      );
      $payload = [
        'countReaction' => $countReaction,
        'whoReacted' => $whoReacted,
        'commentNo' => $commentNo
      ];
      Pusher::broadcast('comments-channel', 'reaction-updated', $payload);

      // if ($update || $insert) {
  msgSuccess(200, 'Reaction added successfully', $payload);
      // }
    } catch (\Throwable $e) {
      \showError($e);
    }
  }

  private function countReactions($commentNo)
  {
    try {

      $countReaction = SelectFn::selectColCountByIdGroup(
        table: 'comment_reactions',
        column: 'label',
        identifier: 'comment_no',
        value: $commentNo
      );

      $countReactionArray = [
        'likes' => 0,
        'love' => 0,
        'haha' => 0,
        'wow' => 0,
        'sad' => 0,
        'angry' => 0,
        'comment_no' => $commentNo
      ];

      foreach ($countReaction as $count) {
        // change $count['label'] to smaller case
        $label = strtolower($count['label']);
        $countReactionArray[$label] = (int)$count['total'];
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
  private function getReactions($commentNo)
  {
    try {
      // Now fetch who reacted (limit for tooltip)
      $response = InnerJoin::joinParamSelect(
        firstTable: 'comment_reactions',
        para: 'id',
        paraWhere: 'comment_no',
        bind: $commentNo,
        table: ['personal'],
        selectFields: 'personal.firstName, personal.lastName',
        limit: 5
      );

      return  $response[0]['firstName'] . ' ' . $response[0]['lastName'];
    } catch (\Throwable $e) {
      \showError($e);
    }
  }
}
