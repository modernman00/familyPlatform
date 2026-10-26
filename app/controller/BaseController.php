<?php

declare(strict_types=1);

namespace App\controller;

use App\model\{ SingleCustomerData, };
use Src\{SelectFn, Utility};
use Src\Db;
use Src\Exceptions\NotFoundException;
use Src\functionality\{ SignIn, };

class BaseController
{
    // use construct to check if user is logged in
    public function __construct()
    {
        // Centralized auth: if session is already active via RouteDispatch or login, use it directly
        if (!empty($_SESSION['id'])) {
            if (empty($_SESSION['famCode'])) {
                $query = "SELECT famCode FROM personal WHERE id = ?";
                $stmt = Db::connect2()->prepare($query);
                $stmt->execute([$_SESSION['id']]);
                $_SESSION['famCode'] = (string) $stmt->fetchColumn();
            }
            self::followFamilyCodeRename();
            return;
        }

        $VerifyJWT = SignIn::verify();
        if (!\is_array($VerifyJWT) || empty($VerifyJWT['id'])) {
            throw new NotFoundException('Error Request_2');
        }
        $id = checkInput($VerifyJWT['id']);

        $_SESSION['id'] = $id;
        $query = "SELECT famCode FROM personal WHERE id = ?";
        $stmt = Db::connect2()->prepare($query);
        $stmt->execute([$id]);
        $famCode = (string) $stmt->fetchColumn();
        $_SESSION['famCode'] = $famCode;
    }
    /**
     * RFC-0005: a renamed family code changes for every member, but their live session still holds the
     * old one. At most every 30s per session, follow the alias to the current code.
     */
    private static function followFamilyCodeRename(): void
    {
        $current = (string) ($_SESSION['famCode'] ?? '');
        if ($current === '' || (int) ($_SESSION['famCodeAliasCheckedAt'] ?? 0) > time() - 30) {
            return;
        }
        $_SESSION['famCodeAliasCheckedAt'] = time();
        try {
            $live = \App\services\FamilyCodeRenameService::resolveAlias(Db::connect2(), $current);
            $was = strtoupper(trim(str_replace('#', '', $current)));
            if ($live !== '' && $live !== $was) {
                $_SESSION['famCode'] = $live;
                // famCodes is a string at login and an array after a migration; keep both in step.
                $codes = $_SESSION['famCodes'] ?? null;
                if (is_string($codes) && strtoupper(trim(str_replace('#', '', $codes))) === $was) {
                    $_SESSION['famCodes'] = $live;
                } elseif (is_array($codes)) {
                    $_SESSION['famCodes'] = array_values(array_unique(array_map(
                        static fn($c) => is_string($c) && strtoupper(trim(str_replace('#', '', $c))) === $was ? $live : $c,
                        $codes
                    )));
                }
            }
        } catch (\Throwable) {
            // never block a request on the alias lookup
        }
    }

    public static function viewWithCsp(string $view, array $data = []): void
    {
        Utility::view($view, $data);
    }

    /**
     * Defensively validate CSRF token across headers, JSON payloads, and form posts.
     *
     * @param array<string, mixed>|null $payload Optional parsed JSON body or form input.
     * @throws \Src\Exceptions\UnauthorisedException If token is missing or invalid.
     */
    public static function validateCsrf(?array $payload = null): void
    {
        $sessionToken = (string) ($_SESSION['token'] ?? '');
        if ($sessionToken === '') {
            throw new \Src\Exceptions\UnauthorisedException('We are not familiar with the nature of your activities.');
        }

        $headerToken = (string) ($_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_CSRF_TOKEN'] ?? '');
        if ($headerToken === '' && function_exists('getallheaders')) {
            $headers = getallheaders();
            $headerToken = (string) ($headers['X-XSRF-TOKEN'] ?? $headers['X-CSRF-TOKEN'] ?? $headers['X-XSRF-Token'] ?? $headers['X-CSRF-Token'] ?? $headers['x-xsrf-token'] ?? $headers['x-csrf-token'] ?? '');
        }

        $bodyToken = (string) ($payload['token'] ?? ($payload['_token'] ?? ($_POST['token'] ?? ($_POST['_token'] ?? ''))));

        $isValid = false;
        if ($headerToken !== '') {
            $isValid = hash_equals($sessionToken, $headerToken) || hash_equals($sessionToken, urldecode($headerToken));
        }
        if (!$isValid && $bodyToken !== '') {
            $isValid = hash_equals($sessionToken, $bodyToken) || hash_equals($sessionToken, urldecode($bodyToken));
        }

        if (!$isValid) {
            \Src\CheckToken::tokenCheck();
        }
    }

    /**
     * Silently and defensively rehydrates the PHP session from a valid auth cookie
     * if the session was flushed by PHP's garbage collector or closed browser tabs.
     * Ported from PartyPlatform with Silas's token_version validation guard.
     */
    public static function rehydrateSession(): void
    {
        if (!empty($_SESSION['id']) && (!empty($_SESSION['auth']['identifyCust']) || !empty($_SESSION['loggedIn']))) {
            return; // Session is already hot and valid
        }

        $tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $token = $_COOKIE[$tokenName] ?? '';
        if (empty($token) || !is_string($token)) {
            return;
        }

        try {
            $jwtKey = (string) ($_ENV['JWT_KEY'] ?? $_ENV['JWT_TOKEN'] ?? '');
            if (!class_exists('\Firebase\JWT\JWT') || empty($jwtKey)) {
                return;
            }

            try {
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtKey, 'HS256'));
            } catch (\Throwable $e) {
                if (!empty($_ENV['JWT_KEY_PREVIOUS'])) {
                    $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key((string) $_ENV['JWT_KEY_PREVIOUS'], 'HS256'));
                } else {
                    throw $e;
                }
            }

            $userData = (array) ($decoded->data ?? []);
            $userId = (string) ($userData['id'] ?? $decoded->sub ?? '');
            $tokenVersion = (int) ($userData['token_version'] ?? $decoded->token_version ?? 1);

            if (empty($userId)) {
                return;
            }

            // Silas Guard: Verify token_version against account table to prevent revoked sessions
            $dbTable = $_ENV['DB_TABLE_LOGIN'] ?? 'account';
            $stmt = Db::connect2()->prepare("SELECT token_version, type, email FROM {$dbTable} WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $accountRow = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$accountRow) {
                throw new \Exception("Account no longer exists");
            }

            if (isset($accountRow['token_version']) && (int) $accountRow['token_version'] !== $tokenVersion) {
                throw new \Exception("Session revoked: token_version mismatch");
            }

            $_SESSION['id'] = $userId;
            $_SESSION['manager_id'] = $userId;
            $_SESSION['auth'] = [
                'identifyCust' => $userId,
                'email' => (string) ($userData['email'] ?? $accountRow['email'] ?? ''),
                'codeVerified' => true,
                '2FA_token_ts' => time(),
                'type' => (string) ($userData['type'] ?? $accountRow['type'] ?? 'user'),
            ];
            $_SESSION['user'] = $userData;
            $_SESSION['user_id'] = $userId;
            $_SESSION['loggedIn'] = true;

            // Also hydrate famCode if empty
            if (empty($_SESSION['famCode'])) {
                $pStmt = Db::connect2()->prepare("SELECT famCode FROM personal WHERE id = ? LIMIT 1");
                $pStmt->execute([$userId]);
                $famCode = $pStmt->fetchColumn();
                if ($famCode) {
                    $_SESSION['famCode'] = (string) $famCode;
                }
            }
        } catch (\Throwable $e) {
            // Invalid, expired, revoked, or tampered token: silently clear the dead cookie
            $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            if (!headers_sent()) {
                setcookie($tokenName, '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        }
    }

    public static function membersData(): array
    {
        $id = checkInput($_SESSION['id']);
        $id = is_string($id) ? $id : '';
        $setData = new SingleCustomerData;
        $table = ['personal', 'contact', 'otherFamily', 'post', 'profilePics', 'work'];

        $memberData = $setData->getCustomerData($id, $table);
        if (!$memberData || !is_array($memberData) || empty($memberData['famCode']) || empty($memberData['firstName']) || empty($memberData['lastName'])) {
            throw new NotFoundException('Unauthorized: No Data Found');
        }

        sessSetMany([
            'famCode' => $memberData['famCode'],
            'fullName' => $memberData['firstName'] . ' ' . $memberData['lastName']
        ]);
        $memberData['id'] = $id;
        return self::formatPerson($memberData);
    }

    public static function fetchRelationsData(string $id, string $table, string $who): ?array
{
    $data = [];
    $seen = [];

    $otherFamily = SelectFn::selectAllRowsById($table, 'id', $id) ?? [];
    if (empty($otherFamily)) {
        return null;
    }

    foreach ($otherFamily as $row) {

        $email  = $row["{$who}_email"]  ?? '';
        $mobile = $row["{$who}_mobile"] ?? '';

        // 1) Try by email
        $fetchId = [];
        if ($email !== '') {
            $fetchId = SelectFn::selectAllRowsById('contact', 'email', $email) ?? [];
        }

        // 2) If not found by email, try by mobile
        if (empty($fetchId) && $mobile !== '') {
            $fetchId = SelectFn::selectAllRowsById('contact', 'mobile', $mobile) ?? [];
        }

        // 3) If found in contact table, pull full member data
        if (!empty($fetchId)) {
            foreach ($fetchId as $getData) {
                $relId = $getData['id'] ?? null;
                if (!$relId) {
                    continue;
                }

                // ✅ don't return the same person as their own sibling/child/etc
                if ($relId === $id) {
                    continue;
                }

                // ✅ dedupe (same relative found twice)
                if (isset($seen[$relId])) {
                    continue;
                }
                $seen[$relId] = true;

                $fullData = BaseController::findMemberById($relId);

                if (isset($fullData['gender'])) {
                    $fullData['relationship'] = self::resolveRelationship($who, $fullData['gender']);
                }

                $data[] = $fullData;
            }

            continue;
        }

        // 4) Fallback (not linked)
        $relationship = self::resolveRelationship($who, $row["{$who}_gender"] ?? '');

        $sex = (($row["{$who}_gender"] ?? '') === 'Male' || $who === 'father')
            ? 'avatarM.png'
            : 'avatarF.png';

        $data[] = [
            'fullName'     => $row["{$who}_name"] ?? '',
            'email'        => $email,
            'mobile'       => $mobile,
            'linked'       => $row["{$who}_linked"] ?? '',
            'relationship' => $relationship,
            'gender'       => $row["{$who}_gender"] ?? '',
            'img'          => "/resources/images/profile/$sex",
        ];
    }

    return $data;
}




    /**
     * @param array|null|string $id
     */
    public static function findMemberById(array|string|null $id): array
    {
        $setData = new SingleCustomerData;
        $table = ['personal', 'contact', 'otherFamily', 'post', 'profilePics', 'work'];

        $idString = is_string($id) ? $id : '';
        $memberData = $setData->getCustomerData($idString, $table);
        if (!$memberData || !is_array($memberData) || empty($memberData['famCode']) || empty($memberData['firstName']) || empty($memberData['lastName'])) {
            throw new NotFoundException('Unauthorized: No Data Found');
        }

        return self::formatPerson($memberData);
    }

    public static function returnId(): array|string|null
    {
        return checkInput($_SESSION['id']);
    }

    /**
     * True when the logged-in session belongs to $famCode — checked against the
     * session ('famCode' / 'famCodes') and, authoritatively, the user_families
     * table. Used to family-scope profile and family-tree views so a member
     * cannot enumerate other families' data by walking ids (IDOR).
     */
    public static function sessionSharesFamily(string $famCode): bool
    {
        $normalize = static fn(string $c): string => strtoupper(ltrim(trim($c), '#'));
        $normFamCode = $normalize($famCode);
        if ($normFamCode === '') {
            return false;
        }

        $sessionCodes = [];
        if (!empty($_SESSION['famCode']) && is_string($_SESSION['famCode'])) {
            $sessionCodes[] = $normalize($_SESSION['famCode']);
        }
        $many = $_SESSION['famCodes'] ?? [];
        if (is_string($many)) {
            $many = [$many];
        }
        if (is_array($many)) {
            foreach ($many as $fc) {
                if (is_string($fc) && trim($fc) !== '') {
                    $sessionCodes[] = $normalize($fc);
                }
            }
        }
        if (in_array($normFamCode, $sessionCodes, true)) {
            return true;
        }

        $sessionId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
        if ($sessionId === '') {
            return false;
        }

        try {
            $stmt = Db::connect2()->prepare(
                "SELECT 1 FROM user_families WHERE user_id = ? AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ? LIMIT 1"
            );
            $stmt->execute([$sessionId, $normFamCode]);
            if ((bool) $stmt->fetchColumn()) {
                return true;
            }

            // Fallback to personal table
            $stmtPersonal = Db::connect2()->prepare(
                "SELECT 1 FROM personal WHERE id = ? AND UPPER(TRIM(REPLACE(famCode, '#', ''))) = ? LIMIT 1"
            );
            $stmtPersonal->execute([$sessionId, $normFamCode]);
            return (bool) $stmtPersonal->fetchColumn();
        } catch (\Throwable $e) {
            error_log('sessionSharesFamily check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Whether the current session may view member $memberId's profile / tree.
     * Mirrors the directory-search visibility rule (AllMembersData::searchMembers):
     * your own record, a shared family, or an approved connection either way.
     */
    public static function sessionCanViewMember(string $memberId, string $memberFamCode): bool
    {
        $sessionId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
        if ($sessionId === '') {
            return false;
        }

        // Super Admin bypass: can view any member irrespective of privacy or family
        if (\App\model\AllMembersData::isSuperAdmin($sessionId)) {
            return true;
        }

        if ($memberId !== '' && hash_equals($sessionId, $memberId)) {
            return true;
        }
        if (self::sessionSharesFamily($memberFamCode)) {
            return true;
        }
        if ($memberId === '') {
            return false;
        }

        try {
            $stmt = Db::connect2()->prepare(
                "SELECT 1 FROM requestMgt
                 WHERE LOWER(status) IN ('approved','accepted')
                   AND ((approver_id = ? AND requester_id = ?) OR (approver_id = ? AND requester_id = ?))
                 LIMIT 1"
            );
            $stmt->execute([$sessionId, $memberId, $memberId, $sessionId]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('sessionCanViewMember check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Determines whether the given user or current session holds Super Admin privileges.
     */
    public static function isSuperAdmin(?string $userId = null): bool
    {
        $id = $userId ?? (isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '');
        return \App\model\AllMembersData::isSuperAdmin($id);
    }

    /**
     * Format main person's data from multiple tables
     * @param array $data
     * @return array
     */
    private static function formatPerson(array $data): array
    {
        $sex = $data['gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';


        $fatherFullName = trim((string)($data['father_name'] ?? ''));
        $fatherParts = !empty($fatherFullName) ? explode(' ', $fatherFullName, 2) : ['', ''];
        $fatherFirstName = $data['father_first_name'] ?? $fatherParts[0];
        $fatherLastName = $data['father_last_name'] ?? ($fatherParts[1] ?? '');

        $motherFullName = trim((string)($data['mother_name'] ?? ''));
        $motherParts = !empty($motherFullName) ? explode(' ', $motherFullName, 2) : ['', ''];
        $motherFirstName = $data['mother_first_name'] ?? $motherParts[0];
        $motherLastName = $data['mother_last_name'] ?? ($motherParts[1] ?? '');

        return [
            'id' => $data['id'] ?? '',
            'firstName' => $data['firstName'] ?? '',
            'lastName' => $data['lastName'] ?? '',
            'fullName' => $data['firstName'] . ' ' . $data['lastName'] ?? '',
            'famCode' => $data['famCode'] ?? '',
            'gender' => $data['gender'] ?? '',
            'birthDate' => \sprintf('%d-%s-%d', $data['year'] ?? 0, $data['month'] ?? '', $data['day'] ?? 0),
            'maritalStatus' => $data['marital_status'] ?? '',
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'country' => $data['country'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'day' => $data['day'] ?? null,
            'month' => $data['month'] ?? null,
            'year' => $data['year'] ?? null,
            'employmentStatus' => $data['employmentStatus'] ?? null,
            'father_first_name' => $fatherFirstName,
            'father_last_name' => $fatherLastName,
            'father_name' => $fatherFullName,
            'father_email' => $data['father_email'] ?? null,
            'father_mobile' => $data['father_mobile'] ?? null,
            'mother_first_name' => $motherFirstName,
            'mother_last_name' => $motherLastName,
            'mother_name' => $motherFullName,
            'mother_email' => $data['mother_email'] ?? null,
            'mother_mobile' => $data['mother_mobile'] ?? null,
            'mother_maiden' => $data['maiden_name'] ?? $data['mother_maiden'] ?? null,
            'maiden_name' => $data['maiden_name'] ?? $data['mother_maiden'] ?? null,
            'spouse_name' => $data['spouse_name'] ?? null,
            'spouse_email' => $data['spouse_email'] ?? null,
            'spouse_mobile' => $data['spouse_mobile'] ?? null,
            'otherFamCode' => $data['otherFamCode'] ?? null,
            'two_factor_auth' => $data['two_factor_auth'] ?? 'off',
            'profile_visibility' => $data['profile_visibility'] ?? 'Public',
            'show_my_profile' => $data['show_my_profile'] ?? 'on',
            'show_birth_year' => $data['show_birth_year'] ?? 'off',
            'email_notifications' => $data['email_notifications'] ?? 'on',
            'sms_notifications' => $data['sms_notifications'] ?? 'on',
            'profilePics' => $data['img'] ?? null,
            'profileImg' => !empty($data['img']) ? $data['img'] : $sex,
            'img' => !empty($data['img']) ? "/resources/images/profile/{$data['img']}" : "/resources/images/profile/$sex",
        ];
    }

    /**
     * Resolve relationship label based on role and gender.
     *
     * @param string $role    Relationship type (e.g. 'sibling', 'children')
     * @param string|null $gender Gender value (e.g. 'Male', 'Female')
     * @return string|null
     */
    private static function resolveRelationship(string $role, ?string $gender): ?string
    {
        // Define mappings for each role
        $map = [
            'sibling' => [
                'Male' => 'Brother',
                'Female' => 'Sister',
            ],
            'children' => [
                'Male' => 'Son',
                'Female' => 'Daughter',
            ],
            'sibling_children' => [
                'Male' => 'Nephew',
                'Female' => 'Niece',
            ],
            // Extendable: add 'parent', 'spouse', etc.
        ];

        // Defensive: check if mapping exists
        return $map[$role][$gender] ?? null;
    }

    /**
     * Fetch sibling children
     * @param array $siblings
     * @param string $famCode
     * @return array
     */
    private static function getSiblingChildren(array $siblings): array
    {
        $siblingChildren = [];
        foreach ($siblings as $sibling) {
            $id = $sibling['id'];

            // printArr($sibling);

            if (empty($id))
                continue;

            $children = SelectFn::selectAllRowsById('children', 'id', $id) ?? [];
            //  printArr($children);

            if (!empty($children)) {

                foreach ($children as $data) {

                    $email = $data["children_email"];

                    // check for children detail via contact 
                    $fetchTheId = SelectFn::selectAllRowsById('contact', 'email', $email);


                    if (!empty($fetchTheId)) {

                        foreach ($fetchTheId as $getData) {
                            $fullData = BaseController::findMemberById($getData['id']);


                            // set relationship for children and siblings
                            if (isset($fullData['gender'])) {
                                $fullData['relationship'] = self::resolveRelationship('sibling_children', $fullData['gender']);
                                $fullData['father_id'] = $id;
                            }

                            $siblingChildren[] = $fullData;
                        }
                    } else {
                        $sex = $data['children_gender'] === 'Male' ? 'avatarM.png' : 'avatarF.png';
                        $siblingChildren[] = [
                            'father_id' => $id,
                            'fullName' => $data['children_name'],
                            'email' => $data['children_email'],
                            'gender' => $data['children_gender'],
                            'relationship' => self::resolveRelationship('sibling_children', $data['children_gender']),
                            // 'mobile' => $data['children_mobile'],
                            'linked' => $data['children_linked'],
                            'img' => $data['img'] ? "/resources/images/profile/{$data['img']}" : "/resources/images/profile/$sex"
                        ];
                    }
                }
            }
        }
        return $siblingChildren;
    }



    private static function hasProperImage(array $person): bool
    {
        $defaultAvatars = ['avatarF.png', 'avatarM.png'];

        // accept both profilePics and profilepics
        $profile = $person['profilePics'] ?? $person['profilepics'] ?? '';

        if (!empty($profile)) {
            return !in_array($profile, $defaultAvatars, true);
        }

        $img = $person['img'] ?? '';
        if (!empty($img)) {
            foreach ($defaultAvatars as $avatar) {
                if (strpos($img, $avatar) !== false) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }




}
