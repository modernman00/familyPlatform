<?php

namespace App\controller\test;

use PDO;

class E2ETestController
{
    private PDO $pdo;

    public function __construct()
    {
        // Enforce that this is only used in non-prod
        if (!in_array((string) ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: ''), ['local', 'development', 'testing'], true)) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        try {
            $this->pdo = \Src\Db::connect2();
        } catch (\Throwable $e) {
            $this->pdo = (new \Src\Db())->connect();
        }
    }

    public function getValidFamilyCode()
    {
        $stmt = $this->pdo->query("SELECT famCode as code FROM personal LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($row ?: ['code' => 'NONE']);
        exit;
    }

    public function getValidFamilyCodeWithInviter()
    {
        $stmt = $this->pdo->query("SELECT p.famCode, p.firstName, p.lastName, a.email FROM personal p JOIN account a ON p.id = a.id WHERE a.email IS NOT NULL AND p.famCode IS NOT NULL LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        if ($row) {
            echo json_encode([
                'code' => $row['famCode'],
                'inviter' => [
                    'firstName' => $row['firstName'],
                    'lastName' => $row['lastName'],
                    'email' => $row['email']
                ]
            ]);
        } else {
            echo json_encode(['error' => 'No valid inviter found']);
        }
        exit;
    }

    public function getPendingApprovalRequests()
    {
        $stmt = $this->pdo->query("SELECT * FROM family_approval_requests WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $approvalService = new \App\service\FamilyCodeApprovalService($this->pdo);

        foreach ($rows as &$row) {
            $row['approval_token'] = $approvalService->generateApprovalToken((int)$row['no']);
        }

        header('Content-Type: application/json');
        echo json_encode($rows);
        exit;
    }

    public function getApprovalRequest($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM family_approval_requests WHERE no = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

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
}
