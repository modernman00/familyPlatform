<?php

declare(strict_types=1);

/**
 * Automated Verification Suite: 3-Month Upcoming Family Events Interactive Modal Schedule
 * Enforces: Model date filtering, tenancy isolation, controller JSON formatting, and Blade trigger contracts.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/app/config/init.php';

use App\controller\members\Event;
use App\model\AllMembersData;
use Src\Db;

$pdo = Db::connect2();

echo "=================================================================\n";
echo "🗓️  UPCOMING EVENTS (3-MONTH HORIZON) INTERACTIVE SUITE\n";
echo "=================================================================\n\n";

$failures = 0;
$testFamCode = 'TEST_FAM_3MO_' . bin2hex(random_bytes(4));
$otherFamCode = 'TEST_FAM_OTHER_' . bin2hex(random_bytes(4));
$testUserId = 'user_3mo_' . bin2hex(random_bytes(4));
$otherUserId = 'user_oth_' . bin2hex(random_bytes(4));

$createdEventNos = [];

try {
    // 1. Seed test users
    $stmtUser = $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
        VALUES (?, 'Amara', 'Okafor', ?, 12, 'August', 1988)
    ");
    $stmtUser->execute([$testUserId, $testFamCode]);

    $stmtOther = $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
        VALUES (?, 'John', 'Smith', ?, 1, 'January', 1980)
    ");
    $stmtOther->execute([$otherUserId, $otherFamCode]);

    // 2. Seed test events across various date horizons
    $date10d = date('Y-m-d', strtotime('+10 days'));
    $date45d = date('Y-m-d', strtotime('+45 days'));
    $date120d = date('Y-m-d', strtotime('+120 days')); // Beyond 3 months (~90 days)
    $datePast = date('Y-m-d', strtotime('-4 days'));

    $seedStmt = $pdo->prepare("
        INSERT INTO events (id, eventCode, eventName, eventDate, eventType, eventDescription, eventFrequency, deleted_at)
        VALUES (?, ?, ?, ?, ?, ?, 'One-off', ?)
    ");

    // Event 1: In 10 days (Should be returned)
    $seedStmt->execute([$testUserId, $testFamCode, 'Family Reunion 2026', $date10d, 'Reunion', 'Annual gathering', null]);
    $createdEventNos[] = (int)$pdo->lastInsertId();

    // Event 2: In 45 days (Should be returned)
    $seedStmt->execute([$testUserId, $testFamCode, "Grandma's 80th Birthday", $date45d, 'Birthday', 'Milestone celebration', null]);
    $createdEventNos[] = (int)$pdo->lastInsertId();

    // Event 3: In 120 days (Beyond 3 months - Should NOT be returned)
    $seedStmt->execute([$testUserId, $testFamCode, 'Future Trip 2027', $date120d, 'Gathering', 'Next year holiday', null]);
    $createdEventNos[] = (int)$pdo->lastInsertId();

    // Event 4: In past (Should NOT be returned)
    $seedStmt->execute([$testUserId, $testFamCode, 'Past Anniversary', $datePast, 'Anniversary', 'Past event', null]);
    $createdEventNos[] = (int)$pdo->lastInsertId();

    // Event 5: Soft-deleted (Should NOT be returned)
    $seedStmt->execute([$testUserId, $testFamCode, 'Cancelled BBQ', $date10d, 'Gathering', 'Cancelled', date('Y-m-d H:i:s')]);
    $createdEventNos[] = (int)$pdo->lastInsertId();

    // Event 6: Other family's event (Should NOT be returned - tenancy guard)
    $seedStmt->execute([$otherUserId, $otherFamCode, 'Other Family Event', $date10d, 'Party', 'Private to them', null]);
    $createdEventNos[] = (int)$pdo->lastInsertId();

    echo "[1/4] Testing Model: AllMembersData::getUpcomingEventsForMonths...\n";
    $results = AllMembersData::getUpcomingEventsForMonths($testFamCode, 3);
    $eventNames = array_column($results, 'eventName');

    if (in_array('Family Reunion 2026', $eventNames, true) && in_array("Grandma's 80th Birthday", $eventNames, true)) {
        echo "  ✅ PASS: Both upcoming events within 3 months returned.\n";
    } else {
        echo "  ❌ FAIL: Expected events within 3 months missing from results.\n";
        $failures++;
    }

    if (!in_array('Future Trip 2027', $eventNames, true)) {
        echo "  ✅ PASS: Events beyond 3 months correctly excluded.\n";
    } else {
        echo "  ❌ FAIL: Event scheduled >3 months out was improperly returned.\n";
        $failures++;
    }

    if (!in_array('Past Anniversary', $eventNames, true)) {
        echo "  ✅ PASS: Past events correctly excluded.\n";
    } else {
        echo "  ❌ FAIL: Past event was improperly returned.\n";
        $failures++;
    }

    if (!in_array('Cancelled BBQ', $eventNames, true)) {
        echo "  ✅ PASS: Soft-deleted events correctly excluded.\n";
    } else {
        echo "  ❌ FAIL: Soft-deleted event was improperly returned.\n";
        $failures++;
    }

    if (!in_array('Other Family Event', $eventNames, true)) {
        echo "  ✅ PASS: Cross-family tenancy isolation confirmed (other family event excluded).\n";
    } else {
        echo "  ❌ FAIL: IDOR flaw: Other family's event was leaked.\n";
        $failures++;
    }

    echo "\n[2/4] Testing Controller & Endpoint: Event::getUpcoming3MonthsEvents...\n";
    $_SESSION['id'] = $testUserId;
    $_SESSION['famCode'] = $testFamCode;
    $_SESSION['famCodes'] = [$testFamCode];

    ob_start();
    Event::getUpcoming3MonthsEvents();
    $rawOutput = ob_get_clean();

    $json = json_decode((string)$rawOutput, true);
    if (is_array($json) && (($json['status'] ?? '') === 'success' || ($json['code'] ?? 0) === 200)) {
        $data = $json['message'] ?? $json['data'] ?? $json['msg'] ?? [];
        if (is_array($data) && count($data) === 2) {
            echo "  ✅ PASS: Controller returned success response with 2 enriched 3-month events.\n";
            $first = $data[0];
            if (isset($first['gCalUrl'], $first['monthKey'], $first['monthYear'], $first['creatorName'], $first['daysUntil'])) {
                echo "  ✅ PASS: Rich metadata verified (gCalUrl, monthKey, creatorName, daysUntil).\n";
            } else {
                echo "  ❌ FAIL: Enriched payload missing required calendar/month keys.\n";
                $failures++;
            }
        } else {
            echo "  ❌ FAIL: Unexpected number of events returned in JSON payload.\n";
            $failures++;
        }
    } else {
        echo "  ❌ FAIL: Controller output not valid JSON response: " . substr((string)$rawOutput, 0, 150) . "\n";
        $failures++;
    }

    echo "\n[3/4] Testing Security: Unauthenticated Access Guard...\n";
    $_SESSION['id'] = '';
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    
    ob_start();
    Event::getUpcoming3MonthsEvents();
    $unauthOutput = ob_get_clean();
    $unauthJson = json_decode((string)$unauthOutput, true);

    $isRejected = false;
    if (http_response_code() === 401) {
        $isRejected = true;
    } elseif (is_array($unauthJson) && isset($unauthJson['code']) && $unauthJson['code'] === 401) {
        $isRejected = true;
    } elseif (is_array($unauthJson) && str_contains(strtolower($unauthJson['message'] ?? ''), 'unauthorised')) {
        $isRejected = true;
    }

    if ($isRejected) {
        echo "  ✅ PASS: Unauthenticated access rejected with HTTP 401.\n";
    } else {
        echo "  ❌ FAIL: Unauthenticated access was not rejected with HTTP 401. Output: " . substr((string)$unauthOutput, 0, 150) . "\n";
        $failures++;
    }

    echo "\n[4/4] Verifying Blade View Templates & Triggers...\n";
    $modalViewPath = __DIR__ . '/../resources/views/member/modals/upcomingEventsModal.blade.php';
    $profileViewPath = __DIR__ . '/../resources/views/member/profilePage.blade.php';
    $rightColPath = __DIR__ . '/../resources/views/member/includes/rightColumn.blade.php';
    $mobileTrayPath = __DIR__ . '/../resources/views/member/includes/mobileHighlightsTray.blade.php';

    if (file_exists($modalViewPath)) {
        echo "  ✅ PASS: upcomingEventsModal.blade.php exists.\n";
    } else {
        echo "  ❌ FAIL: upcomingEventsModal.blade.php missing.\n";
        $failures++;
    }

    $profileContent = file_get_contents($profileViewPath);
    if (str_contains($profileContent, "includeIf('member.modals.upcomingEventsModal')")) {
        echo "  ✅ PASS: profilePage.blade.php includes upcomingEventsModal.\n";
    } else {
        echo "  ❌ FAIL: profilePage.blade.php does not include upcomingEventsModal.\n";
        $failures++;
    }

    $rightColContent = file_get_contents($rightColPath);
    if (str_contains($rightColContent, 'data-bs-target="#allUpcomingEventsModal"') && str_contains($rightColContent, 'min-height: 44px')) {
        echo "  ✅ PASS: rightColumn.blade.php 'See All' button activates modal with min 44px touch target.\n";
    } else {
        echo "  ❌ FAIL: rightColumn.blade.php 'See All' button missing modal trigger or 44px touch target.\n";
        $failures++;
    }

    $mobileTrayContent = file_get_contents($mobileTrayPath);
    if (str_contains($mobileTrayContent, 'data-bs-target="#allUpcomingEventsModal"')) {
        echo "  ✅ PASS: mobileHighlightsTray.blade.php 'See All' activates modal.\n";
    } else {
        echo "  ❌ FAIL: mobileHighlightsTray.blade.php 'See All' missing modal trigger.\n";
        $failures++;
    }

} catch (\Throwable $e) {
    echo "❌ FATAL EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $failures++;
} finally {
    // Clean up test data
    if (!empty($createdEventNos)) {
        $in = implode(',', array_fill(0, count($createdEventNos), '?'));
        $delStmt = $pdo->prepare("DELETE FROM events WHERE no IN ($in)");
        $delStmt->execute($createdEventNos);
    }
    $delUsers = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?)");
    $delUsers->execute([$testUserId, $otherUserId]);
}

echo "\n=================================================================\n";
if ($failures === 0) {
    echo "🎉 ALL TESTS PASSED: Upcoming Events 3-Month Modal Schedule Certified!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "🛑 TEST SUITE FAILED with {$failures} failure(s).\n";
    echo "=================================================================\n";
    exit(1);
}
