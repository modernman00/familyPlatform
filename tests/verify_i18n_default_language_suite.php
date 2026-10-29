<?php

declare(strict_types=1);

/**
 * 🧪 Sovereign Verification Suite: I18n Default Language & New User Protection
 *
 * Verifies:
 * 1. Default language is strictly British English ('en') across all unauthenticated & new user visits.
 * 2. Lingering/stale 'app_locale' cookies from previous testers/sessions do NOT bleed into new user sessions.
 * 3. Explicit locale control via setLocale() or query parameters (?locale=) is respected.
 * 4. resetToDefault() cleanly restores British English ('en') and removes session/cookie overrides.
 * 5. Global translation helper functions (__ and trans) cascade correctly to English defaults.
 */

require_once __DIR__ . '/../app/config/init.php';

use App\classes\I18n;

echo "=================================================================\n";
echo " 🌐 I18N DEFAULT LANGUAGE & LOCALE ISOLATION TEST SUITE           \n";
echo "=================================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertCondition(bool $condition, string $description): void
{
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        $testsPassed++;
        echo "  ✅ PASS: {$description}\n";
    } else {
        echo "  ❌ FAIL: {$description}\n";
        throw new RuntimeException("Assertion failed: {$description}");
    }
}

// Ensure session is started for tests
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// -----------------------------------------------------------------------------
// Test 1: Fresh State Defaults to British English ('en')
// -----------------------------------------------------------------------------
echo "[TEST 1] Verifying fresh visitor defaults to British English ('en')...\n";
I18n::reset();
unset($_SESSION['app_locale']);
unset($_SESSION[I18n::EXPLICIT_LOCALE_SESSION_KEY]);
unset($_COOKIE['app_locale']);
$_GET = [];

assertCondition(I18n::getLocale() === 'en', "Fresh visitor locale is strictly 'en'");
assertCondition(I18n::trans('nav.home') === 'Home', "Default nav.home translates to English 'Home'");
assertCondition(I18n::trans('nav.tree') === 'Family Tree', "Default nav.tree translates to English 'Family Tree'");

// -----------------------------------------------------------------------------
// Test 2: Lingering Cookie Protection for New Users (The Core Bug Remediated)
// -----------------------------------------------------------------------------
echo "\n[TEST 2] Verifying lingering stale cookie does NOT force Spanish on new users...\n";
I18n::reset();
unset($_SESSION['app_locale']);
unset($_SESSION[I18n::EXPLICIT_LOCALE_SESSION_KEY]);
// Simulate previous tester leaving a stale 1-year cookie in browser
$_COOKIE['app_locale'] = 'es';
$_GET = [];

assertCondition(
    I18n::getLocale() === 'en',
    "New user on shared browser with stale 'es' cookie still receives English 'en' default"
);
assertCondition(
    I18n::trans('nav.home') === 'Home',
    "New user is served English dictionary, not Spanish"
);

// -----------------------------------------------------------------------------
// Test 3: Explicit User Choice via setLocale() is Respected
// -----------------------------------------------------------------------------
echo "\n[TEST 3] Verifying explicit user language switch via setLocale()...\n";
I18n::reset();
$switched = I18n::setLocale('fr');

assertCondition($switched === true, "setLocale('fr') returned true");
assertCondition(I18n::getLocale() === 'fr', "Active locale switched to 'fr'");
assertCondition(I18n::trans('nav.home') === 'Accueil', "nav.home translates to French 'Accueil'");
assertCondition($_SESSION[I18n::EXPLICIT_LOCALE_SESSION_KEY] === true, "Explicit choice flag recorded in session");

// Switch to Spanish
$switchedEs = I18n::setLocale('es');
assertCondition($switchedEs === true, "setLocale('es') returned true");
assertCondition(I18n::getLocale() === 'es', "Active locale switched to 'es'");
assertCondition(I18n::trans('nav.home') === 'Inicio', "nav.home translates to Spanish 'Inicio'");

// -----------------------------------------------------------------------------
// Test 4: Query Parameter Control (?locale=)
// -----------------------------------------------------------------------------
echo "\n[TEST 4] Verifying explicit query parameter override (?locale=fr)...\n";
I18n::reset();
unset($_SESSION['app_locale']);
unset($_SESSION[I18n::EXPLICIT_LOCALE_SESSION_KEY]);
unset($_COOKIE['app_locale']);
$_GET['locale'] = 'fr';

assertCondition(I18n::getLocale() === 'fr', "Query param ?locale=fr sets active locale to 'fr'");
assertCondition(I18n::trans('nav.tree') === 'Arbre Généalogique', "nav.tree translates to French 'Arbre Généalogique'");

// Malicious query parameter rejection
$_GET['locale'] = '../../../etc/passwd';
I18n::resetToDefault();
assertCondition(I18n::getLocale() === 'en', "Malicious directory traversal locale rejected and falls back to 'en'");
$_GET = [];

// -----------------------------------------------------------------------------
// Test 5: resetToDefault() Evicts Overrides Cleanly (Logout Protection)
// -----------------------------------------------------------------------------
echo "\n[TEST 5] Verifying resetToDefault() clears overrides on logout...\n";
I18n::setLocale('es');
assertCondition(I18n::getLocale() === 'es', "Pre-condition: locale is Spanish before logout");

I18n::resetToDefault();
assertCondition(I18n::getLocale() === 'en', "After resetToDefault(), locale immediately reverts to 'en'");
assertCondition(empty($_SESSION['app_locale']), "Session app_locale was purged");
assertCondition(empty($_SESSION[I18n::EXPLICIT_LOCALE_SESSION_KEY]), "Explicit choice session flag was purged");

// -----------------------------------------------------------------------------
// Test 6: Global Helper Parity (__ and trans)
// -----------------------------------------------------------------------------
echo "\n[TEST 6] Verifying global __ and trans helper functions...\n";
I18n::resetToDefault();
assertCondition(__('nav.home') === 'Home', "Global __('nav.home') returns 'Home'");
assertCondition(trans('nav.home') === 'Home', "Global trans('nav.home') returns 'Home'");
assertCondition(__('nonexistent.key', 'Fallback Text') === 'Fallback Text', "Fallback string works with __()");

echo "\n=================================================================\n";
echo " 🎉 ALL {$totalTests} I18N DEFAULT LANGUAGE TESTS PASSED!          \n";
echo "=================================================================\n";
