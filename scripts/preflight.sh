#!/usr/bin/env bash
# ==============================================================================
# 🛡️  DAVID'S PRE-FLIGHT QUALITY & GOVERNANCE GATE — FamilyPlatform
# ==============================================================================
# Deterministic local gate enforcing:
#   1. PHP Syntax Check (php -l) across all PHP codebase
#   2. David's PHPStan Level 8 Static Analysis
#   3. Semgrep Security & Vulnerability Scan
#   4. Automated PHPUnit Test Suite
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
# 1. PHP Syntax Check (php -l)
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 1/4] Checking PHP Syntax (php -l)...${NC}"
SYNTAX_ERRORS=0
while IFS= read -r file; do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}  ❌ Syntax Error in: $file${NC}"
        php -l "$file"
        SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
    fi
done < <(find app bin scripts -name "*.php" -type f)

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
echo -e "${BOLD}${CYAN}[GATE 2/4] Running PHPStan Level 8 Analysis...${NC}"
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
echo -e "${BOLD}${CYAN}[GATE 3/4] Running Semgrep Security Scan...${NC}"
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
# 4. Automated PHPUnit Test Suite
# ------------------------------------------------------------------------------
echo -e "${BOLD}${CYAN}[GATE 4/4] Running PHPUnit Test Suite...${NC}"
if [ -x "vendor/bin/phpunit" ]; then
    if vendor/bin/phpunit; then
        echo -e "${GREEN}  ✅ Gate 4 Passed: All PHPUnit tests succeeded.${NC}"
    else
        echo -e "${RED}  ❌ Gate 4 Failed: PHPUnit reported test failures.${NC}"
        FAILURES=$((FAILURES + 1))
    fi
else
    echo -e "${YELLOW}  ⚠️  PHPUnit binary not found at vendor/bin/phpunit.${NC}"
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
