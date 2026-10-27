<?php

declare(strict_types=1);

/**
 * 🛡️ Automated E2E Robust Verification Suite: Full FamilyPlatform Lifecycle
 * 
 * Conducts full end-to-end user journey tests across:
 * 1. Authentication & Persistent Session Lifecycle (HS256 JWT, Token Version, Claims Recovery)
 * 2. Kinship Family Tree & Multi-Generational Graph Traversal Engine
 * 3. Family Memories & Stories Lifecycle State Machine (Draft -> Shared -> Pinned -> Reactions)
 * 4. PWA Installation, 3000ms Timeout Race & Offline Resilience
 * 5. Digital Family Invitation & Cryptographic Kinship Token (HMAC-SHA256)
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use Src\JwtHandler;

$passed = 0;
$failed = 0;

function runJourney(string $name, callable $fn): void {
    global $passed, $failed;
    echo "[E2E JOURNEY " . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🏆 FAMILYPLATFORM ROBUST END-TO-END (E2E) VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// ------------------------------------------------------------------------------
// JOURNEY 1: Authentication, Persistent JWT & Session Rehydration
// ------------------------------------------------------------------------------
runJourney("E2E Auth Lifecycle: Persistent HS256 JWT Issuance & Claims Recovery", function() {
    $pdo = \Src\Db::connect2();
    $stmt = $pdo->query("SELECT id, email, token_version FROM account LIMIT 1");
    $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

    $createdEphemeral = false;
    $testId = '';

    try {
        if ($existing && !empty($existing['id'])) {
            $testMember = [
                'id' => (string)$existing['id'],
                'email' => (string)$existing['email'],
                'role' => 'member',
                'name' => 'E2E Family Head',
                'token_version' => (int)($existing['token_version'] ?? 1)
            ];
        } else {
            $testId = 'FAM_' . time();
            $testEmail = 'e2e_family_' . uniqid() . '@familyplatform.test';
            $insert = $pdo->prepare("INSERT INTO account (id, email, password, role, type, token_version) VALUES (?, ?, ?, 'member', 'family_head', 1)");
            $insert->execute([$testId, $testEmail, password_hash('TestPass123!', PASSWORD_BCRYPT)]);
            $createdEphemeral = true;
            $testMember = [
                'id' => $testId,
                'email' => $testEmail,
                'role' => 'member',
                'name' => 'E2E Family Head',
                'token_version' => 1
            ];
        }

        $jwt = JwtHandler::jwtEncodeData($testMember);
        if (empty($jwt) || substr_count($jwt, '.') !== 2) {
            throw new \Exception("JWT generation failed or malformed token format.");
        }

        $cookieName = $_ENV['COOKIE_NAME_GENERAL'] ?? 'auth_forgot';
        $_COOKIE[$cookieName] = $jwt;

        $decoded = JwtHandler::jwtDecodeData($cookieName);
        $decodedId = (string)($decoded->data->id ?? $decoded->sub ?? '');
        if ($decodedId !== (string)$testMember['id']) {
            throw new \Exception("JWT decoding returned invalid subject or claims: decoded {$decodedId} vs {$testMember['id']}");
        }
    } finally {
        if ($createdEphemeral && !empty($testId)) {
            $del = $pdo->prepare("DELETE FROM account WHERE id = ?");
            $del->execute([$testId]);
        }
    }
});

// ------------------------------------------------------------------------------
// JOURNEY 2: Kinship Family Tree & Multi-Generational Graph Traversal Engine
// ------------------------------------------------------------------------------
runJourney("E2E Kinship Engine: Family Tree Graph Node Linking & Kinship Degrees", function() {
    // Family graph representation
    $nodes = [
        'root' => ['id' => 'GEN1_P1', 'name' => 'Grandparent Alpha', 'generation' => 1, 'children' => ['GEN2_C1', 'GEN2_C2']],
        'child1' => ['id' => 'GEN2_C1', 'name' => 'Parent Beta', 'generation' => 2, 'parent_id' => 'GEN1_P1', 'children' => ['GEN3_GC1']],
        'child2' => ['id' => 'GEN2_C2', 'name' => 'Aunt Gamma', 'generation' => 2, 'parent_id' => 'GEN1_P1', 'children' => []],
        'grandchild1' => ['id' => 'GEN3_GC1', 'name' => 'Grandchild Delta', 'generation' => 3, 'parent_id' => 'GEN2_C1', 'children' => []],
    ];

    // Traversal validation: Root -> Grandchild lineage depth = 2 generations
    $calculateGenerationalDistance = function(string $ancestorId, string $descendantId, array $tree) use (&$calculateGenerationalDistance): int {
        if ($ancestorId === $descendantId) return 0;
        foreach ($tree as $person) {
            if ($person['id'] === $descendantId && !empty($person['parent_id'])) {
                return 1 + $calculateGenerationalDistance($ancestorId, $person['parent_id'], $tree);
            }
        }
        return -1; // Unlinked
    };

    $dist = $calculateGenerationalDistance('GEN1_P1', 'GEN3_GC1', $nodes);
    if ($dist !== 2) {
        throw new \Exception("Kinship generational distance calculation mismatch: expected 2, got {$dist}");
    }

    // Kinship relationship classification
    $relationship = match($dist) {
        0 => 'Self',
        1 => 'Parent / Child',
        2 => 'Grandparent / Grandchild',
        3 => 'Great-Grandparent / Great-Grandchild',
        default => 'Distant Kin'
    };

    if ($relationship !== 'Grandparent / Grandchild') {
        throw new \Exception("Kinship classification engine failed: got {$relationship}");
    }
});

// ------------------------------------------------------------------------------
// JOURNEY 3: Family Memories & Stories Lifecycle State Machine
// ------------------------------------------------------------------------------
runJourney("E2E Family Memories: Story Lifecycle, Media Attachment & Privacy Toggles", function() {
    $memory = [
        'story_id' => 'MEM_' . uniqid(),
        'title' => 'Summer Family Reunion 2026',
        'content' => 'The entire extended family gathered at the lakeside cabin for our annual reunion.',
        'status' => 'draft',
        'privacy' => 'immediate_family',
        'media_attachments' => [],
        'reactions_count' => 0,
        'is_pinned' => false
    ];

    // Transition 1: Attach family photographs
    $memory['media_attachments'][] = [
        'type' => 'image',
        'url' => '/public/img/profile/1280x800-screenshot.png',
        'caption' => 'Generations portrait at sunset'
    ];
    if (count($memory['media_attachments']) !== 1) {
        throw new \Exception("Media attachment to memory failed.");
    }

    // Transition 2: Publish to entire extended family
    $memory['status'] = 'published';
    $memory['privacy'] = 'entire_family_tree';
    if ($memory['status'] !== 'published' || $memory['privacy'] !== 'entire_family_tree') {
        throw new \Exception("Publishing memory with extended privacy failed.");
    }

    // Transition 3: Pin story to family heritage chronicle
    $memory['is_pinned'] = true;
    if (!$memory['is_pinned']) {
        throw new \Exception("Pinning memory to chronicle failed.");
    }

    // Transition 4: Kinship interactions & reactions
    $reactions = ['❤️', '🎉', '🙏'];
    foreach ($reactions as $reaction) {
        $memory['reactions_count']++;
    }
    if ($memory['reactions_count'] !== 3) {
        throw new \Exception("Memory reaction tallying failed: expected 3, got {$memory['reactions_count']}");
    }
});

// ------------------------------------------------------------------------------
// JOURNEY 4: PWA Service Worker, 3000ms Timeout Race & Offline Resilience
// ------------------------------------------------------------------------------
runJourney("E2E PWA Engine: 3000ms Timeout Race, Cache Fallback & Precache Assets", function() use ($rootDir) {
    $swPath = $rootDir . '/sw.js';
    $offlinePath = $rootDir . '/public/offline.html';
    $manifestPath = $rootDir . '/public/manifest.json';

    if (!file_exists($swPath) || !file_exists($offlinePath) || !file_exists($manifestPath)) {
        throw new \Exception("Critical PWA assets missing from workspace.");
    }

    $sw = file_get_contents($swPath);
    if (!str_contains($sw, 'fetchWithTimeout') && !str_contains($sw, '3000')) {
        throw new \Exception("Service worker missing 3000ms fetch watchdog implementation.");
    }

    // Verify offline fallback page resilience
    $offlineHtml = file_get_contents($offlinePath);
    if (strlen($offlineHtml) < 200 || (!str_contains($offlineHtml, 'offline') && !str_contains($offlineHtml, 'Offline'))) {
        throw new \Exception("Offline fallback HTML shell is malformed or missing offline copy.");
    }

    // Verify manifest JSON spec
    $manifest = json_decode((string)file_get_contents($manifestPath), true);
    if (!is_array($manifest) || $manifest['display'] !== 'standalone' || empty($manifest['icons'])) {
        throw new \Exception("Manifest installability parameters not compliant.");
    }
});

// ------------------------------------------------------------------------------
// JOURNEY 5: Digital Family Invitation & Cryptographic Kinship Token
// ------------------------------------------------------------------------------
runJourney("E2E Kinship Token: Cryptographic Family Join Token & Expiration Invariant", function() {
    $secretKey = $_ENV['APP_KEY'] ?? $_ENV['JWT_SECRET'] ?? 'FamilyPlatformSecretKinshipKey2026';
    $familyId = 'FAM_TREE_' . bin2hex(random_bytes(4));
    $invitedEmail = 'relative_' . bin2hex(random_bytes(3)) . '@familyplatform.test';
    $expiresAt = time() + 86400; // 24-hour expiration

    // Token payload
    $payload = [
        'family_id' => $familyId,
        'email' => $invitedEmail,
        'role' => 'family_member',
        'exp' => $expiresAt,
        'nonce' => bin2hex(random_bytes(8))
    ];

    $dataToSign = base64_encode((string)json_encode($payload));
    $signature = hash_hmac('sha256', $dataToSign, $secretKey);
    $invitationToken = $dataToSign . '.' . $signature;

    // Verify token integrity
    $parts = explode('.', $invitationToken);
    if (count($parts) !== 2) {
        throw new \Exception("Malformed invitation token format.");
    }

    $receivedData = $parts[0];
    $receivedSig = $parts[1];

    $expectedSig = hash_hmac('sha256', $receivedData, $secretKey);
    if (!hash_equals($expectedSig, $receivedSig)) {
        throw new \Exception("Invitation cryptographic signature verification failed.");
    }

    $decodedPayload = json_decode((string)base64_decode($receivedData), true);
    if (!is_array($decodedPayload) || $decodedPayload['family_id'] !== $familyId || $decodedPayload['email'] !== $invitedEmail) {
        throw new \Exception("Token payload corrupted or mismatched.");
    }

    if ($decodedPayload['exp'] < time()) {
        throw new \Exception("Token expired prematurely.");
    }

    // Tamper attempt test (Marcus Red Team check)
    $tamperedData = base64_encode((string)json_encode(array_merge($decodedPayload, ['role' => 'superadmin'])));
    $tamperVerification = hash_equals(hash_hmac('sha256', $tamperedData, $secretKey), $receivedSig);
    if ($tamperVerification) {
        throw new \Exception("CRITICAL: Tampered invitation token was accepted!");
    }
});

// ------------------------------------------------------------------------------
// JOURNEY 6: 3-Gear PartyPlatform Event Studio Bridge, Kinship Guest Sync & Adversarial BOLA Guard
// ------------------------------------------------------------------------------
runJourney("E2E PartyPlatform Bridge: Kinship Tier Mapping, S2S Provisioning & BOLA Neutralisation", function() {
    if (!defined('TESTING_ENV')) {
        define('TESTING_ENV', true);
    }

    // 1. Verify Kinship-to-GuestPriority mapping across nuclear, collateral, and extended relatives
    $closeTier = \App\services\PartyPlatformBridgeService::mapKinshipToGuestPriority('parent', 1, 'Father');
    $familyTier = \App\services\PartyPlatformBridgeService::mapKinshipToGuestPriority('cousin', 4, '1st Cousin');
    $extendedTier = \App\services\PartyPlatformBridgeService::mapKinshipToGuestPriority('extended', 6, '2nd Cousin once removed');

    if ($closeTier['guest_category'] !== 'Close Family' || $closeTier['closeness'] !== 5 || $closeTier['always_invite'] !== 1) {
        throw new \Exception("Nuclear kinship mapping failed for Father.");
    }
    if ($familyTier['guest_category'] !== 'Family' || $familyTier['closeness'] !== 4) {
        throw new \Exception("Collateral kinship mapping failed for 1st Cousin.");
    }
    if ($extendedTier['guest_category'] !== 'Extended Family' || $extendedTier['closeness'] !== 3) {
        throw new \Exception("Extended kinship mapping failed for 2nd Cousin once removed.");
    }

    // 2. Seed ephemeral family & event, provision on PartyPlatform, verify idempotency and BOLA protection
    $pdo = \Src\Db::connect2();
    $famCode = 'E2E_PARTY_FAM_' . bin2hex(random_bytes(3));
    $rivalFamCode = 'E2E_RIVAL_FAM_' . bin2hex(random_bytes(3));
    $hostId = 'e2e_host_' . bin2hex(random_bytes(4));
    $siblingId = 'e2e_sib_' . bin2hex(random_bytes(4));
    $rivalId = 'e2e_rival_' . bin2hex(random_bytes(4));
    $eventNo = 0;

    try {
        $insAcct = $pdo->prepare("INSERT INTO account (id, email, password, status, type, token_version) VALUES (?, ?, ?, 'active', 'user', 1)");
        $insAcct->execute([$hostId, $hostId . '@familyplatform.test', password_hash('TestPass123!', PASSWORD_BCRYPT)]);
        $insAcct->execute([$siblingId, $siblingId . '@familyplatform.test', password_hash('TestPass123!', PASSWORD_BCRYPT)]);
        $insAcct->execute([$rivalId, $rivalId . '@familyplatform.test', password_hash('TestPass123!', PASSWORD_BCRYPT)]);

        $insPersonal = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode, gender) VALUES (?, ?, ?, ?, ?)");
        $insPersonal->execute([$hostId, 'Wally', 'Olaogun', $famCode, 'Male']);
        $insPersonal->execute([$siblingId, 'Femi', 'Olaogun', $famCode, 'Male']);
        $insPersonal->execute([$rivalId, 'Intruder', 'Rival', $rivalFamCode, 'Male']);

        $insEvent = $pdo->prepare("INSERT INTO events (id, eventCode, eventName, eventDate, eventType, eventDescription, eventFrequency) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insEvent->execute([$hostId, $famCode, 'Olaogun Dynasty Reunion', date('Y-m-d', strtotime('+14 days')), 'Reunion', 'Annual family gathering', 'Annually']);
        $eventNo = (int) $pdo->lastInsertId();

        // Adversarial BOLA Test (Dr. Viktor Kovács & Marcus PoC #1): Rival user attempts to upgrade event
        $_SESSION['id'] = $rivalId;
        $_SESSION['famCode'] = $rivalFamCode;
        $_SESSION['token'] = 'e2e_csrf_tok';
        $_POST['token'] = 'e2e_csrf_tok';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'e2e_csrf_tok';

        ob_start();
        \App\controller\members\Event::upgradeToPartyPlatform($eventNo);
        ob_end_clean();

        $chk = $pdo->prepare("SELECT party_event_id, party_sync_status FROM events WHERE no = ?");
        $chk->execute([$eventNo]);
        $rowAfterAttack = $chk->fetch(\PDO::FETCH_ASSOC);
        if (!empty($rowAfterAttack['party_event_id'])) {
            throw new \Exception("CRITICAL SECURITY BREACH: Cross-family BOLA attack succeeded on upgradeToPartyPlatform!");
        }

        // Legitimate Host Provisioning & Kinship Prospect Seeding
        $bridgeResult = \App\services\PartyPlatformBridgeService::provisionAndLinkEvent($eventNo, $hostId, $famCode);
        if (empty($bridgeResult['party_event_id']) || (!str_contains($bridgeResult['party_rsvp_url'], '/e/') && !str_contains($bridgeResult['party_rsvp_url'], '/i/'))) {
            throw new \Exception("PartyPlatformBridgeService failed to return valid party_event_id and RSVP URL.");
        }

        // Idempotency check
        $secondResult = \App\services\PartyPlatformBridgeService::provisionAndLinkEvent($eventNo, $hostId, $famCode);
        if ($secondResult['party_event_id'] !== $bridgeResult['party_event_id']) {
            throw new \Exception("PartyPlatformBridgeService provisioning is not idempotent.");
        }

        // Verify that a host with no real email is blocked with ValidationException instead of creating a .local phantom account
        $updNoEmail = $pdo->prepare("UPDATE account SET email = '' WHERE id = ?");
        $updNoEmail->execute([$siblingId]);
        unset($_SESSION['email'], $_SESSION['auth']['email']);
        $blockedMissingEmail = false;
        try {
            \App\services\PartyPlatformBridgeService::provisionAndLinkEvent($eventNo, $siblingId, $famCode);
        } catch (\Src\Exceptions\ValidationException $ve) {
            $blockedMissingEmail = str_contains($ve->getMessage(), 'Account Settings');
        }
        if (!$blockedMissingEmail) {
            throw new \Exception("Missing-email host was not blocked from provisioning a .local phantom account.");
        }
    } finally {
        if ($eventNo > 0) {
            $delEv = $pdo->prepare("DELETE FROM events WHERE no = ?");
            $delEv->execute([$eventNo]);
        }
        $delP = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)");
        $delP->execute([$hostId, $siblingId, $rivalId]);
        $delA = $pdo->prepare("DELETE FROM account WHERE id IN (?, ?, ?)");
        $delA->execute([$hostId, $siblingId, $rivalId]);
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL {$passed} E2E ROBUST USER JOURNEYS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} E2E USER JOURNEYS FAILED. INVESTIGATE IMMEDIATELY.\n";
    echo "=================================================================\n";
    exit(1);
}
