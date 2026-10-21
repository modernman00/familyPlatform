#!/usr/bin/env bash
# ==============================================================================
# 🛡️  IN-FLIGHT ADVERSARIAL ENTERPRISE SECURITY & INVARIANT CHECKER — familyPlatform
# ==============================================================================
# Commissioned by: Wally (CEO) | Enforced by: Jumoke Olaogun (Special Assistant)
# Mandatory Gatekeeper: Dr. Viktor Kovács & Marcus (Adversarial Red Team Lead)
#
# Runs deterministic in-flight checks for Tier 2 and Tier 3 implementations:
#   1. PHP Syntax Check (php -l)
#   2. 10 Golden Engineering Must-Haves Scanner
#   3. Dr. Viktor Kovács & Marcus Adversarial Enterprise Security Attack Suite
#   4. Database Migration Engine & Ledger Health Check
#
# Exit Code: 0 = PASS, 1 = BLOCKED
# ==============================================================================

set -uo pipefail

export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"

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
echo -e "${BOLD}${BLUE}  🛡️  IN-FLIGHT ADVERSARIAL ENTERPRISE SECURITY GATE            ${NC}"
echo -e "${BOLD}${BLUE}  Lead: Dr. Viktor Kovács & Marcus (SecOps Red Team)            ${NC}"
echo -e "${BOLD}${BLUE}  App : familyPlatform                                                  ${NC}"
echo -e "${BOLD}${BLUE}================================================================${NC}"
echo -e "Started at: $(date)"
echo -e "Workspace : $ROOT_DIR"
echo ""

FAILURES=0

# 0. Standard 18 Zero-Symlink Invariant Fail-Fast Guard
if [ -L "vendor/modernman00/shared-lib" ] || [ -L "node_modules/@modernman00/shared-js-lib" ]; then
    echo -e "${RED}❌ FATAL GOVERNANCE BREACH: Standard 18 Zero-Symlink Invariant Violated!${NC}"
    echo -e "${RED}   Symlinks in vendor/ or node_modules/ are strictly prohibited in application workspaces.${NC}"
    echo -e "${RED}   Run 'manage-shared.sh --unlink' or install real versioned packages.${NC}"
    exit 1
fi

# 1. PHP Syntax Linting
echo -e "${BOLD}${CYAN}[IN-FLIGHT 1/4] Checking PHP Syntax (php -l)...${NC}"
SYNTAX_ERRORS=0
while IFS= read -r file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}  ❌ Syntax Error in: $file${NC}"
        php -l "$file"
        SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
    fi
done < <(find app bootstrap bin scripts -name "*.php" -type f 2>/dev/null)

if [ "$SYNTAX_ERRORS" -eq 0 ]; then
    echo -e "${GREEN}  ✅ In-Flight 1 Passed: All PHP files have valid syntax.${NC}"
else
    echo -e "${RED}  ❌ In-Flight 1 Failed: $SYNTAX_ERRORS syntax errors found.${NC}"
    FAILURES=$((FAILURES + 1))
fi
echo ""

# 2. Engineering Must-Haves
echo -e "${BOLD}${CYAN}[IN-FLIGHT 2/4] Checking 10 Golden Engineering Invariants...${NC}"
if [ -f "scripts/verify_engineering_must_haves.php" ]; then
    if php scripts/verify_engineering_must_haves.php; then
        echo -e "${GREEN}  ✅ In-Flight 2 Passed: All Engineering Must-Haves verified.${NC}"
    else
        echo -e "${RED}  ❌ In-Flight 2 Failed: Engineering Invariant violations detected.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
fi
echo ""

# 3. Dr. Viktor Kovács & Marcus Adversarial Enterprise Security Attack Suite
echo -e "${BOLD}${CYAN}[IN-FLIGHT 3/4] Executing Dr. Viktor Kovács Adversarial Exploit Attacks...${NC}"
ADVERSARIAL_PASSED=1
if [ -f "scripts/dast-php2shell-cve-2026-109618.sh" ]; then
    bash scripts/dast-php2shell-cve-2026-109618.sh || ADVERSARIAL_PASSED=0
fi
if [ -f "scripts/dast-guest-builder-idor.sh" ]; then
    bash scripts/dast-guest-builder-idor.sh || ADVERSARIAL_PASSED=0
fi
if [ -f "scripts/dast-idor-access-control.sh" ]; then
    bash scripts/dast-idor-access-control.sh || ADVERSARIAL_PASSED=0
fi
if [ -f "scripts/run-all-security-checks.sh" ]; then
    LOCAL_TEST_URL="https://olaogun.test"
    if ! curl -k -s -m 2 "$LOCAL_TEST_URL" > /dev/null 2>&1; then
        LOCAL_TEST_URL="http://localhost:8000"
    fi
    bash scripts/run-all-security-checks.sh "$LOCAL_TEST_URL" --skip-zap || ADVERSARIAL_PASSED=0
fi

if [ "$ADVERSARIAL_PASSED" -eq 1 ]; then
    echo -e "${GREEN}  ✅ In-Flight 3 Passed: Dr. Viktor Kovács & Marcus adversarial attacks neutralized (100% immune).${NC}"
else
    echo -e "${RED}  ❌ In-Flight 3 Failed: Unhandled exploit or security vulnerability detected!${NC}"
    FAILURES=$((FAILURES + 1))
fi
echo ""

# 4. Database Migration Engine & Ledger Health
echo -e "${BOLD}${CYAN}[IN-FLIGHT 4/4] Checking Database Migration Engine & Ledger Health...${NC}"
if [ -f "scripts/run_migrations.php" ]; then
    if php scripts/run_migrations.php --status > /tmp/_migration_status.txt 2>&1; then
        cat /tmp/_migration_status.txt
        echo -e "${GREEN}  ✅ In-Flight 4 Passed: Database migration engine and ledger are healthy.${NC}"
        rm -f /tmp/_migration_status.txt
    else
        cat /tmp/_migration_status.txt
        rm -f /tmp/_migration_status.txt
        echo -e "${RED}  ❌ In-Flight 4 Failed: Database migration engine reported errors!${NC}"
        FAILURES=$((FAILURES + 1))
    fi
else
    echo -e "${RED}  ❌ In-Flight 4 Failed: scripts/run_migrations.php not found!${NC}"
    FAILURES=$((FAILURES + 1))
fi
echo ""

# Final In-Flight Verdict
echo -e "${BOLD}${BLUE}================================================================${NC}"
if [ "$FAILURES" -eq 0 ]; then
    echo -e "${BOLD}${GREEN}  🏆 IN-FLIGHT SECURITY CHECK CLEARED! READY TO ADVANCE.       ${NC}"
    echo -e "${BOLD}${BLUE}================================================================${NC}"
    exit 0
else
    echo -e "${BOLD}${RED}  🛑 IN-FLIGHT SECURITY CHECK FAILED ($FAILURES issues). BLOCKED.    ${NC}"
    echo -e "${BOLD}${BLUE}================================================================${NC}"
    exit 1
fi
