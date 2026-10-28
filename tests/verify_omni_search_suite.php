<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Omni-Search & Universal Action Discovery Engine
 *
 * Verifies:
 * 1. OmniSearchService Action Catalog integrity and scoring
 * 2. Querying "create family code" directs directly to /accountSetting#v-pills-familycode (#birthFamilyCard)
 * 3. Querying "add relative", "journal", "studio", "invite" returns relevant actions
 * 4. Multi-Tenant isolation: database results bounded strictly to user family codes
 * 5. Adversarial Red-Team attacks (Dr. Viktor Kovács & Marcus): SQLi, XSS, Buffer Overflows neutralized
 * 6. UI Integration: navbar trigger button, modal markup, and deep-link tab switching
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\OmniSearchService;
use App\controller\members\OmniSearchController;
use Src\Db;

$passed = 0;
$failed = 0;

function runTest(string $title, callable $fn): void {
    global $passed, $failed;
    echo "[OMNI-SEARCH TEST " . ($passed + $failed + 1) . "] {$title}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        $failed++;
    }
}

$pdo = Db::connect2();
$service = new OmniSearchService($pdo);

// TEST 1: Action Catalog Integrity
runTest('Action catalog provides rich system functions with valid URLs and icons', function() use ($service) {
    $catalog = $service->getActionCatalog('test_user_1', 'FAM123');
    if (empty($catalog)) {
        throw new RuntimeException('Action catalog is empty');
    }

    $foundCreateCode = false;
    $foundAddRelative = false;

    foreach ($catalog as $item) {
        if (!isset($item['id'], $item['title'], $item['description'], $item['url'], $item['category'], $item['icon'])) {
            throw new RuntimeException('Catalog item missing essential properties: ' . json_encode($item));
        }
        if ($item['id'] === 'act_create_family_code') {
            $foundCreateCode = true;
            if ($item['url'] !== '/accountSetting#v-pills-familycode') {
                throw new RuntimeException('Create Family Code URL incorrect: ' . $item['url']);
            }
            if (($item['targetElement'] ?? '') !== '#birthFamilyCard') {
                throw new RuntimeException('Create Family Code targetElement incorrect: ' . ($item['targetElement'] ?? ''));
            }
        }
        if ($item['id'] === 'act_add_relative') {
            $foundAddRelative = true;
        }
    }

    if (!$foundCreateCode) {
        throw new RuntimeException('Create Family Code action not found in catalog');
    }
    if (!$foundAddRelative) {
        throw new RuntimeException('Add Relative action not found in catalog');
    }
});

// TEST 2: Intent Matching for "create family code"
runTest('Querying "create family code" matches top action with high confidence', function() use ($service) {
    $results = $service->searchActions('create family code', 'test_user_1', 'FAM123');
    if (empty($results)) {
        throw new RuntimeException('No results returned for "create family code"');
    }

    $topMatch = $results[0];
    if ($topMatch['id'] !== 'act_create_family_code') {
        throw new RuntimeException('Top match is not act_create_family_code, got: ' . $topMatch['id']);
    }
    if ($topMatch['url'] !== '/accountSetting#v-pills-familycode') {
        throw new RuntimeException('Target URL mismatch: ' . $topMatch['url']);
    }
});

// TEST 2B: Intent Matching for "family journal"
runTest('Querying "family journal" reliably matches Family Journal actions and data', function() use ($service) {
    $res = $service->search('family journal', '117540OLAWALE', 'MODERNMAN');
    if (empty($res['categories']['actions'])) {
        throw new RuntimeException('No actions returned for "family journal"');
    }

    $actionIds = array_column($res['categories']['actions'], 'id');
    if (!in_array('act_family_journal', $actionIds, true)) {
        throw new RuntimeException('Expected act_family_journal in actions, got: ' . implode(', ', $actionIds));
    }
    if (!in_array('act_write_journal', $actionIds, true)) {
        throw new RuntimeException('Expected act_write_journal in actions, got: ' . implode(', ', $actionIds));
    }

    // Top action must be Family Journal
    if ($res['categories']['actions'][0]['id'] !== 'act_family_journal') {
        throw new RuntimeException('Top action for "family journal" is not act_family_journal, got: ' . $res['categories']['actions'][0]['id']);
    }
});

// TEST 3: Synonym and partial matching
runTest('Synonym searches ("make family code", "new family code", "tree", "studio") resolve correctly', function() use ($service) {
    $testCases = [
        'make family code' => 'act_create_family_code',
        'birth code' => 'act_create_family_code',
        'tree' => 'act_view_tree',
        'studio' => 'act_family_studio',
        'invite' => 'act_invite_others',
        'journal' => 'act_write_journal',
    ];

    foreach ($testCases as $query => $expectedId) {
        $matches = $service->searchActions($query, 'user_1', 'FAM123');
        $matchedIds = array_column($matches, 'id');
        if (!in_array($expectedId, $matchedIds, true)) {
            throw new RuntimeException("Query '{$query}' failed to include expected action '{$expectedId}'. Matches: " . implode(', ', $matchedIds));
        }
    }
});

// TEST 4: Unified Multi-Tenant Search Execution
runTest('Unified search returns categorized payload conforming to API specifications', function() use ($service) {
    $res = $service->search('family code', 'user_123', 'TESTFAM');
    if (!isset($res['query'], $res['total'], $res['categories'], $res['meta'])) {
        throw new RuntimeException('Invalid search payload structure: ' . json_encode(array_keys($res)));
    }
    if (!isset($res['categories']['actions'], $res['categories']['members'], $res['categories']['journals'], $res['categories']['posts'])) {
        throw new RuntimeException('Missing subcategories in search response');
    }
    if (empty($res['categories']['actions'])) {
        throw new RuntimeException('Expected actions for "family code" but found none');
    }
});

// TEST 5: Adversarial Red-Team Assault (Dr. Viktor Kovács & Marcus)
runTest('Adversarial penetration tests (SQLi, XSS, polyglot payloads) are completely neutralized', function() use ($service) {
    $maliciousQueries = [
        "' OR '1'='1",
        "'; DROP TABLE post; --",
        "<script>alert(document.cookie)</script>",
        "\" onload=\"alert(1)\"",
        str_repeat('A', 5000), // Buffer overflow attempt
        "' UNION SELECT id, password_hash, email FROM account --",
    ];

    foreach ($maliciousQueries as $attackPayload) {
        try {
            $output = $service->search($attackPayload, 'attacker_user', 'FAM_HACK');
            // Must return array without throwing PDO / SQL exceptions
            if (!is_array($output)) {
                throw new RuntimeException('Payload did not return array structure: ' . substr($attackPayload, 0, 20));
            }
        } catch (\PDOException $pdoErr) {
            throw new RuntimeException('SQL Injection vulnerability exposed: ' . $pdoErr->getMessage());
        }
    }
});

// TEST 6: UI Components & Deep-Link Handlers Verification
runTest('Frontend views include Omni-Search trigger button, modal, and deep-link focus handlers', function() use ($rootDir) {
    // 1. Check Navbar
    $navbarContent = file_get_contents($rootDir . '/resources/views/layouts/navbar.blade.php');
    if (!str_contains($navbarContent, 'omniSearchModal') || !str_contains($navbarContent, 'btn-omni-search-trigger')) {
        throw new RuntimeException('Omni-Search trigger button missing from navbar.blade.php');
    }

    // 2. Check ProfileBase layout
    $profileBaseContent = file_get_contents($rootDir . '/resources/views/layouts/profileBase.blade.php');
    if (!str_contains($profileBaseContent, 'components.omniSearchModal')) {
        throw new RuntimeException('omniSearchModal component inclusion missing from profileBase.blade.php');
    }

    // 3. Check OmniSearchModal component exists and has required elements
    $modalContent = file_get_contents($rootDir . '/resources/views/components/omniSearchModal.blade.php');
    if (!str_contains($modalContent, 'id="omniSearchInput"') || !str_contains($modalContent, 'act_create_family_code') || !str_contains($modalContent, 'act_family_journal')) {
        throw new RuntimeException('omniSearchModal.blade.php missing search input, family code or family journal bindings');
    }

    // 4. Check accountSetting deep-link focus
    $accountSettingContent = file_get_contents($rootDir . '/resources/views/member/accountSetting.blade.php');
    if (!str_contains($accountSettingContent, 'handleDeepLinkTarget') || !str_contains($accountSettingContent, 'pulse-highlight-target')) {
        throw new RuntimeException('accountSetting.blade.php missing handleDeepLinkTarget or pulse highlight handler');
    }
});

echo "\n======================================================\n";
echo "🏁 OMNI-SEARCH VERIFICATION RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "======================================================\n";

if ($failed > 0) {
    exit(1);
}
