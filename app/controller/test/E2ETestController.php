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
}
