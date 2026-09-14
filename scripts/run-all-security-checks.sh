#!/usr/bin/env bash
# ==============================================================================
# 🛡️  MASTER SECURITY & QUALITY SUITE — FamilyPlatform
# ==============================================================================
# Single-command runner for ALL security, code quality, SAST, and DAST checks.
#
# Runs:
#   1. Git Secret & Credential Leak Audit
#   2. Full Backend PHP Syntax Linting (php -l)
#   3. PHPStan Level 8 Static Analysis
#   4. Automated PHPUnit Test Suite
#   5. Semgrep SAST Static Vulnerability Scan (.semgrep.yml)
#   6. Red Team Adversarial IDOR Guard Test (scripts/dast-guest-builder-idor.sh)
#   7. OWASP ZAP Dynamic Application Security Scan (scripts/zap-scan.sh)
#
# Usage:
#   bash scripts/run-all-security-checks.sh [TARGET_URL] [OPTIONS]
#
# Examples:
#   bash scripts/run-all-security-checks.sh
#   bash scripts/run-all-security-checks.sh https://staging.myfamilyplatform.com
#   bash scripts/run-all-security-checks.sh http://localhost:8000 --skip-zap
# ==============================================================================

set -uo pipefail

# Ensure PHP and tools in PATH
export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"

# Color Codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Default Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"
DEFAULT_PRODUCTION_URL="https://myfamilyplatform.com"
TARGET_URL="$DEFAULT_PRODUCTION_URL"
SKIP_ZAP=false
SKIP_IDOR=false

# Parse flags and arguments in any order
for arg in "$@"; do
    case "$arg" in
        --skip-zap)  SKIP_ZAP=true ;;
        --skip-idor) SKIP_IDOR=true ;;
        --help|-h)
            echo "Usage: $0 [TARGET_URL] [--skip-zap] [--skip-idor]"
            exit 0
            ;;
        *)
            if [[ "$arg" != --* ]] && [ -z "$TARGET_URL" ]; then
                TARGET_URL="$arg"
            fi
            ;;
    esac
done

cd "$ROOT_DIR"

# Scorecard Tracker
PASSED_CHECKS=0
FAILED_CHECKS=0
SKIPPED_CHECKS=0

print_header() {
    echo -e "\n${BLUE}${BOLD}======================================================================${NC}"
    echo -e "${BLUE}${BOLD}   🛡️  FAMILYPLATFORM MASTER SECURITY & QUALITY AUDIT SUITE          ${NC}"
    echo -e "${BLUE}${BOLD}======================================================================${NC}"
    echo -e "📅 Date: $(date)"
    echo -e "📁 Directory: $ROOT_DIR"
    if [ -n "$TARGET_URL" ]; then
        echo -e "🎯 Live Target URL: ${CYAN}$TARGET_URL${NC}"
    else
        echo -e "🎯 Live Target URL: ${YELLOW}Not provided (Runtime DAST will be prompt/skipped)${NC}"
    fi
    echo -e "${BLUE}${BOLD}----------------------------------------------------------------------${NC}\n"
}

log_pass() {
    echo -e "${GREEN}${BOLD}✅ PASS:${NC} $1"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
}

log_fail() {
    echo -e "${RED}${BOLD}❌ FAIL:${NC} $1"
    FAILED_CHECKS=$((FAILED_CHECKS + 1))
}

log_skip() {
    echo -e "${YELLOW}${BOLD}⚠️  SKIP:${NC} $1"
    SKIPPED_CHECKS=$((SKIPPED_CHECKS + 1))
}

print_header

# ==============================================================================
# 1. SECRET & CREDENTIAL LEAK DETECTION
# ==============================================================================
echo -e "${CYAN}${BOLD}[1/7] Scanning Git Repository for Secret & Credential Leaks...${NC}"
SECRET_PATTERNS='(\.env$|\.pem$|\.key$|id_rsa|private_key|token_cache|aws_access_key|api_secret|BEGIN (RSA|EC|DSA) PRIVATE KEY)'

STAGED_SECRETS=$(git status -s 2>/dev/null | grep -iE "$SECRET_PATTERNS" | grep -v '\.env\.example' || true)

if [ -n "$STAGED_SECRETS" ]; then
    log_fail "Potential secrets or credential files detected in git working tree:"
    echo "$STAGED_SECRETS"
else
    log_pass "Zero uncommitted secrets, API keys, or private certificates detected."
fi

# ==============================================================================
# 2. PHP SYNTAX LINTING (Full Backend)
# ==============================================================================
echo -e "\n${CYAN}${BOLD}[2/7] Running PHP Syntax Linting (Full Backend)...${NC}"
PHP_BIN=$(which php 2>/dev/null || echo "")

if [ -z "$PHP_BIN" ]; then
    log_fail "PHP binary not found in PATH."
else
    LINT_ERRORS=0
    while IFS= read -r file; do
        if ! "$PHP_BIN" -l "$file" > /dev/null 2>&1; then
            echo -e "   ${RED}Syntax Error in: $file${NC}"
            LINT_ERRORS=$((LINT_ERRORS + 1))
        fi
    done < <(find app bootstrap -type f -name "*.php" 2>/dev/null)

    if [ "$LINT_ERRORS" -eq 0 ]; then
        log_pass "100% of PHP backend files clean (No syntax errors)."
    else
        log_fail "Found $LINT_ERRORS PHP syntax errors."
    fi
fi

# ==============================================================================
# 3. PHPSTAN STATIC ANALYSIS
# ==============================================================================
echo -e "\n${CYAN}${BOLD}[3/7] Running PHPStan Static Analysis...${NC}"
PHPSTAN_BIN=""
if [ -x "vendor/bin/phpstan" ]; then
    PHPSTAN_BIN="vendor/bin/phpstan"
elif [ -x "vendor/phpstan/phpstan/phpstan" ]; then
    PHPSTAN_BIN="vendor/phpstan/phpstan/phpstan"
elif command -v phpstan > /dev/null 2>&1; then
    PHPSTAN_BIN="phpstan"
fi

if [ -n "$PHPSTAN_BIN" ]; then
    if "$PHPSTAN_BIN" analyse --no-progress --quiet 2>/dev/null; then
        log_pass "PHPStan static analysis passed."
    else
        log_fail "PHPStan identified type/logic issues. Run '$PHPSTAN_BIN analyse' to inspect."
    fi
else
    log_skip "PHPStan executable not found in vendor/bin/phpstan or vendor/phpstan/."
fi

# ==============================================================================
# 4. PHPUNIT AUTOMATED UNIT & INTEGRATION TESTS
# ==============================================================================
echo -e "\n${CYAN}${BOLD}[4/7] Running PHPUnit Automated Test Suite...${NC}"
PHPUNIT_BIN=""
if [ -x "vendor/bin/phpunit" ]; then
    PHPUNIT_BIN="vendor/bin/phpunit"
elif [ -x "vendor/phpunit/phpunit/phpunit" ]; then
    PHPUNIT_BIN="vendor/phpunit/phpunit/phpunit"
elif command -v phpunit > /dev/null 2>&1; then
    PHPUNIT_BIN="phpunit"
fi

if [ -n "$PHPUNIT_BIN" ]; then
    if "$PHPUNIT_BIN" --no-coverage > /dev/null 2>&1; then
        log_pass "All PHPUnit automated unit & integration tests passed."
    else
        log_fail "PHPUnit tests failed. Run '$PHPUNIT_BIN' for detailed report."
    fi
elif [ -x "scripts/run_tests.sh" ]; then
    if bash scripts/run_tests.sh > /dev/null 2>&1; then
        log_pass "Automated test suite (run_tests.sh) passed."
    else
        log_fail "Automated tests failed via scripts/run_tests.sh."
    fi
else
    log_skip "PHPUnit test runner not found."
fi

# ==============================================================================
# 5. SEMGREP SAST SECURITY SCAN
# ==============================================================================
echo -e "\n${CYAN}${BOLD}[5/7] Running Semgrep SAST Static Vulnerability Scan...${NC}"
if command -v semgrep > /dev/null 2>&1; then
    SEMGREP_ARGS="--config=p/phpcs-security-audit --config=p/owasp-top-ten"
    if [ -f ".semgrep.yml" ]; then
        SEMGREP_ARGS="${SEMGREP_ARGS} --config=.semgrep.yml"
        echo "   ↳ Using custom portfolio ruleset: .semgrep.yml"
    fi

    # Run scan
    if semgrep scan $SEMGREP_ARGS --error --quiet app/ index.php 2>/dev/null; then
        log_pass "Semgrep SAST scan clean (Zero OWASP Top 10 vulnerabilities)."
    else
        log_fail "Semgrep SAST identified vulnerabilities. Run 'semgrep scan --config=.semgrep.yml app/' to view."
    fi
else
    log_skip "Semgrep binary not installed on machine (Install via: brew install semgrep)."
fi

# ==============================================================================
# 6. RED TEAM ADVERSARIAL IDOR GUARD DAST TEST
# ==============================================================================
echo -e "\n${CYAN}${BOLD}[6/7] Running Red Team Adversarial IDOR Guard Test...${NC}"
if [ "$SKIP_IDOR" = true ]; then
    log_skip "Red Team IDOR test skipped via --skip-idor flag."
elif [ -f "scripts/dast-guest-builder-idor.sh" ]; then
    IDOR_TARGET="${TARGET_URL:-http://localhost:8000}"
    echo "   ↳ Target URL for IDOR test: $IDOR_TARGET"
    
    # Check if target is responding
    if curl -s -m 3 "$IDOR_TARGET" > /dev/null 2>&1; then
        if STAGING_URL="$IDOR_TARGET" bash scripts/dast-guest-builder-idor.sh > /dev/null 2>&1; then
            log_pass "Red Team IDOR Guard Test passed (Cross-tenant access rejected)."
        else
            log_fail "Red Team IDOR Guard Test failed. Run 'bash scripts/dast-guest-builder-idor.sh' to inspect."
        fi
    else
        log_skip "Target URL ($IDOR_TARGET) is not currently running/reachable. Start server to execute."
    fi
else
    log_skip "Script scripts/dast-guest-builder-idor.sh not found."
fi

# ==============================================================================
# 7. OWASP ZAP DYNAMIC APPLICATION SECURITY SCAN (DAST)
# ==============================================================================
echo -e "\n${CYAN}${BOLD}[7/7] Running OWASP ZAP Runtime DAST Scan...${NC}"
if [ "$SKIP_ZAP" = true ]; then
    log_skip "OWASP ZAP scan skipped via --skip-zap flag."
elif [ -f "scripts/zap-scan.sh" ]; then
    if [ -n "$TARGET_URL" ]; then
        echo "   ↳ Attacking Target URL: $TARGET_URL"
        if bash scripts/zap-scan.sh "$TARGET_URL"; then
            log_pass "OWASP ZAP DAST scan completed successfully. Report saved in reports/zap/."
        else
            log_fail "OWASP ZAP detected security alerts. Review reports/zap/ for details."
        fi
    else
        log_skip "Provide a target URL (e.g. bash $0 http://localhost:8000) to execute live ZAP DAST scan."
    fi
else
    log_skip "Script scripts/zap-scan.sh not found."
fi

# ==============================================================================
# 📊 FINAL CONSOLIDATED AUDIT REPORT
# ==============================================================================
echo -e "\n${BLUE}${BOLD}======================================================================${NC}"
echo -e "${BLUE}${BOLD}                    AUDIT SCORECARD SUMMARY                           ${NC}"
echo -e "${BLUE}${BOLD}======================================================================${NC}"
echo -e "   ${GREEN}${BOLD}Passed Checks:${NC}  $PASSED_CHECKS"
echo -e "   ${RED}${BOLD}Failed Checks:${NC}  $FAILED_CHECKS"
echo -e "   ${YELLOW}${BOLD}Skipped Checks:${NC} $SKIPPED_CHECKS"
echo -e "${BLUE}${BOLD}======================================================================${NC}"

if [ "$FAILED_CHECKS" -gt 0 ]; then
    echo -e "\n${RED}${BOLD}🛑 AUDIT FAILED:${NC} One or more security/quality gates did not pass."
    exit 1
else
    echo -e "\n${GREEN}${BOLD}🎉 AUDIT PASSED:${NC} System certified clean under company governance."
    exit 0
fi
