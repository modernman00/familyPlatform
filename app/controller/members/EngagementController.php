<?php
declare(strict_types=1);

namespace App\Controller\members;

use App\model\ReactionData;
use App\model\PollData;
use App\model\AllMembersData;

class EngagementController
{
    public function react(): void
    {
        try {
            $postNo = (int) $_POST['post_no'];
            $reactionType = $_POST['reaction_type'];
            $userId = $_SESSION['id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }
            
            $validReactions = ['like', 'love', 'haha', 'shock', 'sad'];
            if (!in_array($reactionType, $validReactions, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid reaction type']);
                return;
            }
            
            ReactionData::toggleReaction($postNo, $userId, $reactionType);
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $th) {
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
    
    public function vote(): void
    {
        try {
            $optionId = (int) $_POST['option_id'];
            $userId = $_SESSION['id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }
            
            PollData::castVote($optionId, $userId);
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $th) {
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
    
    public function fetchMemories(): void
    {
        try {
            $userId = $_SESSION['id'] ?? null;
            $famCodes = $_SESSION['famCodes'] ?? [];
            
            if (!$userId || empty($famCodes)) {
                echo json_encode(['status' => 'success', 'data' => []]);
                return;
            }
            
            $memories = AllMembersData::getMemories($userId, $famCodes);
            echo json_encode(['status' => 'success', 'data' => $memories]);
        } catch (\Throwable $th) {
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
}
