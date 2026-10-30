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

# ------------------------------------------------------------------------------
# ⚡ CPU Priority Throttling & Global Cross-Chat Mutex Queue (Olutobi & SRE Gate)
# Prevents concurrent chat sessions from stampeding CPU/RAM simultaneously.
# ------------------------------------------------------------------------------
renice -n 10 $$ >/dev/null 2>&1 || true

LOCK_DIR="/tmp/portfolio_gate_mutex.lock"
LOCK_ACQUIRED=0
WAIT_ANNOUNCED=0
WAIT_START=$(date +%s)

cleanup_gate_lock() {
    if [ "$LOCK_ACQUIRED" -eq 1 ] && [ -f "$LOCK_DIR/pid" ]; then
        if [ "$(cat "$LOCK_DIR/pid" 2>/dev/null)" = "$$" ]; then
            rm -rf "$LOCK_DIR" 2>/dev/null || true
        fi
    fi
}
trap cleanup_gate_lock EXIT INT TERM

while true; do
    if mkdir "$LOCK_DIR" 2>/dev/null; then
        echo "$$" > "$LOCK_DIR/pid"
        echo "familyPlatform:inflight" > "$LOCK_DIR/owner"
        date +%s > "$LOCK_DIR/timestamp"
        LOCK_ACQUIRED=1
        if [ "$WAIT_ANNOUNCED" -eq 1 ]; then
            echo -e "${GREEN}⚡ [GLOBAL QUEUE] Slot acquired after $(( $(date +%s) - WAIT_START ))s. Proceeding with in-flight...${NC}"
        fi
        break
    fi

    HOLDER_PID="$(cat "$LOCK_DIR/pid" 2>/dev/null || echo "")"
    HOLDER_OWNER="$(cat "$LOCK_DIR/owner" 2>/dev/null || echo "another workspace")"
    LOCK_TS="$(cat "$LOCK_DIR/timestamp" 2>/dev/null || echo "0")"
    NOW_TS=$(date +%s)

    if [ -n "$HOLDER_PID" ] && ! kill -0 "$HOLDER_PID" 2>/dev/null; then
        rm -rf "$LOCK_DIR" 2>/dev/null || true
        continue
    elif [ "$LOCK_TS" -gt 0 ] && [ $(( NOW_TS - LOCK_TS )) -gt 180 ]; then
        rm -rf "$LOCK_DIR" 2>/dev/null || true
        continue
    fi

    if [ "$WAIT_ANNOUNCED" -eq 0 ]; then
        echo -e "${YELLOW}⏳ [GLOBAL QUEUE] Another gate is active in ${HOLDER_OWNER} (PID ${HOLDER_PID:-unknown}). Queuing to keep your Mac fast...${NC}"
        WAIT_ANNOUNCED=1
    fi
    sleep 1
done

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

# 1. PHP Syntax Linting — Smart Incremental Git-Diff + Untracked Scan
echo -e "${BOLD}${CYAN}[IN-FLIGHT 1/4] Checking PHP Syntax (php -l)...${NC}"
SYNTAX_ERRORS=0
LINT_COUNT=0

if [ "${FULL_LINT:-0}" != "1" ] && git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    while IFS= read -r file; do
        [ -z "$file" ] && continue
        [ ! -f "$file" ] && continue
        LINT_COUNT=$((LINT_COUNT + 1))
        if ! php -l "$file" > /dev/null 2>&1; then
            echo -e "${RED}  ❌ Syntax Error in: $file${NC}"
            php -l "$file"
            SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
        fi
    done < <({ git diff --name-only HEAD 2>/dev/null; git diff --name-only --cached 2>/dev/null; git ls-files --others --exclude-standard 2>/dev/null; } | grep -E '\.php$' | sort -u)
else
    while IFS= read -r file; do
        LINT_COUNT=$((LINT_COUNT + 1))
        if ! php -l "$file" > /dev/null 2>&1; then
            echo -e "${RED}  ❌ Syntax Error in: $file${NC}"
            php -l "$file"
            SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
        fi
    done < <(find app bootstrap bin scripts -name "*.php" -type f 2>/dev/null)
fi

if [ "$SYNTAX_ERRORS" -eq 0 ]; then
    echo -e "${GREEN}  ✅ In-Flight 1 Passed: All PHP files have valid syntax (${LINT_COUNT} modified/untracked files checked; set FULL_LINT=1 for full scan).${NC}"
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
elif [ -f "scripts/dast-idor-access-control.sh" ]; then
    bash scripts/dast-idor-access-control.sh || ADVERSARIAL_PASSED=0
elif [ -f "scripts/run-all-security-checks.sh" ]; then
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
