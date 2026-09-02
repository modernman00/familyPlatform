<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * Base for tests that exercise FamilyRequestController against the real dev
 * database (Src\Db has no test-DB config of its own — see Db::dbVariables()).
 * Every row a test creates is tracked and deleted in tearDown() so the suite
 * never leaves fixture data behind in a database also used for local dev/QA.
 */
abstract class FamilyRequestTestCase extends TestCase
{
    protected PDO $pdo;

    /** @var array<string, list<string>> table => list of `id`-ish values to delete */
    private array $createdIds = [
        'personal' => [],
        'contact' => [],
        'requestMgt' => [],
        'notification' => [],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER['HTTP_X_XSRF_TOKEN'] = '';
        // Matches what axios actually sends for these JSON POST/PUT/DELETE calls —
        // the global showError() helper (vendor helpers.php) only returns JSON
        // instead of an HTML error page when it recognises the request as an API
        // call (see its $isApi detection), and CONTENT_TYPE is the header axios sets.
        $_SERVER['CONTENT_TYPE'] = 'application/json';
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds['notification'] as $id) {
            $this->pdo->prepare('DELETE FROM notification WHERE sender_id = ? OR receiver_id = ?')
                ->execute([$id, $id]);
        }
        foreach ($this->createdIds['requestMgt'] as $id) {
            $this->pdo->prepare('DELETE FROM requestMgt WHERE approver_id = ? OR requester_id = ?')
                ->execute([$id, $id]);
        }
        foreach ($this->createdIds['contact'] as $id) {
            $this->pdo->prepare('DELETE FROM contact WHERE id = ?')->execute([$id]);
        }
        foreach ($this->createdIds['personal'] as $id) {
            $this->pdo->prepare('DELETE FROM personal WHERE id = ?')->execute([$id]);
        }

        parent::tearDown();
    }

    /** A collision-safe test member id, distinguishable in the DB from real users. */
    protected function testMemberId(string $label): string
    {
        return 'PHPUNIT_' . strtoupper($label) . '_' . bin2hex(random_bytes(4));
    }

    /**
     * Seeds the minimum `personal` row BaseController::findMemberById()/
     * membersData() require (id + famCode + firstName + lastName; every other
     * table those methods touch is optional — see SingleCustomerData::getCustomerData()).
     */
    protected function seedMember(string $id, string $famCode, string $firstName, string $lastName, string $gender = 'Male'): void
    {
        // `personal` has four NOT NULL columns with no DB default (day, month, year, kids).
        // MySQL strict mode rejects INSERTs that omit them, so we supply neutral test values.
        $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
             VALUES (?, ?, ?, ?, ?, 1, ?, 2000, 0)'
        )->execute([$id, $firstName, $lastName, $famCode, $gender, 'Jan']);

        $this->createdIds['personal'][] = $id;
        $this->createdIds['requestMgt'][] = $id;
        $this->createdIds['notification'][] = $id;
    }

    /**
     * Seeds a `contact` row. Only needed for members a test expects to
     * actually receive email through the real send path (e.g. the requester
     * in an approval — see SendEmailFunctionality::email() reading $req['email']),
     * since findMemberById() treats `contact` as optional otherwise.
     */
    protected function seedContact(string $id, string $email, string $country = 'UK', string $mobile = '07000000000'): void
    {
        $this->pdo->prepare(
            'INSERT INTO contact (id, email, country, mobile) VALUES (?, ?, ?, ?)'
        )->execute([$id, $email, $country, $mobile]);

        $this->createdIds['contact'][] = $id;
    }

    protected function requestMgtRow(string $approverId, string $requesterId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM requestMgt WHERE approver_id = ? AND requester_id = ? ORDER BY no DESC LIMIT 1'
        );
        $stmt->execute([$approverId, $requesterId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    protected function latestNotificationFor(string $receiverId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notification WHERE receiver_id = ? ORDER BY no DESC LIMIT 1'
        );
        $stmt->execute([$receiverId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Captures everything the controller echoes and decodes it as JSON. */
    protected function captureJsonOutput(callable $callback): array
    {
        ob_start();
        try {
            $callback();
        } finally {
            $output = ob_get_clean();
        }

        $decoded = json_decode($output !== false ? $output : '', true);
        $this->assertIsArray($decoded, "Expected JSON output, got: $output");
        return $decoded;
    }
}
