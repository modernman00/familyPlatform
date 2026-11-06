#!/bin/bash
set -e

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# ------------------------------------------------------------------------------
# ⚡ CPU Priority Throttling & Global Cross-Chat Mutex Queue (Olutobi & SRE Gate)
# Prevents concurrent whole-site Chrome/HTTP scans from stampeding CPU/RAM.
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
        echo "$(basename "$ROOT_DIR"):scan-site" > "$LOCK_DIR/owner"
        date +%s > "$LOCK_DIR/timestamp"
        LOCK_ACQUIRED=1
        break
    fi

    HOLDER_PID="$(cat "$LOCK_DIR/pid" 2>/dev/null || echo "")"
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
        echo "⏳ [GLOBAL QUEUE] Waiting for active portfolio gate (PID ${HOLDER_PID:-unknown})..."
        WAIT_ANNOUNCED=1
    fi
    sleep 1
done

if [ -x "$ROOT_DIR/vendor/bin/scan-site" ]; then
    exec php "$ROOT_DIR/vendor/bin/scan-site" --dir="$ROOT_DIR" "$@"
elif [ -f "$ROOT_DIR/vendor/modernman00/shared-lib/bin/scan-site" ]; then
    exec php "$ROOT_DIR/vendor/modernman00/shared-lib/bin/scan-site" --dir="$ROOT_DIR" "$@"
else
    exec php "/Users/waleolaogun/Sites/shared-lib/bin/scan-site" --dir="$ROOT_DIR" "$@"
fi
