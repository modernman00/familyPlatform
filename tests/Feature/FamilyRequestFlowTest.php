<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\FamilyRequestController;
use Tests\Support\FamilyRequestTestCase;
use Tests\Support\PhpInputStreamWrapper;

/**
 * Covers the backend half of the friend-request flow end to end against the
 * real dev database (and, per team decision, real SMTP/Pusher — see the
 * project's PHPUnit setup discussion): request created -> pending row +
 * notification + email + push attempt -> approve -> status flips to
 * "approved" (which IS network membership; see AllMembersData's joins on
 * requestMgt.status) -> approval notification back to the requester.
 *
 * What this suite does NOT verify: actual email delivery/content, actual
 * push delivery to a device, or Pusher delivery to a subscribed client —
 * those need a mailbox/device/websocket client watching, not a PHP process.
 * It verifies the DB side effects and that the send calls complete without
 * throwing.
 */
final class FamilyRequestFlowTest extends FamilyRequestTestCase
{
    private function authenticateAs(string $id, string $token = 'phpunit-fixed-token'): void
    {
        $_SESSION['id'] = $id;
        $_SESSION['token'] = $token;
        $_SERVER['HTTP_X_XSRF_TOKEN'] = $token;
    }

    private function sendFriendRequest(array $approver): array
    {
        PhpInputStreamWrapper::setContent(json_encode([
            'approver' => $approver,
            'emailPath' => 'msg/request_premium',
        ]));
        PhpInputStreamWrapper::register();

        try {
            return $this->captureJsonOutput(fn() => FamilyRequestController::request());
        } finally {
            PhpInputStreamWrapper::restore();
        }
    }

    public function test_request_creates_pending_request_and_notification(): void
    {
        $requesterId = $this->testMemberId('requester');
        $approverId = $this->testMemberId('approver');
        $this->seedMember($requesterId, 'REQFAM', 'Requester', 'Person');
        $this->seedMember($approverId, 'APPFAM', 'Approver', 'Person');

        $this->authenticateAs($requesterId);

        $response = $this->sendFriendRequest([
            'approverId' => $approverId,
            'approverCode' => 'APPFAM',
            'approverEmail' => $_ENV['TEST_EMAIL'] ?? 'waledevtest@gmail.com',
            'approverFirstName' => 'Approver',
            'approverLastName' => 'Person',
        ]);

        $this->assertSame('success', $response['status'] ?? null);
        $this->assertSame('Request sent', $response['message'] ?? null);

        $row = $this->requestMgtRow($approverId, $requesterId);
        $this->assertNotNull($row, 'Expected a requestMgt row for this approver/requester pair.');
        $this->assertSame('Request sent', $row['status']);

        $notification = $this->latestNotificationFor($approverId);
        $this->assertNotNull($notification, 'Expected the approver to receive a notification row.');
        $this->assertSame('Friend Request', $notification['notification_type']);
        $this->assertSame($requesterId, $notification['sender_id']);
    }

    public function test_duplicate_pending_request_is_rejected(): void
    {
        $requesterId = $this->testMemberId('requester');
        $approverId = $this->testMemberId('approver');
        $this->seedMember($requesterId, 'REQFAM', 'Requester', 'Person');
        $this->seedMember($approverId, 'APPFAM', 'Approver', 'Person');

        $this->authenticateAs($requesterId);
        $approver = [
            'approverId' => $approverId,
            'approverCode' => 'APPFAM',
            'approverEmail' => $_ENV['TEST_EMAIL'] ?? 'waledevtest@gmail.com',
            'approverFirstName' => 'Approver',
            'approverLastName' => 'Person',
        ];

        $first = $this->sendFriendRequest($approver);
        $this->assertSame('success', $first['status'] ?? null);

        $second = $this->sendFriendRequest($approver);
        $this->assertSame('error', $second['status'] ?? null);
        $this->assertSame('Request already pending', $second['message'] ?? null);

        // Only one row should exist — the duplicate must not have inserted another.
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM requestMgt WHERE approver_id = ? AND requester_id = ?');
        $stmt->execute([$approverId, $requesterId]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function test_approving_a_request_grants_network_membership_and_notifies_requester(): void
    {
        $requesterId = $this->testMemberId('requester');
        $approverId = $this->testMemberId('approver');
        $this->seedMember($requesterId, 'REQFAM', 'Requester', 'Person');
        $this->seedMember($approverId, 'APPFAM', 'Approver', 'Person');
        // The "approved" branch really emails the requester (real SMTP, per
        // this suite's setup) — needs a real deliverable-looking address.
        $this->seedContact($requesterId, $_ENV['TEST_EMAIL'] ?? 'waledevtest@gmail.com');

        // Seed the pending request directly — approveDelete() only reads/writes
        // requestMgt, it doesn't go through request().
        $this->pdo->prepare(
            'INSERT INTO requestMgt (approver_id, requester_id, status, requesterCode, approverCode) VALUES (?, ?, ?, ?, ?)'
        )->execute([$approverId, $requesterId, 'Request sent', 'REQFAM', 'APPFAM']);

        // dec=50 -> approved; src='pp' now just issues a redirect to /profilePage
        // with no response body (the JSON success payload was dropped in the
        // Phase-4 ApiResponse work), so the DB side effects below are the check.
        ob_start();
        try {
            FamilyRequestController::approveDelete($requesterId, $approverId, '50', 'APPFAM', 'pp');
        } finally {
            ob_end_clean();
        }

        $row = $this->requestMgtRow($approverId, $requesterId);
        $this->assertNotNull($row);
        $this->assertSame('approved', $row['status'], 'An approved status is what makes the requester part of the approver\'s network (see AllMembersData\'s joins on requestMgt.status).');

        $notification = $this->latestNotificationFor($requesterId);
        $this->assertNotNull($notification, 'Expected the requester to be notified of the approval.');
        $this->assertSame($approverId, $notification['sender_id']);
        $this->assertSame('Request approval', $notification['notification_name']);
    }

    public function test_rejecting_a_request_does_not_grant_network_membership(): void
    {
        $requesterId = $this->testMemberId('requester');
        $approverId = $this->testMemberId('approver');
        $this->seedMember($requesterId, 'REQFAM', 'Requester', 'Person');
        $this->seedMember($approverId, 'APPFAM', 'Approver', 'Person');

        $this->pdo->prepare(
            'INSERT INTO requestMgt (approver_id, requester_id, status, requesterCode, approverCode) VALUES (?, ?, ?, ?, ?)'
        )->execute([$approverId, $requesterId, 'Request sent', 'REQFAM', 'APPFAM']);

        // dec=10 -> rejected; this branch sends no email/push, and — unlike the
        // approved branch — echoes no JSON body either, so just discard output.
        ob_start();
        try {
            FamilyRequestController::approveDelete($requesterId, $approverId, '10', 'APPFAM', null);
        } finally {
            ob_end_clean();
        }

        $row = $this->requestMgtRow($approverId, $requesterId);
        $this->assertNotNull($row);
        $this->assertSame('rejected', $row['status']);
    }

    public function test_get_friend_request_data_lists_pending_requesters_for_the_approver(): void
    {
        $requesterId = $this->testMemberId('requester');
        $approverId = $this->testMemberId('approver');
        $this->seedMember($requesterId, 'REQFAM', 'Requester', 'Person');
        $this->seedMember($approverId, 'APPFAM', 'Approver', 'Person');

        $this->pdo->prepare(
            'INSERT INTO requestMgt (approver_id, requester_id, status, requesterCode, approverCode) VALUES (?, ?, ?, ?, ?)'
        )->execute([$approverId, $requesterId, 'Request sent', 'REQFAM', 'APPFAM']);

        $this->authenticateAs($approverId);

        $response = $this->captureJsonOutput(fn() => FamilyRequestController::getFriendRequestData());

        $this->assertSame('success', $response['status'] ?? null);
        $ids = array_column($response['message'], 'id');
        $this->assertContains($requesterId, $ids, 'The pending requester should show up on the approver\'s incoming-requests list.');
    }
}
