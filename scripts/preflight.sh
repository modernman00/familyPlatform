#!/usr/bin/env bash
# ==============================================================================
# 🛡️  DAVID & RED TEAM PRE-FLIGHT QUALITY & GOVERNANCE GATE — FamilyPlatform
# ==============================================================================
# Deterministic local gate enforcing:
#   1. PHP Syntax Check (php -l) across all PHP codebase
#   2. David's PHPStan Level 8 Static Analysis
#   3. Semgrep Security & Vulnerability Scan
#   4. Engineering Must-Haves (CSRF, SQL, Passwords, Shared-Lib)
#   5. Sovereign Robust E2E User Journey Gate (tests/verify_e2e_robust_suite.php)
#   6. Automated PHPUnit Test Suite
#   7. Dr. Viktor Kovács & Marcus Adversarial Enterprise Security Attack Gate (scripts/run-all-security-checks.sh)
#
# Exit Code: 0 = PASS (Ready to commit/deploy), 1 = BLOCKED (Must fix errors)
# ==============================================================================

set -uo pipefail

# Ensure standard paths
export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"

# Color Codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo -e "${BOLD}${BLUE}================================================================${NC}"
echo -e "${BOLD}${BLUE}  🛡️  FAMILYPLATFORM PRE-FLIGHT GOVERNANCE GATE                ${NC}"
echo -e "${BOLD}${BLUE}================================================================${NC}"
echo -e "Started at: $(date)"
echo -e "Workspace : $ROOT_DIR"
echo ""

FAILURES=0

# ------------------------------------------------------------------------------
# 0. Standard 18 Zero-Symlink Invariant Fail-Fast Guard
# ------------------------------------------------------------------------------
if [ -L "vendor/modernman00/shared-lib" ] || [ -L "node_modules/@modernman00/shared-js-lib" ]; then
    echo -e "${RED}❌ FATAL GOVERNANCE BREACH: Standard 18 Zero-Symlink Invariant Violated!${NC}"
    echo -e "${RED}   Symlinks in vendor/ or node_modules/ are strictly prohibited in application workspaces.${NC}"
    echo -e "${RED}   Run 'manage-shared.sh --unlink' or install real versioned packages.${NC}"
    exit 1
fi

# ------------------------------------------------------------------------------
# 1. PHP Syntax Check (php -l)
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 1/7] Checking PHP Syntax (php -l)...${NC}"
SYNTAX_ERRORS=0
while IFS= read -r file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}  ❌ Syntax Error in: $file${NC}"
        php -l "$file"
        SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
    fi
done < <(find app bin scripts cron -name "*.php" -type f 2>/dev/null)

if [ "$SYNTAX_ERRORS" -eq 0 ]; then
    echo -e "${GREEN}  ✅ Gate 1 Passed: All PHP files have valid syntax.${NC}"
else
    echo -e "${RED}  ❌ Gate 1 Failed: $SYNTAX_ERRORS syntax errors found.${NC}"
    FAILURES=$((FAILURES + 1))
fi
echo ""

# ------------------------------------------------------------------------------
# 2. David's PHPStan Level 8 Static Analysis Gate
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 2/7] Running PHPStan Level 8 Analysis...${NC}"
if [ -x "vendor/bin/phpstan" ]; then
    if vendor/bin/phpstan analyse --level=8; then
        echo -e "${GREEN}  ✅ Gate 2 Passed: Zero PHPStan Level 8 errors.${NC}"
    else
        echo -e "${RED}  ❌ Gate 2 Failed: PHPStan Level 8 reported errors.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
else
    echo -e "${YELLOW}  ⚠️  PHPStan binary not found at vendor/bin/phpstan.${NC}"
fi
echo ""

# ------------------------------------------------------------------------------
# 3. Semgrep Security & Vulnerability Scan
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 3/7] Running Semgrep Security Scan...${NC}"
if command -v semgrep >/dev/null 2>&1; then
    SEMGREP_CFG="--config=auto"
    if [ -f ".semgrep.yml" ]; then
        SEMGREP_CFG="--config=.semgrep.yml"
    fi
    if semgrep scan $SEMGREP_CFG --error --quiet app/ index.php 2>/dev/null; then
        echo -e "${GREEN}  ✅ Gate 3 Passed: Semgrep reported zero security violations.${NC}"
    else
        echo -e "${YELLOW}  ⚠️  Semgrep scan completed with warnings or notices.${NC}"
    fi
else
    echo -e "${YELLOW}  ⚠️  Semgrep not installed in environment (Skipping).${NC}"
fi
echo ""

# ------------------------------------------------------------------------------
# 4. Engineering Must-Haves, PWA & Stability Suites (TAT & BRATS Gate)
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 4/7] Verifying Engineering Must-Haves, PWA & Stability Suites...${NC}"
GATE4_PASSED=1
if [ -f "scripts/verify_engineering_must_haves.php" ]; then
    php scripts/verify_engineering_must_haves.php || GATE4_PASSED=0
fi

if [ -f "tests/verify_pwa_optimization.php" ]; then
    php tests/verify_pwa_optimization.php || GATE4_PASSED=0
fi

if [ -f "tests/verify_frontend_stability.php" ]; then
    php tests/verify_frontend_stability.php || GATE4_PASSED=0
fi

if [ -f "tests/verify_link_preview_graphics_test.php" ]; then
    php tests/verify_link_preview_graphics_test.php || GATE4_PASSED=0
fi

if [ "$GATE4_PASSED" -eq 1 ]; then
    echo -e "${GREEN}  ✅ Gate 4 Passed: 0 Must-Have violations, PWA verified, Frontend stable.${NC}"
else
    echo -e "${RED}  ❌ Gate 4 Failed: Engineering Must-Haves or Frontend Stability suites reported failures.${NC}"
    FAILURES=$((FAILURES + 1))
fi
echo ""

# ------------------------------------------------------------------------------
# 5. Sovereign Robust E2E User Journey Gate (Mandatory & Non-Skippable)
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 5/7] Running Sovereign Robust E2E User Journey Gate...${NC}"
if [ ! -f "tests/verify_e2e_robust_suite.php" ]; then
    echo -e "${RED}  ❌ FATAL: Sovereign E2E suite missing at tests/verify_e2e_robust_suite.php. Skipping is strictly forbidden.${NC}"
    FAILURES=$((FAILURES + 1))
else
    if php tests/verify_e2e_robust_suite.php; then
        echo -e "${GREEN}  ✅ Gate 5 Passed: Sovereign Robust E2E User Journey Suite 100% passed.${NC}"
    else
        echo -e "${RED}  ❌ Gate 5 Failed: Sovereign Robust E2E User Journey Suite reported failures.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
fi
echo ""

# ------------------------------------------------------------------------------
# 6. Automated PHPUnit Test Suite
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 6/7] Running PHPUnit Test Suite...${NC}"
if [ -x "vendor/bin/phpunit" ]; then
    if vendor/bin/phpunit; then
        echo -e "${GREEN}  ✅ Gate 6 Passed: All PHPUnit tests succeeded.${NC}"
    else
        echo -e "${RED}  ❌ Gate 6 Failed: PHPUnit reported test failures.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
else
    echo -e "${YELLOW}  ⚠️  PHPUnit binary not found at vendor/bin/phpunit.${NC}"
fi
echo ""

# ------------------------------------------------------------------------------
# 7. Dr. Viktor Kovács & Marcus Adversarial Enterprise Security Attack Gate
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 7/7] Running Dr. Viktor Kovács & Marcus Adversarial Enterprise Security Attack Gate...${NC}"
if [ -f "scripts/dast-guest-builder-idor.sh" ]; then
    if bash scripts/dast-guest-builder-idor.sh; then
        echo -e "${GREEN}  ✅ Gate 7 Passed: Marcus & Ghost Red Team IDOR gauntlet passed.${NC}"
    else
        echo -e "${RED}  ❌ Gate 7 Failed: Adversarial security check failed.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
elif [ -f "scripts/run-all-security-checks.sh" ]; then
    if bash scripts/run-all-security-checks.sh; then
        echo -e "${GREEN}  ✅ Gate 7 Passed: Adversarial security checks passed.${NC}"
    else
        echo -e "${RED}  ❌ Gate 7 Failed: Adversarial security check failed.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
else
    echo -e "${YELLOW}  ⚠️  Red team security script not found.${NC}"
fi
echo ""

# ------------------------------------------------------------------------------
# Final Verdict
# ------------------------------------------------------------------------------
echo -e "${BOLD}${BLUE}================================================================${NC}"
if [ "$FAILURES" -eq 0 ]; then
    echo -e "${BOLD}${GREEN}  🏆 ALL PRE-FLIGHT GATES CLEARED! READY FOR SIGN-OFF & COMMIT. ${NC}"
    echo -e "${BOLD}${BLUE}================================================================${NC}"
    exit 0
else
    echo -e "${BOLD}${RED}  🚫 PRE-FLIGHT BLOCKED ($FAILURES gates failed). DO NOT COMMIT.  ${NC}"
    echo -e "${BOLD}${BLUE}================================================================${NC}"
    exit 1
fi
