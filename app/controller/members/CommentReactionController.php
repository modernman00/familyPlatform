<?php 

namespace App\Controller\members;

use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\{SelectFn, SubmitForm, UpdateFn};

class CommentReactionController 
{

  public function addReaction() {
    try {
      $userId = $_SESSION['id'];

      $input = json_decode(file_get_contents('php://input'), true);
      $commentNo = $input['comment_no'] ?? null;
      $reaction = $input['reaction'] ?? null;
      $label = $input['label'] ?? null;
      $remove = !empty($input['remove']); // support removing reaction

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
        $update = UpdateFn::updateMultiple(
          table: 'comment_reactions',
          identifier: 'id',
          data: [
            'reaction' => $reaction,
            'label' => $label,
            'id' => $userId,
          ],
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

      if ($update || $insert) {
        \msgSuccess(200, 'Reaction added successfully');
      }
    } catch (\Throwable $e) {
     \showError($e);
    }
  }

}