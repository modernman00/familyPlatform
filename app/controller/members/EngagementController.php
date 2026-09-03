<?php
declare(strict_types=1);

namespace App\controller\members;

use App\model\ReactionData;
use App\model\PollData;
use App\model\AllMembersData;
use App\controller\BaseController;
use Src\CheckToken;
use Src\Db;

class EngagementController
{
    public function react(): void
    {
        try {
            CheckToken::tokenCheck();

            $postNo = (int) ($_POST['post_no'] ?? 0);
            $reactionType = is_string($_POST['reaction_type'] ?? null) ? $_POST['reaction_type'] : '';
            $userId = $_SESSION['id'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $validReactions = ['like', 'love', 'haha', 'shock', 'sad'];
            if (!in_array($reactionType, $validReactions, true)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid reaction type']);
                return;
            }

            // IDOR guard: you can only react to a post in one of your families.
            if (!self::postInSessionFamily($postNo)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Not allowed']);
                return;
            }

            ReactionData::toggleReaction($postNo, $userId, $reactionType);
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $th) {
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /** True when $postNo belongs to a family the current session is a member of. */
    private static function postInSessionFamily(int $postNo): bool
    {
        if ($postNo <= 0) {
            return false;
        }
        try {
            $stmt = Db::connect2()->prepare("SELECT postFamCode FROM post WHERE post_no = ? LIMIT 1");
            $stmt->execute([$postNo]);
            $fc = $stmt->fetchColumn();
            return is_string($fc) && BaseController::sessionSharesFamily($fc);
        } catch (\Throwable $e) {
            error_log('postInSessionFamily check failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Resolve the post a poll option belongs to (0 when unknown). */
    private static function postForOption(int $optionId): int
    {
        if ($optionId <= 0) {
            return 0;
        }
        try {
            $stmt = Db::connect2()->prepare(
                "SELECT pp.post_no
                 FROM post_poll_options ppo
                 JOIN post_polls pp ON pp.poll_id = ppo.poll_id
                 WHERE ppo.option_id = ? LIMIT 1"
            );
            $stmt->execute([$optionId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('postForOption lookup failed: ' . $e->getMessage());
            return 0;
        }
    }
    
    public function vote(): void
    {
        try {
            CheckToken::tokenCheck();

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

            // IDOR guard: resolve the poll's post (client-supplied post_no is only
            // a hint) and confirm it's in one of the caller's families.
            $resolvedPost = self::postForOption($optionId);
            if ($resolvedPost <= 0 || !self::postInSessionFamily($resolvedPost)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Not allowed']);
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
