#!/usr/bin/env bash
# ==============================================================================
# 🔒 PORTFOLIO MUTEX & ORPHANED BROWSER GUARD
# ==============================================================================
# Serialises heavy test commands (Playwright, Cypress, full PHPUnit) across all
# portfolio workspaces using /tmp/portfolio_gate_mutex.lock, lowers CPU priority,
# and cleans up orphaned chrome-headless-shell renderers before/after runs.
#
# Usage:
#   bash scripts/run_locked.sh <command> [args...]
# ==============================================================================

set -uo pipefail

if [ "$#" -eq 0 ]; then
    echo "Usage: bash scripts/run_locked.sh <command> [args...]" >&2
    exit 2
fi

renice -n 10 $$ >/dev/null 2>&1 || true

APP_NAME="$(basename "$(pwd)")"
LOCK_DIR="/tmp/portfolio_gate_mutex.lock"
LOCK_ACQUIRED=0
WAIT_ANNOUNCED=0
WAIT_START=$(date +%s)

cleanup_lock_and_browsers() {
    if [ "$LOCK_ACQUIRED" -eq 1 ]; then
        pkill -u "${USER:-$(id -un)}" -f "chrome-headless-shell" >/dev/null 2>&1 || true
        if [ -f "$LOCK_DIR/pid" ] && [ "$(cat "$LOCK_DIR/pid" 2>/dev/null)" = "$$" ]; then
            rm -rf "$LOCK_DIR" 2>/dev/null || true
        fi
    fi
}
trap cleanup_lock_and_browsers EXIT INT TERM

while true; do
    if mkdir "$LOCK_DIR" 2>/dev/null; then
        echo "$$" > "$LOCK_DIR/pid"
        echo "${APP_NAME}:e2e" > "$LOCK_DIR/owner"
        date +%s > "$LOCK_DIR/timestamp"
        LOCK_ACQUIRED=1
        if [ "$WAIT_ANNOUNCED" -eq 1 ]; then
            echo "⚡ [GLOBAL QUEUE] Slot acquired after $(( $(date +%s) - WAIT_START ))s. Running: $*"
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
    elif [ "$LOCK_TS" -gt 0 ] && [ $(( NOW_TS - LOCK_TS )) -gt 300 ]; then
        rm -rf "$LOCK_DIR" 2>/dev/null || true
        continue
    fi

    if [ "$WAIT_ANNOUNCED" -eq 0 ]; then
        echo "⏳ [GLOBAL QUEUE] Another heavy gate/test is active in ${HOLDER_OWNER} (PID ${HOLDER_PID:-unknown}). Queuing..."
        WAIT_ANNOUNCED=1
    fi
    sleep 1
done

# Clean up any orphaned headless Chromium renderers before launching a fresh E2E run
pkill -u "${USER:-$(id -un)}" -f "chrome-headless-shell" >/dev/null 2>&1 || true

"$@"
