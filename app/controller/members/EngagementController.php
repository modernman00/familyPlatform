<?php
declare(strict_types=1);

namespace App\controller\members;

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
            // Accept both multipart/form-data and a raw JSON body
            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents('php://input');
                if ($raw !== false && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $input = $decoded;
                    }
                }
            }

            $optionId = (int) ($input['option_id'] ?? 0);
            $postNo = (int) ($input['post_no'] ?? 0);
            $userId = $_SESSION['id'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            if ($optionId <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid option ID']);
                return;
            }

            // Cast the vote
            PollData::castVote($optionId, $userId);

            // Return updated poll data if post_no is provided, so the frontend can refresh it
            $pollData = null;
            if ($postNo > 0) {
                $polls = PollData::getPollsForPosts([$postNo], $userId);
                $pollData = $polls[$postNo] ?? null;
            }

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'poll' => $pollData]);
        } catch (\Throwable $th) {
            http_response_code(400);
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
