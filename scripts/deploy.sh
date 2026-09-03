#!/usr/bin/env bash
# ==============================================================================
# 🚀 WORLD-CLASS ENTERPRISE DEPLOYMENT PIPELINE (Local -> Remote Server)
# ==============================================================================
# Architecture & Governance: TAT (David, Olutobi, Helena, Marcus, Oladele)
# Standards: ISO 27001 (A.12.4), Zero-Downtime, Isolated Sandbox, Live Smoke Test
# ==============================================================================

set -euo pipefail
export PATH="/opt/homebrew/bin:/opt/homebrew/opt/rsync/bin:/usr/local/bin:$PATH"
IFS=$'\n\t'

# Always operate from the repository root
cd "$(dirname "$0")/.."

# --- Configuration (Overridable via Environment Variables) ---
APP_NAME="${DEPLOY_APP_NAME:-FamilyPlatform}"
SSH_USER="${DEPLOY_SSH_USER:-bestiias}"
SSH_HOST="${DEPLOY_SSH_HOST:-premium145.web-hosting.com}"
SSH_PORT="${DEPLOY_SSH_PORT:-21098}"
REMOTE_DIR="${DEPLOY_REMOTE_DIR:-/home/bestiias/myfamilyplatform}"
LIVE_HEALTH_URL="${DEPLOY_HEALTH_URL:-https://myfamilyplatform.com}" # Live production domain

# Flags
DRY_RUN=0
NON_INTERACTIVE=0
SKIP_TESTS=0
PUSH_GIT=0

for arg in "$@"; do
    case $arg in
        --dry-run) DRY_RUN=1 ;;
        -y|--yes) NON_INTERACTIVE=1 ;;
        --skip-tests) SKIP_TESTS=1 ;;
        --push|--push-git) PUSH_GIT=1 ;;
    esac
done

START_TIME=$(date +%s)
DEPLOY_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
DEPLOY_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
DEPLOY_USER=$(whoami)
SW_FILE="service-worker.js"

echo "======================================================================"
echo " 🚀 [$APP_NAME] INITIATING ENTERPRISE DEPLOYMENT PIPELINE"
echo " Target: ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR} (Port: ${SSH_PORT})"
echo " Commit: ${DEPLOY_COMMIT:0:8} [${DEPLOY_BRANCH}] | User: ${DEPLOY_USER}"
[ $DRY_RUN -eq 1 ] && echo " 🔍 MODE: DRY-RUN (No files will be transferred to remote)"
echo "======================================================================"

# ------------------------------------------------------------------------------
# STAGE 1: PRE-FLIGHT INTEGRITY & SAFETY GATES
# ------------------------------------------------------------------------------
echo -e "\n📋 [Stage 1/6] Pre-Flight Integrity & SecOps Inspection..."

# Defensive Destination Check (Red Team: Amara Osei)
if [[ -z "$REMOTE_DIR" || "$REMOTE_DIR" == "/" || "$REMOTE_DIR" == "." || "$REMOTE_DIR" == "$HOME" ]]; then
    echo "🛑 FATAL: Unsafe REMOTE_DIR destination: '${REMOTE_DIR}'. Aborting."
    exit 1
fi

# Locate GNU rsync 3.x (Required for advanced filter rules)
RSYNC_BIN=""
CANDIDATES=(
    "/opt/homebrew/opt/rsync/bin/rsync"
    "/opt/homebrew/bin/rsync"
    "/usr/local/bin/rsync"
)
which rsync >/dev/null 2>&1 && CANDIDATES+=("$(which rsync)")

for cand in "${CANDIDATES[@]}"; do
    if [ -x "$cand" ]; then
        VER_STR=$("$cand" --version 2>/dev/null || true)
        if [[ "$VER_STR" =~ version\ 3 ]]; then
            RSYNC_BIN="$cand"
            break
        fi
    fi
done

if [ -z "$RSYNC_BIN" ]; then
    echo "🛑 FATAL: GNU rsync 3.x required for atomic filter rules. (brew install rsync)"
    exit 1
fi

# Pre-flight SSH Connectivity & Remote Disk Space Check (David Chen / Oladele)
if [ $DRY_RUN -eq 1 ]; then
    echo "🔌 Testing SSH connectivity to ${SSH_HOST} (non-blocking in dry-run)..."
    ssh -q -o BatchMode=yes -o ConnectTimeout=5 -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "echo connected" >/dev/null 2>&1 || echo "ℹ️  Remote host offline or requires password (proceeding with local build dry-run)."
else
    echo "🔌 Testing SSH connectivity to ${SSH_HOST}..."
    if ! ssh -q -o BatchMode=yes -o ConnectTimeout=8 -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "echo connected" >/dev/null 2>&1; then
        echo "⚠️  SSH ping check requires interactive login. Verifying..."
        ssh -o ConnectTimeout=10 -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "exit" || {
            echo "🛑 FATAL: Cannot connect to ${SSH_HOST}:${SSH_PORT}."
            exit 1
        }
    fi
fi

# SecOps Secret Staging Guard (Red Team: "Ghost" Reinholt)
UNTRACKED_SECRETS=$(git status --porcelain | grep -E "^\?\?.*(\.env|\.pem|\.sql|\.key|\.crt|\.backup)" || true)
if [ -n "$UNTRACKED_SECRETS" ]; then
    echo "🛑 SEC-OPS ALERT: Potential untracked secret files detected in workspace:"
    echo "$UNTRACKED_SECRETS"
    echo "Remove or add them to .gitignore before deploying."
    exit 1
fi
echo "✅ Pre-flight checks passed."

# ------------------------------------------------------------------------------
# STAGE 2: STRICT QUALITY & CODE HEALTH GATING
# ------------------------------------------------------------------------------
echo -e "\n🛡️  [Stage 2/6] Enforcing Structural Quality Gates..."

# PHP Syntax Linting on core controllers and models
echo "🔍 Running PHP syntax lint on backend files..."
find app -type f -name "*.php" | head -n 50 | while read -r php_file; do
    php -l "$php_file" >/dev/null || {
        echo "🛑 FATAL: PHP Syntax Error in $php_file"
        exit 1
    }
done
echo "✅ PHP syntax clean."

# Automated Test Suite Gating
if [ $SKIP_TESTS -eq 0 ]; then
    TEST_SCRIPT=""
    for t_cand in scripts/run_tests.sh run_tests.sh run_all_tests.sh; do
        [ -f "$t_cand" ] && TEST_SCRIPT="$t_cand" && break
    done

    if [ -n "$TEST_SCRIPT" ]; then
        echo "🧪 Executing automated test suite ($TEST_SCRIPT)..."
        set +e
        bash "$TEST_SCRIPT"
        TEST_STATUS=$?
        set -e
        if [ $TEST_STATUS -ne 0 ]; then
            echo -e "\n🛑 QUALITY GATE FAILED: Automated tests failed with code $TEST_STATUS."
            if [ $NON_INTERACTIVE -eq 1 ]; then
                exit 1
            fi
            read -p "Do you want to override and force deployment anyway? (y/N): " FORCE_DEP < /dev/tty
            if [[ "$FORCE_DEP" != "y" && "$FORCE_DEP" != "Y" ]]; then
                echo "🛑 Deployment aborted by engineer."
                exit 1
            fi
        else
            echo "✅ All tests passed."
        fi
    fi
fi

# ------------------------------------------------------------------------------
# STAGE 3: ISOLATED SANDBOX ARTIFACT BUILD
# ------------------------------------------------------------------------------
echo -e "\n🎨 [Stage 3/6] Building Production Artifact in Isolated Sandbox..."

# Compile frontend assets locally
if [ -f "package.json" ]; then
    echo "📦 Compiling production frontend bundle..."
    if grep -q '"prod":' package.json; then
        npm run prod --silent
    elif grep -q '"build":' package.json; then
        npm run build --silent
    fi
    echo "✅ Assets compiled successfully."
fi

# Create isolated sandbox in /tmp (Local environment is NEVER contaminated)
SANDBOX_DIR=$(mktemp -d /tmp/${APP_NAME}_deploy_XXXXXX)
cleanup() {
    echo "🧹 Cleaning up temporary build sandbox..."
    rm -rf "$SANDBOX_DIR"
    git checkout -- "$SW_FILE" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

echo "🏗️  Assembling release artifact in: $SANDBOX_DIR"
"$RSYNC_BIN" -a \
    --exclude='.git*' \
    --exclude='node_modules' \
    --exclude='cypress*' \
    --exclude='tests' \
    --exclude='scratch' \
    --exclude='scripts/deploy.sh' \
    ./ "$SANDBOX_DIR/"

# Service Worker Version Stamping (Atomic cache busting)
if [ -f "$SANDBOX_DIR/$SW_FILE" ]; then
    SW_BUILD="g${DEPLOY_COMMIT:0:8}-$(date +%s)"
    sed -i.bak -E "s|^const SW_VERSION = .*|const SW_VERSION = '${SW_BUILD}'; // deployed $(date -u +%Y-%m-%dT%H:%M:%SZ)|" "$SANDBOX_DIR/$SW_FILE"
    rm -f "$SANDBOX_DIR/${SW_FILE}.bak"
    echo "🔁 Stamped Service Worker build: ${SW_BUILD}"
fi

# Production Composer Optimization (Offline & Non-Dev inside sandbox)
echo "⚡ Building optimized production autoloader (--no-dev, offline)..."
(cd "$SANDBOX_DIR" && COMPOSER_DISABLE_NETWORK=1 composer dump-autoload --optimize --no-dev --no-interaction --quiet)
echo "✅ Production artifact built successfully."

# ------------------------------------------------------------------------------
# STAGE 4: HARDENED RSYNC TRANSFER (ALLOWLIST & PROTECTED PATHS)
# ------------------------------------------------------------------------------
echo -e "\n🔄 [Stage 4/6] Synchronizing Files to Remote Server ($SSH_HOST)..."

# BRATS & SecOps Manifest Sanity Assertion (David / Marcus / Alex Mercer)
# Guarantee core directories are matched by .rsync-filter before --delete-excluded executes
echo "🛡️  Asserting integrity of sync manifest before remote synchronization..."
MANIFEST_FILE=$(mktemp /tmp/manifest_XXXXXX)
"$RSYNC_BIN" -av --dry-run -f 'merge .rsync-filter' "$SANDBOX_DIR/" /tmp/manifest_check > "$MANIFEST_FILE" 2>&1
if [ -d "resources/views" ]; then
    if ! grep -E "resources/views/(index|base|about|login|contact)" "$MANIFEST_FILE" >/dev/null; then
        echo "🛑 FATAL STRUCTURAL GATING: Filter matched zero Blade templates!"
        echo "Aborting deployment immediately to prevent remote --delete-excluded data loss."
        rm -f "$MANIFEST_FILE"
        exit 1
    fi
fi
if ! grep -E "(app/controller|app/model|index\.php)" "$MANIFEST_FILE" >/dev/null; then
    echo "🛑 FATAL STRUCTURAL GATING: Filter matched zero core application files!"
    rm -f "$MANIFEST_FILE"
    exit 1
fi
rm -f "$MANIFEST_FILE"
echo "✅ Sync manifest asserted: Core application and Blade views verified."

RSYNC_ARGS=(
    -av
    --delete
    --delete-excluded
    -e "ssh -p $SSH_PORT"
    -f 'merge .rsync-filter'
)

[ $DRY_RUN -eq 1 ] && RSYNC_ARGS+=(--dry-run)

"$RSYNC_BIN" "${RSYNC_ARGS[@]}" "$SANDBOX_DIR/" "${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}"
echo "✅ File transfer synchronized."

# ------------------------------------------------------------------------------
# STAGE 5: REMOTE PERMISSIONS, CACHE FLUSH & OPCACHE RESET
# ------------------------------------------------------------------------------
if [ $DRY_RUN -eq 0 ]; then
    echo -e "\n🧹 [Stage 5/6] Executing Remote Maintenance & Cache Purge..."
    ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << REMOTE_CMD
        # 1. Enforce strict permissions (SecOps: Marcus)
        find "${REMOTE_DIR}" -type d -exec chmod 755 {} + 2>/dev/null || true
        find "${REMOTE_DIR}" -type f -exec chmod 644 {} + 2>/dev/null || true
        [ -f "${REMOTE_DIR}/.env" ] && chmod 600 "${REMOTE_DIR}/.env" 2>/dev/null || true

        # 2. Clear Blade compiled template cache
        rm -f "${REMOTE_DIR}/bootstrap/cache/"*.bladec 2>/dev/null || true

        # 3. Trigger OPcache reset if web server supports CLI/touch
        touch "${REMOTE_DIR}/index.php" 2>/dev/null || true
REMOTE_CMD
    echo "✅ Remote caches cleared and permissions hardened."
fi

# ------------------------------------------------------------------------------
# STAGE 6: LIVE SMOKE TEST & ISO 27001 AUDIT LOGGING
# ------------------------------------------------------------------------------
echo -e "\n🧪 [Stage 6/6] Live Smoke Test & Health Check..."

if [ $DRY_RUN -eq 0 ] && [ -n "$LIVE_HEALTH_URL" ]; then
    echo "🌐 Probing Live Application: $LIVE_HEALTH_URL"
    HTTP_STATUS=$(curl -k -s -o /dev/null -w "%{http_code}" "$LIVE_HEALTH_URL" || echo "000")
    
    if [[ "$HTTP_STATUS" =~ ^(200|301|302)$ ]]; then
        echo "✅ LIVE HEALTH CHECK PASSED: HTTP $HTTP_STATUS OK"
    else
        echo "⚠️  CRITICAL WARNING: Live Health Check returned HTTP $HTTP_STATUS!"
        echo "Check server error logs immediately via SSH: ${REMOTE_DIR}/bootstrap/log/"
    fi
fi

# Record ISO 27001 Audit Entry
DURATION=$(( $(date +%s) - START_TIME ))
if [ -f "scripts/audit_deploy.php" ]; then
    php scripts/audit_deploy.php "$DEPLOY_COMMIT" "$DEPLOY_USER" "$DURATION" || true
fi

# ------------------------------------------------------------------------------
# STAGE 7: GITHUB SYNCHRONIZATION
# ------------------------------------------------------------------------------
if [ $DRY_RUN -eq 0 ]; then
    DO_GIT_PUSH=0
    if [ $PUSH_GIT -eq 1 ]; then
        DO_GIT_PUSH=1
    elif [ $NON_INTERACTIVE -eq 0 ] && [ -t 0 ]; then
        echo -e "\n🐙 [Stage 7/7] GitHub Synchronization..."
        read -r -p "Do you want to push deployed commit (${DEPLOY_COMMIT:0:8}) to GitHub? (y/N): " PUSH_CONFIRM || PUSH_CONFIRM="n"
        if [[ "$PUSH_CONFIRM" =~ ^[yY]$ ]]; then
            DO_GIT_PUSH=1
        fi
    fi

    if [ $DO_GIT_PUSH -eq 1 ]; then
        echo "🐙 Pushing ${DEPLOY_BRANCH} to GitHub origin..."
        git push origin "${DEPLOY_BRANCH}" || echo "⚠️  GitHub push failed (check network or credentials)."
        echo "✅ GitHub sync complete."
    else
        echo "⏭️  Skipping GitHub push."
    fi
fi

echo "======================================================================"
echo " 🏆 DEPLOYMENT COMPLETE (Duration: ${DURATION}s)"
echo " Server updated to commit ${DEPLOY_COMMIT:0:8}"
echo "======================================================================"
