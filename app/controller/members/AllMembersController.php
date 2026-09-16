<?php
declare(strict_types=1);

namespace App\controller\members;


use Src\{
    Select,
    Delete,
    CheckToken
};
use App\model\AllMembersData;
use App\controller\BaseController;
use Exception;
use Src\Exceptions\ForbiddenException;
use Src\functionality\SignIn;

final class AllMembersController extends AllMembersData
{
    public function index(): void
    {
        try {

            view('member/showMembers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData(): void
    {
        try {
            $id = $_SESSION['id'] ?? null;
            if (!$id) {
                $tokenVerify = SignIn::verify();
                $id = $tokenVerify['id'];
            }

            $result = $this->getAllMembers($id);

            msgSuccess(200, $result);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData2(): void
    {
        try {
            $result = $this->getAllMembersNoPics();
            echo json_encode(['status' => 'success', 'message' => $result]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    // /**
    //  * @return never
    //  */
    // public function setProfile($id)
    // {
    //     $id = checkInput($id);
    //     $_SESSION['id'] = $id;
    //     header("Location: /allMembers/getProfile");
    //     die();
    // }
    // /allMembers/setProfile?id
    public function getProfile(mixed $id): void
    {
        try {
            $sessId = $_SESSION['id'] ?? null;
            if (!$sessId) {
                SignIn::verify();
            }

            $id = checkInput($id);
            $id = is_string($id) ? $id : '';
            $data = BaseController::findMemberById($id);

            // IDOR guard: only view a profile you'd see in the directory — a
            // shared family or an approved connection. Otherwise a logged-in
            // user could read any member's profile, relatives and DOB by id.
            if (!BaseController::sessionCanViewMember($id, (string) ($data['famCode'] ?? ''))) {
                msgException(403, 'You can only view profiles within your family or approved connections.');
                return;
            }

            $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'images', identifier1: "id");
            $pictures = Select::selectFn2(query: $query, bind: [$id]);

            // Fetch relatives for immediate family card
            $relativesWithImgs = [];
            $roles = [
                ['table' => 'otherFamily', 'who' => 'spouse', 'defaultRel' => 'Spouse'],
                ['table' => 'otherFamily', 'who' => 'father', 'defaultRel' => 'Father'],
                ['table' => 'otherFamily', 'who' => 'mother', 'defaultRel' => 'Mother'],
                ['table' => 'sibling', 'who' => 'sibling', 'defaultRel' => 'Sibling'],
                ['table' => 'children', 'who' => 'children', 'defaultRel' => 'Child'],
            ];

            foreach ($roles as $role) {
                $relations = BaseController::fetchRelationsData($id, $role['table'], $role['who']);
                if (!empty($relations)) {
                    foreach ($relations as $rel) {
                        if (!empty($rel['fullName'])) {
                            $rel['relationship'] = $rel['relationship'] ?? $role['defaultRel'];
                            $rel['img'] = !empty($rel['profilePics']) ? "/resources/images/profile/{$rel['profilePics']}" : ($rel['img'] ?? '/resources/images/profile/avatarM.png');
                            $relativesWithImgs[] = $rel;
                        }
                    }
                }
            }

            // Fetch total published posts count
            $db = \Src\Db::connect();
            $postCountStmt = $db->prepare("SELECT COUNT(*) FROM post WHERE id = ? AND post_status = 'published'");
            $postCountStmt->execute([$id]);
            $postCount = (int)$postCountStmt->fetchColumn();

            // Fetch public photos only
            $publicPhotos = \App\model\Post::getAllImagesByAuthor($id, true);

            \App\controller\BaseController::viewWithCsp('member/getProfile', compact('data', 'pictures', 'relativesWithImgs', 'postCount', 'publicPhotos'));
        } catch (Exception $e) {
            showError($e);
        }
    }

    // /allMembers/removeProfile?removeProfile
    public function removeProfile(mixed $apr = null, mixed $req = null): bool
    {
        try {
            // Defensively validate CSRF token (supports X-XSRF-TOKEN, X-CSRF-TOKEN, headers, cookie, POST/GET)
            $sessionToken = $_SESSION['token'] ?? '';
            $headerToken = $_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_CSRF_TOKEN'] ?? '';
            if (empty($headerToken) && function_exists('getallheaders')) {
                $headers = getallheaders();
                $headerToken = $headers['X-XSRF-TOKEN'] ?? $headers['X-CSRF-TOKEN'] ?? $headers['X-XSRF-Token'] ?? $headers['X-CSRF-Token'] ?? $headers['x-xsrf-token'] ?? $headers['x-csrf-token'] ?? '';
            }

            $isTokenValid = false;
            if (!empty($sessionToken)) {
                if (!empty($headerToken) && hash_equals($sessionToken, $headerToken)) {
                    $isTokenValid = true;
                } elseif (!empty($_POST['token']) && hash_equals($sessionToken, (string)$_POST['token'])) {
                    $isTokenValid = true;
                } elseif (!empty($_GET['token']) && hash_equals($sessionToken, (string)$_GET['token'])) {
                    $isTokenValid = true;
                } elseif (!empty($_COOKIE['XSRF-TOKEN']) && hash_equals($sessionToken, (string)$_COOKIE['XSRF-TOKEN'])) {
                    $isTokenValid = true;
                }
            }

            if (!$isTokenValid) {
                CheckToken::tokenCheck();
            }

            $sessionId = (string) ($_SESSION['id'] ?? '');
            if ($sessionId === '') {
                $payload = SignIn::verify('users');
                $sessionId = (string) \cleanSession((string) $payload['id']);
            }

            // Sanitize inputs
            $aprClean = $apr !== null ? checkInput($apr) : '';
            $reqClean = $req !== null ? checkInput($req) : '';
            $apr = is_string($aprClean) ? $aprClean : '';
            $req = is_string($reqClean) ? $reqClean : '';

            // If only one ID is passed (the target member), pair it with current session ID
            if ($apr !== '' && ($req === '' || $req === 'null' || $req === 'undefined')) {
                if ($apr === $sessionId) {
                    msgException(400, 'Target member ID required.');
                    return false;
                }
                $req = $sessionId;
            } elseif ($req !== '' && ($apr === '' || $apr === 'null' || $apr === 'undefined')) {
                if ($req === $sessionId) {
                    msgException(400, 'Target member ID required.');
                    return false;
                }
                $apr = $sessionId;
            }

            if ($req === '' || $req === 'null' || $req === 'undefined') {
                $req = $sessionId;
            }
            if ($apr === '' || $apr === 'null' || $apr === 'undefined') {
                $apr = $sessionId;
            }

            // IDOR + authz guard: the caller must be one of the two parties to
            // the connection they're deleting. Without this any logged-in user
            // could wipe arbitrary approver/requester rows by guessing ids.
            if ($sessionId === '' || (!hash_equals($sessionId, $apr) && !hash_equals($sessionId, $req))) {
                msgException(403, 'You can only remove your own connections.');
                return false;
            }

            $targetId = hash_equals($sessionId, $apr) ? $req : $apr;
            $db = \Src\Db::connect2();

            $removed = false;

            // 1. Delete cross-family connection in requestMgt
            $stmtReq = $db->prepare(
                "DELETE FROM requestMgt WHERE (approver_id = ? AND requester_id = ?) OR (approver_id = ? AND requester_id = ?)"
            );
            $stmtReq->execute([$apr, $req, $req, $apr]);
            if ($stmtReq->rowCount() > 0) {
                $removed = true;
            }

            // 2. Check if caller and target share a family code or user_families association
            $caller = BaseController::findMemberById($sessionId);
            $target = BaseController::findMemberById($targetId);

            $callerFamCode = strtoupper(trim(str_replace('#', '', (string)($caller['famCode'] ?? ''))));
            $targetFamCode = strtoupper(trim(str_replace('#', '', (string)($target['famCode'] ?? ''))));

            // Also check user_families table for shared family codes
            $famStmt = $db->prepare("SELECT family_code FROM user_families WHERE user_id = ?");
            $famStmt->execute([$sessionId]);
            $callerFamCodes = array_map(fn($r) => strtoupper(trim(str_replace('#', '', (string)$r['family_code']))), $famStmt->fetchAll(\PDO::FETCH_ASSOC) ?: []);
            if ($callerFamCode !== '' && !in_array($callerFamCode, $callerFamCodes, true)) {
                $callerFamCodes[] = $callerFamCode;
            }

            // Check if target is in any of caller's family codes
            $isSameFamily = ($callerFamCode !== '' && $callerFamCode === $targetFamCode);
            if (!$isSameFamily && !empty($callerFamCodes)) {
                $placeholders = implode(',', array_fill(0, count($callerFamCodes), '?'));
                $targetFamCheck = $db->prepare("SELECT COUNT(*) FROM user_families WHERE user_id = ? AND UPPER(TRIM(REPLACE(family_code, '#', ''))) IN ($placeholders)");
                $targetFamCheck->execute(array_merge([$targetId], $callerFamCodes));
                if (((int)$targetFamCheck->fetchColumn()) > 0) {
                    $isSameFamily = true;
                }
            }

            if ($isSameFamily) {
                // Delete user_families records linking target to any of caller's family codes
                if (!empty($callerFamCodes)) {
                    $placeholders = implode(',', array_fill(0, count($callerFamCodes), '?'));
                    $delFamStmt = $db->prepare("DELETE FROM user_families WHERE user_id = ? AND UPPER(TRIM(REPLACE(family_code, '#', ''))) IN ($placeholders)");
                    $delFamStmt->execute(array_merge([$targetId], $callerFamCodes));
                }

                // If target's primary personal.famCode matches caller's family code, isolate target to new solo family code
                if ($targetFamCode !== '' && in_array($targetFamCode, $callerFamCodes, true)) {
                    try {
                        $approvalService = new \App\service\FamilyCodeApprovalService($db);
                        $targetSurname = (string)($target['lastName'] ?? 'Member');
                        $approvalService->switchUserToSoloFamily($targetId, $targetSurname, $target);
                    } catch (\Throwable $switchEx) {
                        error_log('[removeProfile] Solo family switch warning: ' . $switchEx->getMessage());
                    }
                }

                // Clean up any pending approval requests
                try {
                    $delReq = $db->prepare("DELETE FROM family_approval_requests WHERE (id = ? AND approver_id = ?) OR (id = ? AND approver_id = ?)");
                    $delReq->execute([$targetId, $sessionId, $sessionId, $targetId]);
                } catch (\Throwable $reqEx) {
                    error_log('[removeProfile] Approval request cleanup warning: ' . $reqEx->getMessage());
                }

                $removed = true;
            }

            if ($removed) {
                msgSuccess(200, "success");
                return true;
            } else {
                msgException(404, "No active connection or family relationship found to remove");
                return false;
            }
        } catch (\Throwable $e) {
            showError($e);
            return false;
        }
    }

    /**
     * GET /allMembers/search?q=...&limit=30&offset=0
     * Server-side search across family + wider directory.
     */
    public function search(): void
    {
        try {
            $payload = SignIn::verify('users');

            $requesterId = (int) cleanSession((string)$payload['id']);
            // NOTE: SignIn::verify()'s payload shape is {id, email, role} — it never
            // carries famCode, so this has always resolved to ''. Left as-is (not a
            // typing fix's place to change search-matching behaviour); flagged in the
            // PHPStan cleanup report as a pre-existing bug for follow-up.
            $famCode     = '';

            $term   = isset($_GET['q']) ? (string) $_GET['q'] : '';
            $limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 30;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $results = $this->searchMembers($requesterId, $famCode, $term, $limit, $offset);

            msgSuccess(200, $results);
        } catch (\Throwable $th) {
            showError($th);
            msgException(500, 'Unable to search members');
        }
    }
}

