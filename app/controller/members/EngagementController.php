<?php
declare(strict_types=1);

namespace App\controller\members;

use App\model\ReactionData;
use App\model\PollData;
use App\model\AllMembersData;
use App\controller\BaseController;
use Src\Db;

class EngagementController
{
    public function react(): void
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

            BaseController::validateCsrf($input);

            $postNo = (int) ($input['post_no'] ?? 0);
            $reactionType = is_string($input['reaction_type'] ?? null) ? $input['reaction_type'] : '';
            $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';

            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $validReactions = ['like', 'love', 'haha', 'shock', 'sad'];
            if (!in_array($reactionType, $validReactions, true)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid reaction type']);
                return;
            }

            // IDOR guard: you can only react to a post accessible to your session.
            if (!self::sessionCanAccessPost($postNo, $userId)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Not allowed']);
                return;
            }

            ReactionData::toggleReaction($postNo, $userId, $reactionType);
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
        } catch (\Throwable $th) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * Determine whether the given user session has permission to view and interact
     * with the specified post (voting, reacting, commenting).
     */
    public static function sessionCanAccessPost(int $postNo, string $userId): bool
    {
        if ($postNo <= 0 || $userId === '') {
            return false;
        }
        if (BaseController::isSuperAdmin($userId)) {
            return true;
        }
        try {
            $stmt = Db::connect2()->prepare(
                "SELECT id, postFamCode FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1"
            );
            $stmt->execute([$postNo]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$post) {
                return false;
            }

            // 1. Author of the post
            if ((string)($post['id'] ?? '') === $userId) {
                return true;
            }

            // 2. Shares family code directly or via user_families/personal
            $postFamCode = (string)($post['postFamCode'] ?? '');
            if ($postFamCode !== '' && BaseController::sessionSharesFamily($postFamCode)) {
                return true;
            }

            // 3. Connected via requestMgt (either requester or approver)
            $authorId = (string)($post['id'] ?? '');
            if ($authorId !== '') {
                $stmtConn = Db::connect2()->prepare(
                    "SELECT 1 FROM requestMgt
                     WHERE ((requester_id = ? AND approver_id = ?) OR (approver_id = ? AND requester_id = ?))
                     LIMIT 1"
                );
                $stmtConn->execute([$userId, $authorId, $userId, $authorId]);
                if ((bool)$stmtConn->fetchColumn()) {
                    return true;
                }
            }

            // 4. Tagged in post_tags
            $stmtTag = Db::connect2()->prepare(
                "SELECT 1 FROM post_tags WHERE post_no = ? AND user_id = ? LIMIT 1"
            );
            $stmtTag->execute([$postNo, $userId]);
            if ((bool)$stmtTag->fetchColumn()) {
                return true;
            }

            // 5. Member shares any family tree / family_nodes branch with this post
            if ($postFamCode !== '') {
                $stmtTree = Db::connect2()->prepare(
                    "SELECT 1 FROM family_nodes
                     WHERE user_id = ? AND family_code = ?
                     LIMIT 1"
                );
                $stmtTree->execute([$userId, $postFamCode]);
                if ((bool)$stmtTree->fetchColumn()) {
                    return true;
                }

                // Or the post author is on the user's family tree
                if ($authorId !== '' && $authorId !== 'HERITAGE_BOT') {
                    $stmtKin = Db::connect2()->prepare(
                        "SELECT 1 FROM family_nodes fnUser
                         JOIN family_nodes fnAuthor ON fnUser.family_code = fnAuthor.family_code
                         WHERE fnUser.user_id = ? AND fnAuthor.user_id = ?
                         LIMIT 1"
                    );
                    $stmtKin->execute([$userId, $authorId]);
                    if ((bool)$stmtKin->fetchColumn()) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Throwable $e) {
            error_log('sessionCanAccessPost check failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Backwards-compatibility alias for internal callers. */
    public static function postInSessionFamily(int $postNo): bool
    {
        $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
        return self::sessionCanAccessPost($postNo, $userId);
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

            BaseController::validateCsrf($input);

            $optionId = (int) ($input['option_id'] ?? 0);
            $postNo = (int) ($input['post_no'] ?? 0);
            $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';

            if ($userId === '') {
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
            // a hint) and confirm it's accessible to the caller's session.
            $resolvedPost = self::postForOption($optionId);
            $targetPost = ($resolvedPost > 0) ? $resolvedPost : $postNo;
            if ($targetPost <= 0 || !self::sessionCanAccessPost($targetPost, $userId)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Not allowed']);
                return;
            }

            // Cast the vote
            PollData::castVote($optionId, $userId);

            // Return updated poll data so the frontend can refresh it authoritatively
            $pollPostNo = ($postNo > 0) ? $postNo : $targetPost;
            $polls = PollData::getPollsForPosts([$pollPostNo], $userId);
            $pollData = $polls[$pollPostNo] ?? null;

            http_response_code(200);
            header('Content-Type: application/json');
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response, served as application/json; json_encode escapes the payload and the browser never HTML-renders it.
            echo json_encode(['status' => 'success', 'poll' => $pollData]);
        } catch (\Throwable $th) {
            http_response_code(400);
            header('Content-Type: application/json');
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API error response, application/json.
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

            // Read-only: release the session lock so this does not serialise
            // behind the profile page's other concurrent AJAX calls.
            \releaseSessionLock();

            $memories = AllMembersData::getMemories($userId, $famCodes);
            echo json_encode(['status' => 'success', 'data' => $memories]);
        } catch (\Throwable $th) {
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
}
