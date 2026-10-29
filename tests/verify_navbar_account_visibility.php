<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

echo "========================================================\n";
echo "🧪 VERIFY NAVBAR CLEANUP & ACCOUNT VISIBILITY INVARIANT\n";
echo "========================================================\n";

$navbarFile = __DIR__ . '/../resources/views/layouts/navbar.blade.php';
$cssFile = __DIR__ . '/../public/css/navbar.css';
$standardsCssFile = __DIR__ . '/../public/css/standards-v21.css';

$navbarContent = file_get_contents($navbarFile);
$cssContent = file_get_contents($cssFile);
$standardsCssContent = file_get_contents($standardsCssFile);

// Check 1: Journal must NOT be in the navbar
if (strpos($navbarContent, 'familyJournalNav') !== false || strpos($navbarContent, 'href="/familyJournal"') !== false) {
    echo "❌ FAIL: Journal link is still present in the navbar!\n";
    exit(1);
}
echo "✅ PASS: Journal successfully removed from the navbar.\n";

// Check 2: Core links remain in navbar
$expectedLinks = ['/profilePage', 'mode=canvas', '/familyStudio', '/reels', '/allMembers'];
foreach ($expectedLinks as $link) {
    if (strpos($navbarContent, $link) === false) {
        echo "❌ FAIL: Missing required nav link: {$link}\n";
        exit(1);
    }
}
echo "✅ PASS: Core nav links (My Page, Tree, Studio, Reels, Family) present.\n";

// Check 3: User Menu account capsule structure
if (strpos($navbarContent, 'user-name-label') === false) {
    echo "❌ FAIL: User Menu button missing user-name-label class for text truncation.\n";
    exit(1);
}
echo "✅ PASS: User Menu account capsule includes user-name-label.\n";

// Check 4: CSS prevents clipping on button itself and delegates to user-name-label
if (strpos($standardsCssContent, '#mainNavbar .btn-user-stitch') === false || strpos($standardsCssContent, 'overflow: visible !important;') === false) {
    echo "❌ FAIL: standards-v21.css does not enforce overflow: visible on .btn-user-stitch.\n";
    exit(1);
}
echo "✅ PASS: standards-v21.css guarantees overflow: visible on #mainNavbar .btn-user-stitch.\n";

// Check 5: Dropdown toggle does not have destructive overflow: hidden clipping on button
if (strpos($standardsCssContent, '#mainNavbar .dropdown-toggle { max-width: 180px; overflow: hidden;') !== false) {
    echo "❌ FAIL: Legacy clipping rule '#mainNavbar .dropdown-toggle { max-width: 180px; overflow: hidden;' still exists.\n";
    exit(1);
}
echo "✅ PASS: Legacy button-level overflow: hidden clipping removed.\n";

// Check 6: Responsive desktop space optimization
if (strpos($navbarContent, 'navbar-actions-group flex-shrink-0') === false) {
    echo "❌ FAIL: navbar-actions-group is missing flex-shrink-0 protection.\n";
    exit(1);
}
echo "✅ PASS: navbar-actions-group has flex-shrink-0 protection against horizontal squashing.\n";

echo "\n🎉 ALL 6 NAVBAR & ACCOUNT VISIBILITY INVARIANTS VERIFIED!\n";
exit(0);
