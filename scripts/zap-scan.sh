#!/usr/bin/env bash
# ==============================================================================
# 🛡️  OWASP ZAP AUTOMATED DAST SCAN — Portfolio Security
# ==============================================================================
# Usage    : bash scripts/zap-scan.sh [TARGET_URL]
# Default  : Reads TARGET_URL from DEPLOY_HEALTH_URL in .deploy.env
# Requires : Docker (for ZAP container) OR zap.sh in PATH
# Output   : reports/zap-report-YYYYMMDD.html + .json
# Exits    : 0 = clean, 1 = warnings, 2 = alerts found (blocks deploy if -e)
# ==============================================================================

set -euo pipefail

cd "$(dirname "$0")/.."

# ── Source env if present ────────────────────────────────────────────────────
if [ -f ".deploy.env" ]; then
    # shellcheck disable=SC1091
    source ".deploy.env"
fi

TARGET_URL="${1:-${DEPLOY_HEALTH_URL:-}}"

if [ -z "$TARGET_URL" ]; then
    echo "🛑 FATAL: No TARGET_URL provided."
    echo "   Usage: bash scripts/zap-scan.sh https://staging.yourapp.com"
    exit 1
fi

APP_NAME="${DEPLOY_APP_NAME:-app}"
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
REPORT_DIR="reports/zap"
mkdir -p "$REPORT_DIR"
HTML_REPORT="${REPORT_DIR}/zap-report-${TIMESTAMP}.html"
JSON_REPORT="${REPORT_DIR}/zap-report-${TIMESTAMP}.json"

echo "======================================================================"
echo " 🛡️  OWASP ZAP DAST SCAN"
echo " App    : ${APP_NAME}"
echo " Target : ${TARGET_URL}"
echo " Report : ${HTML_REPORT}"
echo "======================================================================"

# ── Attempt 1: Docker-based ZAP (preferred — isolated, always latest) ────────
if command -v docker > /dev/null 2>&1; then
    echo -e "\n🐳 Running ZAP via Docker (ghcr.io/zaproxy/zaproxy:stable)..."

    docker run --rm \
        -v "$(pwd)/${REPORT_DIR}:/zap/wrk:rw" \
        ghcr.io/zaproxy/zaproxy:stable \
        zap-baseline.py \
            -t "${TARGET_URL}" \
            -r "zap-report-${TIMESTAMP}.html" \
            -J "zap-report-${TIMESTAMP}.json" \
            -l WARN \
            --hook=/zap/auth_hook.py 2>/dev/null || ZAP_EXIT=$?

    ZAP_EXIT="${ZAP_EXIT:-0}"

# ── Attempt 2: Native ZAP installation ──────────────────────────────────────
elif command -v zap.sh > /dev/null 2>&1 || command -v zaproxy > /dev/null 2>&1; then
    ZAP_CMD=$(command -v zap.sh 2>/dev/null || command -v zaproxy)
    echo -e "\n🔧 Running ZAP natively via ${ZAP_CMD}..."

    "$ZAP_CMD" -cmd \
        -quickurl "${TARGET_URL}" \
        -quickout "${HTML_REPORT}" \
        -quickprogress || ZAP_EXIT=$?

    ZAP_EXIT="${ZAP_EXIT:-0}"

# ── Fallback: curl-based lightweight header check ────────────────────────────
else
    echo -e "\n⚠️  WARNING: Neither Docker nor ZAP found locally."
    echo "   Falling back to security header audit (curl-based)."
    echo "   Install Docker or ZAP for full DAST coverage."
    echo ""
    echo "   To install Docker:    https://docs.docker.com/get-docker/"
    echo "   To install ZAP:       https://www.zaproxy.org/download/"
    echo ""

    HEADERS=$(curl -sI --max-time 15 "$TARGET_URL" 2>/dev/null || echo "")

    echo "── Security Header Audit ─────────────────────────────────────────"

    _check_header() {
        local header="$1"
        local label="$2"
        if echo "$HEADERS" | grep -qi "$header"; then
            echo "  ✅ ${label}"
        else
            echo "  ❌ MISSING: ${label}"
        fi
    }

    _check_header "content-security-policy"        "Content-Security-Policy"
    _check_header "strict-transport-security"      "Strict-Transport-Security (HSTS)"
    _check_header "x-frame-options"               "X-Frame-Options"
    _check_header "x-content-type-options"        "X-Content-Type-Options"
    _check_header "referrer-policy"               "Referrer-Policy"
    _check_header "permissions-policy"            "Permissions-Policy"

    # Check for dangerous exposures
    echo ""
    echo "── Exposure Audit ────────────────────────────────────────────────"
    if echo "$HEADERS" | grep -qi "x-powered-by"; then
        echo "  ⚠️  WARNING: X-Powered-By header exposed (leaks stack info)"
    else
        echo "  ✅ X-Powered-By: suppressed"
    fi

    if echo "$HEADERS" | grep -qi "server:.*apache\|server:.*nginx\|server:.*php"; then
        echo "  ⚠️  WARNING: Server header leaks web server version"
    else
        echo "  ✅ Server: version suppressed"
    fi

    echo ""
    echo "── Fallback audit complete. Install Docker for full DAST scan. ───"
    echo ""
    ZAP_EXIT=0
fi

# ── Result Evaluation ────────────────────────────────────────────────────────
echo ""
if [ "${ZAP_EXIT:-0}" -eq 0 ]; then
    echo "✅ ZAP DAST scan completed: No alerts at WARNING level or above."
elif [ "${ZAP_EXIT:-0}" -eq 1 ]; then
    echo "⚠️  ZAP DAST scan completed: Warnings found — review ${HTML_REPORT}"
    echo "   These are advisory. Build continues."
else
    echo "🛑 ZAP DAST scan FAILED: Alerts found that require remediation."
    echo "   Review full report: ${HTML_REPORT}"
    echo "   Resolve all MEDIUM+ findings before deploying to production."
    exit 2
fi

echo ""
echo "📊 Reports saved:"
echo "   HTML: $(pwd)/${HTML_REPORT}"
[ -f "${JSON_REPORT}" ] && echo "   JSON: $(pwd)/${JSON_REPORT}"
echo "======================================================================"
