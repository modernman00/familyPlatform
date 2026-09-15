<?php

namespace App\controller\test;

use PDO;
use PDOStatement;

class E2ETestController
{
    private PDO $pdo;

    public function __construct()
    {
        // Enforce that this is only used in non-prod
        if (!in_array($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '', ['local', 'development', 'testing'], true)) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        try {
            $this->pdo = \Src\Db::connect2();
        } catch (\Throwable $e) {
            $this->pdo = (new \Src\Db())->connect();
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param list<int|string> $params
     */
    private function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function getValidFamilyCode(): never
    {
        // Return a family code that has at least one usable inviter, so the
        // "family code exists" check and the inviter-verification step agree.
        $row = $this->run(
            "SELECT p.famCode AS code
             FROM personal p
             JOIN account a ON a.id = p.id
             LEFT JOIN contact c ON c.id = p.id
             WHERE p.famCode IS NOT NULL AND p.famCode <> ''
               AND a.deleted_at IS NULL
               AND TRIM(COALESCE(p.firstName, '')) <> ''
               AND TRIM(COALESCE(p.lastName, '')) <> ''
               AND (
                   (a.email IS NOT NULL AND a.email <> '')
                   OR (c.email IS NOT NULL AND c.email <> '')
                   OR (c.mobile IS NOT NULL AND c.mobile <> '')
               )
             LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($row ?: ['code' => 'NONE']);
        exit;
    }

    public function getValidFamilyCodeWithInviter(): never
    {
        // Mirror FamilyCodeApprovalService::findMatchingInviter: a non-deleted
        // member of the family, with a real name and a contact the verify step
        // can match on.
        $row = $this->run(
            "SELECT p.famCode, p.firstName, p.lastName,
                    COALESCE(NULLIF(c.email, ''), a.email) AS email,
                    c.mobile
             FROM personal p
             JOIN account a ON a.id = p.id
             LEFT JOIN contact c ON c.id = p.id
             WHERE p.famCode IS NOT NULL AND p.famCode <> ''
               AND a.deleted_at IS NULL
               AND TRIM(COALESCE(p.firstName, '')) <> ''
               AND TRIM(COALESCE(p.lastName, '')) <> ''
               AND (
                   (a.email IS NOT NULL AND a.email <> '')
                   OR (c.email IS NOT NULL AND c.email <> '')
                   OR (c.mobile IS NOT NULL AND c.mobile <> '')
               )
             LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        if ($row) {
            echo json_encode([
                'code' => $row['famCode'],
                'inviter' => [
                    'firstName' => $row['firstName'],
                    'lastName' => $row['lastName'],
                    'email' => $row['email'] ?: $row['mobile']
                ]
            ]);
        } else {
            echo json_encode(['error' => 'No valid inviter found']);
        }
        exit;
    }

    public function getPendingApprovalRequests(): never
    {
        $rows = $this->run("SELECT * FROM family_approval_requests WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

        $approvalService = new \App\service\FamilyCodeApprovalService($this->pdo);

        foreach ($rows as &$row) {
            $row['approval_token'] = $approvalService->generateApprovalToken((int)$row['no']);
        }
        unset($row);

        header('Content-Type: application/json');
        echo json_encode($rows);
        exit;
    }

    public function getApprovalRequest(int|string $id): never
    {
        $row = $this->run("SELECT * FROM family_approval_requests WHERE no = ?", [$id])->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $approvalService = new \App\service\FamilyCodeApprovalService($this->pdo);
            $row['approval_token'] = $approvalService->generateApprovalToken((int)$row['no']);
        }

        header('Content-Type: application/json');
        if ($row) {
            echo json_encode($row);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['error' => 'Not found']);
        }
        exit;
    }

    public function clearRateLimit(): never
    {
        try {
            $this->run("TRUNCATE TABLE rate_limiter");
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function seedTestUser(): never
    {
        $email = 'cypress_test@myfamilyplatform.com';
        $password = 'National2';
        $userId = \bin2hex(\random_bytes(8));

        // Check if user already exists
        $existing = $this->run("SELECT id FROM account WHERE email = ?", [$email])->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Test user already exists']);
            exit;
        }

        try {
            // Create account
            $this->run(
                "INSERT INTO account (id, email, password, deleted_at) VALUES (?, ?, ?, NULL)",
                [$userId, $email, \password_hash($password, PASSWORD_ARGON2ID)]
            );

            // Create personal data
            $this->run(
                "INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)",
                [$userId, 'Cypress', 'Test', 'TEST' . \strtoupper(\substr($userId, 0, 4))]
            );

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Test user created', 'id' => $userId]);
        } catch (\Throwable $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function setup(): never
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        try {
            $randA = \bin2hex(\random_bytes(4));
            $randB = \bin2hex(\random_bytes(4));

            $userAId = 'dast_user_a_' . $randA;
            $emailA = 'dast_a_' . $randA . '@test.myfamilyplatform.com';
            $famCodeA = 'DAST_FAM_A_' . \strtoupper($randA);

            $userBId = 'dast_user_b_' . $randB;
            $emailB = 'dast_b_' . $randB . '@test.myfamilyplatform.com';
            $famCodeB = 'DAST_FAM_B_' . \strtoupper($randB);

            $passwordHash = \password_hash('DastPass123!', PASSWORD_ARGON2ID);

            // User A & Family A (Root Member + Tree Relative Node + Post + Comment + Event)
            $this->run("INSERT INTO account (id, email, password, status, deleted_at) VALUES (?, ?, ?, 'active', NULL)", [$userAId, $emailA, $passwordHash]);
            $this->run("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'DASTUserA', 'FamilyA', ?)", [$userAId, $famCodeA]);
            $this->run("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio) VALUES (?, ?, 'Alice', 'FamilyA', 'Female', 2, 'DAST Root A')", [$famCodeA, $userAId]);
            $rootAId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio) VALUES (?, NULL, 'RelativeA', 'FamilyA', 'Female', 2, 'DAST Relative A')", [$famCodeA]);
            $nodeAId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO post (id, fullName, postFamCode, postMessage, post_status) VALUES (?, 'DASTUserA FamilyA', ?, 'DAST Post A by User A', 'published')", [$userAId, $famCodeA]);
            $postAId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO comment (id, post_no, fullName, comment, comment_status) VALUES (?, ?, 'DASTUserA FamilyA', 'DAST Comment A', 'published')", [$userAId, (string)$postAId]);
            $commentAId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO events (id, eventName, eventDate, eventType, eventCode) VALUES (?, 'DAST Event A', CURDATE(), 'Birthday', ?)", [$userAId, $famCodeA]);
            $eventANo = (int)$this->pdo->lastInsertId();

            // User B & Family B (Root Member + Tree Relative Node + Post + Comment + Event)
            $this->run("INSERT INTO account (id, email, password, status, deleted_at) VALUES (?, ?, ?, 'active', NULL)", [$userBId, $emailB, $passwordHash]);
            $this->run("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'DASTUserB', 'FamilyB', ?)", [$userBId, $famCodeB]);
            $this->run("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio) VALUES (?, ?, 'Bob', 'FamilyB', 'Male', 2, 'DAST Root B')", [$famCodeB, $userBId]);
            $rootBId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio) VALUES (?, NULL, 'RelativeB', 'FamilyB', 'Male', 2, 'DAST Relative B')", [$famCodeB]);
            $nodeBId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO post (id, fullName, postFamCode, postMessage, post_status) VALUES (?, 'DASTUserB FamilyB', ?, 'DAST Post B by User B', 'published')", [$userBId, $famCodeB]);
            $postBId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO comment (id, post_no, fullName, comment, comment_status) VALUES (?, ?, 'DASTUserB FamilyB', 'DAST Comment B', 'published')", [$userBId, (string)$postBId]);
            $commentBId = (int)$this->pdo->lastInsertId();
            $this->run("INSERT INTO events (id, eventName, eventDate, eventType, eventCode) VALUES (?, 'DAST Event B', CURDATE(), 'Reunion', ?)", [$userBId, $famCodeB]);
            $eventBNo = (int)$this->pdo->lastInsertId();

            $tokenA = \Src\JwtHandler::jwtEncodeData(['id' => $userAId, 'email' => $emailA, 'role' => 'users']);
            $tokenB = \Src\JwtHandler::jwtEncodeData(['id' => $userBId, 'email' => $emailB, 'role' => 'users']);

            $cleanupPayload = [
                'users' => [$userAId, $userBId],
                'famCodes' => [$famCodeA, $famCodeB],
                'nodes' => [$rootAId, $nodeAId, $rootBId, $nodeBId],
                'posts' => [$postAId, $postBId],
                'comments' => [$commentAId, $commentBId],
                'events' => [$eventANo, $eventBNo],
                'exp' => time() + 7200
            ];
            $cleanupToken = \base64_encode((string)\json_encode($cleanupPayload));

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'cleanup_token' => $cleanupToken,
                'cookie_name' => $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie',
                'data' => [
                    'manager_a' => [
                        'id' => $userAId,
                        'email' => $emailA,
                        'fam_code' => $famCodeA,
                        'token' => $tokenA
                    ],
                    'manager_b' => [
                        'id' => $userBId,
                        'email' => $emailB,
                        'fam_code' => $famCodeB,
                        'token' => $tokenB
                    ],
                    'user_a' => [
                        'id' => $userAId,
                        'email' => $emailA,
                        'fam_code' => $famCodeA,
                        'token' => $tokenA
                    ],
                    'user_b' => [
                        'id' => $userBId,
                        'email' => $emailB,
                        'fam_code' => $famCodeB,
                        'token' => $tokenB
                    ],
                    'event_a' => $famCodeA,
                    'event_b' => $famCodeB,
                    'fam_a' => $famCodeA,
                    'fam_b' => $famCodeB,
                    'node_a_id' => $nodeAId,
                    'node_b_id' => $nodeBId,
                    'prospect_a_id' => $nodeAId,
                    'prospect_b_id' => $nodeBId,
                    'post_a_id' => $postAId,
                    'post_b_id' => $postBId,
                    'comment_a_id' => $commentAId,
                    'comment_b_id' => $commentBId,
                    'event_a_no' => $eventANo,
                    'event_b_no' => $eventBNo
                ]
            ]);
            exit;
        } catch (\Throwable $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function teardown(): never
    {
        $rawInput = file_get_contents('php://input') ?: '';
        $json = json_decode($rawInput, true);
        $cleanupToken = (string)($json['cleanup_token'] ?? ($_POST['cleanup_token'] ?? ($_GET['cleanup_token'] ?? '')));

        if (empty($cleanupToken)) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing cleanup_token']);
            exit;
        }

        try {
            $payloadJson = \base64_decode($cleanupToken, true);
            if ($payloadJson === false) {
                header('HTTP/1.1 400 Bad Request');
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid cleanup_token format']);
                exit;
            }

            $payload = json_decode($payloadJson, true);
            if (!is_array($payload)) {
                header('HTTP/1.1 400 Bad Request');
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid cleanup_token payload']);
                exit;
            }

            $users = (array)($payload['users'] ?? []);
            $famCodes = (array)($payload['famCodes'] ?? []);
            $nodes = (array)($payload['nodes'] ?? []);
            $posts = (array)($payload['posts'] ?? []);
            $comments = (array)($payload['comments'] ?? []);
            $events = (array)($payload['events'] ?? []);

            foreach ($comments as $commentNo) {
                $this->run("DELETE FROM comment WHERE comment_no = ?", [(int)$commentNo]);
            }

            foreach ($posts as $postNo) {
                $this->run("DELETE FROM comment WHERE post_no = ?", [(string)$postNo]);
                $this->run("DELETE FROM post WHERE post_no = ?", [(int)$postNo]);
            }

            foreach ($events as $eventNo) {
                $this->run("DELETE FROM events WHERE no = ?", [(int)$eventNo]);
            }

            foreach ($nodes as $nodeId) {
                $this->run("DELETE FROM family_node_children WHERE child_id = ?", [(int)$nodeId]);
                $this->run("DELETE FROM family_nodes WHERE id = ?", [(int)$nodeId]);
            }

            foreach ($famCodes as $famCode) {
                $this->run("DELETE FROM family_unions WHERE family_code = ?", [(string)$famCode]);
                $this->run("DELETE FROM family_nodes WHERE family_code = ?", [(string)$famCode]);
            }

            foreach ($users as $userId) {
                $this->run("DELETE FROM personal WHERE id = ?", [(string)$userId]);
                $this->run("DELETE FROM account WHERE id = ?", [(string)$userId]);
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Cleaned up successfully']);
            exit;
        } catch (\Throwable $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}

