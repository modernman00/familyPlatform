<?php
declare(strict_types=1);

/**
 * E2E fixture for the family-migration journey.
 *
 *   php scripts/e2e_family_migration_fixture.php seed_for_cypress_user
 *   php scripts/e2e_family_migration_fixture.php cleanup
 *
 * Seeds pending migration cards directly for cypress_test@myfamilyplatform.com,
 * and cleans up deterministically without touching real production or dev data.
 */

require_once __DIR__ . '/../tests/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\services\FamilyMigrationService;
use Src\Db;

const E2E_PASSWORD = 'National2';

$pdo = Db::connect2();
$mode = $argv[1] ?? 'seed_for_cypress_user';

function e2eCleanup(PDO $pdo): void
{
    $ids = $pdo->query("SELECT id FROM account WHERE id LIKE 'e2e_mig_%'")->fetchAll(PDO::FETCH_COLUMN);
    $emails = $pdo->query("SELECT email FROM account WHERE id LIKE 'e2e_mig_%'")->fetchAll(PDO::FETCH_COLUMN);
    if ($ids !== []) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare(sprintf('DELETE FROM family_migration_requests WHERE requester_id IN (%s) OR target_user_id IN (%s)', $in, $in))
            ->execute(array_merge($ids, $ids));
        foreach (['personal' => 'id', 'account' => 'id', 'code_mgt' => 'id', 'user_families' => 'user_id', 'family_nodes' => 'user_id'] as $table => $col) {
            $pdo->prepare(sprintf('DELETE FROM %s WHERE %s IN (%s)', $table, $col, $in))->execute($ids);
        }
    }
    foreach ($emails as $email) {
        $pdo->prepare('DELETE FROM code_mgt WHERE email = ?')->execute([$email]);
    }
    $pdo->exec("DELETE FROM user_families WHERE family_code LIKE 'E2EMIG%'");
    $pdo->exec("DELETE FROM family_nodes WHERE family_code LIKE 'E2EMIG%'");
}

e2eCleanup($pdo);
if ($mode === 'cleanup') {
    echo json_encode(['cleaned' => true]), "\n";
    exit(0);
}

// Find the Cypress test user
$stmtCyp = $pdo->prepare('SELECT a.id, p.famCode FROM account a JOIN personal p ON p.id = a.id WHERE a.email = ? LIMIT 1');
$stmtCyp->execute(['cypress_test@myfamilyplatform.com']);
$cypressUser = $stmtCyp->fetch(PDO::FETCH_ASSOC);

if (!$cypressUser) {
    echo json_encode(['error' => 'Cypress test user not found']), "\n";
    exit(1);
}

$cypressUserId = (string)$cypressUser['id'];
$cypressFamCode = (string)$cypressUser['famCode'];

$suffix = bin2hex(random_bytes(3));
$hash = password_hash(E2E_PASSWORD, PASSWORD_DEFAULT);

// 1. Create a user who sends a Join Request to Cypress user's family
$reqUser = [
    'id' => 'e2e_mig_req_' . $suffix,
    'fam' => 'E2EMIGREQ',
    'first' => 'Kemi',
    'email' => "e2e_mig_req_{$suffix}@myfamilyplatform.com",
];

$pdo->prepare('INSERT INTO account (id, email, password, status, created_at) VALUES (?, ?, ?, "active", NOW())')
    ->execute([$reqUser['id'], $reqUser['email'], $hash]);
$pdo->prepare('INSERT INTO personal (id, famCode, firstName, lastName, created_at) VALUES (?, ?, ?, "Joiner", NOW())')
    ->execute([$reqUser['id'], $reqUser['fam'], $reqUser['first']]);
$pdo->prepare('INSERT INTO code_mgt (id, code) VALUES (?, ?)')->execute([$reqUser['id'], $reqUser['fam']]);
$pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")')
    ->execute([$reqUser['id'], $reqUser['fam']]);

// 2. Create a user who sends a Nudge to the Cypress user
$nudgerUser = [
    'id' => 'e2e_mig_ndg_' . $suffix,
    'fam' => 'E2EMIGNDG',
    'first' => 'Femi',
    'email' => "e2e_mig_ndg_{$suffix}@myfamilyplatform.com",
];

$pdo->prepare('INSERT INTO account (id, email, password, status, created_at) VALUES (?, ?, ?, "active", NOW())')
    ->execute([$nudgerUser['id'], $nudgerUser['email'], $hash]);
$pdo->prepare('INSERT INTO personal (id, famCode, firstName, lastName, created_at) VALUES (?, ?, ?, "Nudger", NOW())')
    ->execute([$nudgerUser['id'], $nudgerUser['fam'], $nudgerUser['first']]);
$pdo->prepare('INSERT INTO code_mgt (id, code) VALUES (?, ?)')->execute([$nudgerUser['id'], $nudgerUser['fam']]);
$pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")')
    ->execute([$nudgerUser['id'], $nudgerUser['fam']]);

$service = new FamilyMigrationService($pdo);

// Request from reqUser to join Cypress user's family
$reqRes = $service->createRequest($reqUser['id'], $cypressFamCode, $cypressUserId, 'Please welcome me to our family code!');

// Nudge from nudgerUser inviting Cypress user to their family
$nudgeRes = $service->createNudge($nudgerUser['id'], $cypressUserId, 'Come join our ancestral branch!');

echo json_encode([
    'success' => true,
    'cypressUser' => $cypressUser,
    'request' => [
        'migrationId' => $reqRes['migration_id'] ?? null,
        'token' => $reqRes['token'] ?? null,
        'user' => $reqUser,
    ],
    'nudge' => [
        'migrationId' => $nudgeRes['migration_id'] ?? null,
        'token' => $nudgeRes['token'] ?? null,
        'user' => $nudgerUser,
    ],
]), "\n";
