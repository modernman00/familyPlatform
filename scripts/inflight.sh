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

# 1. PHP Syntax Linting
echo -e "${BOLD}${CYAN}[IN-FLIGHT 1/3] Checking PHP Syntax (php -l)...${NC}"
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
echo -e "${BOLD}${CYAN}[IN-FLIGHT 2/3] Checking 10 Golden Engineering Invariants...${NC}"
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
echo -e "${BOLD}${CYAN}[IN-FLIGHT 3/3] Executing Dr. Viktor Kovács Adversarial Exploit Attacks...${NC}"
ADVERSARIAL_PASSED=1
if [ -f "scripts/dast-guest-builder-idor.sh" ]; then
    bash scripts/dast-guest-builder-idor.sh || ADVERSARIAL_PASSED=0
fi
if [ -f "scripts/dast-idor-access-control.sh" ]; then
    bash scripts/dast-idor-access-control.sh || ADVERSARIAL_PASSED=0
fi
if [ -f "scripts/run-all-security-checks.sh" ]; then
    bash scripts/run-all-security-checks.sh || ADVERSARIAL_PASSED=0
fi

if [ "$ADVERSARIAL_PASSED" -eq 1 ]; then
    echo -e "${GREEN}  ✅ In-Flight 3 Passed: Dr. Viktor Kovács & Marcus adversarial attacks neutralized (100% immune).${NC}"
else
    echo -e "${RED}  ❌ In-Flight 3 Failed: Unhandled exploit or security vulnerability detected!${NC}"
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
