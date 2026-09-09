#!/usr/bin/env bash
# ==============================================================================
# 🚀 DIRECT ATOMIC RSYNC DEPLOYMENT PIPELINE  v2.1
# ==============================================================================
# Architecture : Direct Atomic Rsync into DocumentRoot, Maintenance Guard,
#                Persistent Storage Protection, OPcache Reset, ISO 27001 Audit
# Framework    : Custom PHP 8.2 (AltoRouter + BladeOne + modernman00/shared-lib)
# Target System: Namecheap cPanel / Cloud VPS (DocumentRoot: $DEPLOY_REMOTE_DIR)
# Governance   : Technical Approval Team (TAT) Certified
# ==============================================================================
# USAGE:
#   source .deploy.env && bash scripts/deploy.sh
#
# REQUIRED ENV VARS:
#   DEPLOY_APP_NAME   — Human-readable app name, e.g. "iAccountApp"
#   DEPLOY_SSH_USER   — SSH username
#   DEPLOY_SSH_HOST   — SSH hostname
#   DEPLOY_SSH_PORT   — SSH port (typically 21098 for Namecheap)
#   DEPLOY_REMOTE_DIR — Absolute remote path, e.g. /home/bestiias/iaccountapp
#   DEPLOY_HEALTH_URL — Full health check URL, e.g. https://www.iaccountapp.com
#
# OPTIONAL ENV VARS:
#   DEPLOY_HEALTH_WAIT — Seconds to wait before health check (default: 3)
# ==============================================================================

set -euo pipefail
IFS=$'\n\t'

# Always operate from the repository root
cd "$(dirname "$0")/.."

################################################################################
# 1. MANDATORY ENVIRONMENT VARIABLE VALIDATION
################################################################################

_require_env() {
    local var="$1"
    if [ -z "${!var:-}" ]; then
        echo "🛑 FATAL: Required environment variable \$${var} is not set."
        echo "   Source your .deploy.env file first: source .deploy.env && bash scripts/deploy.sh"
        exit 1
    fi
}

# Source .deploy.env if present (gitignored per-app config)
if [ -f ".deploy.env" ]; then
    # shellcheck disable=SC1091
    source ".deploy.env"
fi

_require_env DEPLOY_APP_NAME
_require_env DEPLOY_SSH_USER
_require_env DEPLOY_SSH_HOST
_require_env DEPLOY_SSH_PORT
_require_env DEPLOY_REMOTE_DIR
_require_env DEPLOY_HEALTH_URL

################################################################################
# 2. CONFIGURATION
################################################################################

APP_NAME="${DEPLOY_APP_NAME}"
SSH_USER="${DEPLOY_SSH_USER}"
SSH_HOST="${DEPLOY_SSH_HOST}"
SSH_PORT="${DEPLOY_SSH_PORT}"
REMOTE_ROOT="${DEPLOY_REMOTE_DIR}"
HEALTH_URL="${DEPLOY_HEALTH_URL}"
HEALTH_WAIT="${DEPLOY_HEALTH_WAIT:-3}"

START_TIME=$(date +%s)
RELEASE_ID=$(date +"%Y%m%d%H%M%S")

COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
USER_NAME=$(whoami)

# Sandboxed isolated build directory (cleaned on exit)
SANDBOX=$(mktemp -d "/tmp/${APP_NAME}_deploy_XXXXXX")

# Remote deploy lock path (released in cleanup)
DEPLOY_LOCK_PATH="${REMOTE_ROOT}/.deploy.lock"
DEPLOY_LOCK_HELD=false

cleanup() {
    rm -rf "$SANDBOX"
    if [ "$DEPLOY_LOCK_HELD" = "true" ]; then
        ssh -p "$SSH_PORT" -o ConnectTimeout=10 -o BatchMode=yes \
            "${SSH_USER}@${SSH_HOST}" "rmdir '${DEPLOY_LOCK_PATH}' 2>/dev/null || true" 2>/dev/null || true
        echo "🔓 Deploy lock released."
    fi
}
trap cleanup EXIT INT TERM

echo "======================================================================"
echo " 🚀 [$APP_NAME] INITIATING DIRECT ATOMIC DEPLOYMENT PIPELINE"
echo " Target  : ${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}"
echo " Commit  : ${COMMIT:0:8} [${BRANCH}]"
echo " Deployer: ${USER_NAME}"
echo "======================================================================"

################################################################################
# 3. PRE-FLIGHT: SSH CONNECTIVITY CHECK & LOCK ACQUISITION
################################################################################

echo -e "\n🔌 [1/8] Verifying SSH connectivity to ${SSH_HOST}..."
if ! ssh -p "$SSH_PORT" \
         -o ConnectTimeout=10 \
         -o BatchMode=yes \
         -o StrictHostKeyChecking=accept-new \
         -o LogLevel=INFO \
         "${SSH_USER}@${SSH_HOST}" exit 2>/tmp/_deploy_ssh_check.txt; then
    echo "🛑 FATAL: Cannot establish SSH connection to ${SSH_USER}@${SSH_HOST}:${SSH_PORT}"
    rm -f /tmp/_deploy_ssh_check.txt
    exit 1
fi
rm -f /tmp/_deploy_ssh_check.txt
echo "✅ SSH connection verified."

# Acquire remote deploy lock (atomic mkdir)
echo "🔒 Acquiring remote deploy lock..."
if ! ssh -p "$SSH_PORT" -o ConnectTimeout=10 -o BatchMode=yes "${SSH_USER}@${SSH_HOST}" \
    "mkdir '${DEPLOY_LOCK_PATH}' 2>/dev/null" ; then
    echo "🛑 FATAL: Deployment already in progress (lock exists: ${DEPLOY_LOCK_PATH})."
    echo "   If no deployment is running, remove it manually: rmdir ${DEPLOY_LOCK_PATH}"
    exit 1
fi
DEPLOY_LOCK_HELD=true
echo "✅ Deploy lock acquired."

################################################################################
# 4. GIT & WORKSPACE INTEGRITY
################################################################################

echo -e "\n🔍 [2/8] Verifying Git & Workspace Integrity..."

if ! git diff-index --quiet HEAD -- 2>/dev/null; then
    echo "⚠️  WARNING: Uncommitted changes detected in workspace:"
    git status -s
    echo ""
    if [ ! -e /dev/tty ]; then
        echo "🛑 ERROR: Non-interactive terminal detected. Please commit changes before deploying."
        exit 1
    fi
    read -r -p "Do you want to commit these changes now before deploying? (y/N): " AUTO_COMMIT < /dev/tty || AUTO_COMMIT="n"
    if [[ "$AUTO_COMMIT" =~ ^[yY]$ ]]; then
        read -r -p "📝 Enter commit message: " COMMIT_MSG < /dev/tty
        if [ -z "$COMMIT_MSG" ]; then
            echo "🛑 FATAL: Commit message cannot be empty. Aborting."
            exit 1
        fi
        git add -u
        git commit -m "$COMMIT_MSG"
        COMMIT=$(git rev-parse HEAD)
        echo "✅ Changes committed successfully (${COMMIT:0:8})."
    else
        echo "🛑 FATAL: Enterprise policy forbids deploying uncommitted code. Aborting."
        exit 1
    fi
fi

# Guard against accidental secret leaks
UNTRACKED_SECRETS=$(git status --porcelain | grep -E "^\?\?.*(\\.env|\\.pem|\\.sql|\\.key|\\.crt|\\.backup)" || true)
STAGED_SECRETS=$(git diff --cached --name-only | grep -E "(\\.env|\\.pem|\\.sql|\\.key|\\.crt|\\.backup)" || true)

if [ -n "$UNTRACKED_SECRETS" ]; then
    echo "🛑 SEC-OPS ALERT: Potential untracked secret files detected in workspace:"
    echo "$UNTRACKED_SECRETS"
    exit 1
fi

if [ -n "$STAGED_SECRETS" ]; then
    echo "🛑 SEC-OPS ALERT: Potential secret files are STAGED for commit:"
    echo "$STAGED_SECRETS"
    exit 1
fi

echo "✅ Git workspace clean. No secret leaks detected."

################################################################################
# 5. QUALITY GATES (PHP Lint → PHPStan → PHPUnit → Semgrep)
################################################################################

echo -e "\n🔍 [3/8] Running PHP Syntax Linting (Full Backend)..."
find app bootstrap -type f -name "*.php" \
    | xargs -P4 -I{} php -l {} 1>/dev/null \
    || { echo "🛑 FATAL: PHP syntax error detected."; exit 1; }
echo "✅ PHP syntax clean."

if [ -f "phpstan.neon" ]; then
    echo -e "\n🔍 Running PHPStan Static Analysis..."
    vendor/bin/phpstan analyse --no-progress --quiet || {
        echo "🛑 FATAL: PHPStan static analysis failed."
        exit 1
    }
    echo "✅ PHPStan passed."
fi

if [ -f "phpunit.xml" ]; then
    echo -e "\n🧪 Running PHPUnit Test Suite..."
    vendor/bin/phpunit --no-coverage || {
        echo "🛑 FATAL: Automated tests failed."
        exit 1
    }
    echo "✅ Tests passed."
fi

if command -v semgrep > /dev/null 2>&1; then
    echo -e "\n🛡️  Running Semgrep SecOps Scan..."
    semgrep scan --config="p/phpcs-security-audit" --config="p/owasp-top-ten" --error --quiet app/ index.php 2>/dev/null || {
        echo "🛑 FATAL: Semgrep identified structural security vulnerabilities."
        exit 1
    }
    echo "✅ Security scan clean."
fi

################################################################################
# 6. GITHUB PUSH (Source control is source of truth)
################################################################################

if [ "$BRANCH" = "master" ] || [ "$BRANCH" = "main" ]; then
    echo -e "\n🐙 [4/8] Pushing ${BRANCH} to GitHub origin..."
    git push origin "$BRANCH" || {
        echo "🛑 FATAL: GitHub push failed. Aborting deployment to keep source control aligned."
        exit 1
    }
    echo "✅ GitHub push successful."
fi

################################################################################
# 7. ASSET COMPILATION & SANDBOX ASSEMBLY
################################################################################

echo -e "\n📦 [5/8] Compiling Frontend Assets & Assembling Sandbox..."

if [ -f "package.json" ]; then
    if grep -q '"prod":' package.json; then
        npm run prod --silent
    elif grep -q '"build":' package.json; then
        npm run build --silent
    fi
fi

copy_item() {
    local item="$1"
    if [ -e "$item" ]; then
        cp -R "$item" "$SANDBOX/"
    fi
}

copy_item app
copy_item bootstrap
copy_item public
copy_item resources
copy_item vendor
copy_item composer.json
copy_item composer.lock
copy_item index.php
copy_item offline.html
copy_item .htaccess

if [ -f "manifest.json" ]; then
    copy_item manifest.json
fi

# Clean local cache & log artifacts inside sandbox
rm -rf "$SANDBOX/bootstrap/cache/"*
rm -rf "$SANDBOX/bootstrap/log/"*
touch "$SANDBOX/bootstrap/cache/.gitkeep"
touch "$SANDBOX/bootstrap/log/.gitkeep"

# Service Worker Cache Busting for PWA
SW_FILE="$SANDBOX/public/service-worker.js"
if [ -f "$SW_FILE" ]; then
    SW_BUILD="v${RELEASE_ID}-${COMMIT:0:8}"
    sed -i.bak -E "s|^const SW_VERSION = .*|const SW_VERSION = '${SW_BUILD}';|" "$SW_FILE"
    rm -f "${SW_FILE}.bak"
    echo "🔁 Stamped Service Worker: ${SW_BUILD}"
fi

SW_ROOT_FILE="$SANDBOX/service-worker.js"
if [ -f "service-worker.js" ]; then
    copy_item service-worker.js
    SW_BUILD="v${RELEASE_ID}-${COMMIT:0:8}"
    sed -i.bak -E "s|^const SW_VERSION = .*|const SW_VERSION = '${SW_BUILD}';|" "$SW_ROOT_FILE"
    rm -f "${SW_ROOT_FILE}.bak"
    echo "🔁 Stamped Root Service Worker: ${SW_BUILD}"
fi

# Local offline composer dump-autoload inside sandbox
echo "⚡ Generating optimized production autoloader (--no-dev)..."
(cd "$SANDBOX" && composer dump-autoload --optimize --no-dev --no-interaction --quiet 2>/dev/null || true)
echo "✅ Sandbox assembled and optimized."

################################################################################
# 8. DIRECT ATOMIC RSYNC SYNCHRONIZATION
################################################################################

echo -e "\n🚀 [6/8] Synchronizing Application Directly to ${REMOTE_ROOT}..."

# Briefly activate maintenance flag during rsync to avoid partial file execution
ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "touch '${REMOTE_ROOT}/.maintenance' 2>/dev/null || true"

rsync -az --delete \
    --timeout=120 \
    -e "ssh -p ${SSH_PORT}" \
    --exclude='.env' \
    --exclude='.deploy.lock' \
    --exclude='.git*' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='cypress*' \
    --exclude='scratch' \
    --exclude='public/uploads/*' \
    --exclude='public/uploads/**' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*.bladec' \
    --exclude='bootstrap/cache/*' \
    --exclude='bootstrap/log/*.log' \
    --exclude='bootstrap/log/*' \
    --exclude='scripts' \
    --exclude='releases' \
    --exclude='backups' \
    --exclude='shared' \
    --exclude='current*' \
    "$SANDBOX/" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}/"

echo "✅ Rsync synchronization complete."

# Re-run remote composer dump-autoload if composer binary is available on remote
ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" REMOTE_ROOT="$REMOTE_ROOT" "bash -s" <<'EOF'
COMPOSER_BIN=$(command -v composer 2>/dev/null \
    || ls /usr/local/bin/composer /usr/bin/composer /opt/cpanel/composer/bin/composer 2>/dev/null | head -1 \
    || echo "")
if [ -x "$COMPOSER_BIN" ]; then
    cd "$REMOTE_ROOT" || exit 1
    "$COMPOSER_BIN" dump-autoload --optimize --no-dev --no-interaction --quiet \
        && echo "✅ Remote Composer autoloader verified." \
        || echo "ℹ️ Remote autoloader skipped — local optimized vendor utilized."
fi
EOF

################################################################################
# 9. REMOTE PERMISSIONS, RUNTIME DIRS, CACHE PURGE & OPCACHE RESET
################################################################################

echo -e "\n🧹 [7/8] Enforcing Permissions, Purging Caches & Resetting OPcache..."

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
set -e

# Ensure runtime directories exist
mkdir -p "${REMOTE_ROOT}/storage/logs"
mkdir -p "${REMOTE_ROOT}/storage/framework/cache"
mkdir -p "${REMOTE_ROOT}/storage/framework/sessions"
mkdir -p "${REMOTE_ROOT}/storage/framework/views"
mkdir -p "${REMOTE_ROOT}/bootstrap/cache"
mkdir -p "${REMOTE_ROOT}/bootstrap/log"
mkdir -p "${REMOTE_ROOT}/public/uploads"

# Permissions Hardening
find "${REMOTE_ROOT}" -maxdepth 2 -not -path "*/storage/*" -not -path "*/.maintenance" -type d \
    | xargs chmod 755 2>/dev/null || true
find "${REMOTE_ROOT}" -maxdepth 2 -not -path "*/storage/*" -type f \
    | xargs chmod 644 2>/dev/null || true
[ -f "${REMOTE_ROOT}/.env" ] && chmod 600 "${REMOTE_ROOT}/.env" 2>/dev/null || true
chmod -R 775 "${REMOTE_ROOT}/storage" 2>/dev/null || true
chmod -R 775 "${REMOTE_ROOT}/bootstrap/log" 2>/dev/null || true
chmod 775 "${REMOTE_ROOT}/bootstrap/cache" 2>/dev/null || true

# Invalidate template and file caches
rm -rf "${REMOTE_ROOT}/storage/framework/views/"* 2>/dev/null || true
rm -f "${REMOTE_ROOT}/bootstrap/cache/"*.bladec 2>/dev/null || true
rm -rf "${REMOTE_ROOT}/storage/framework/cache/"* 2>/dev/null || true
echo "🗑️  Cleared compiled view and framework cache."

# Touch entry point to bump mtime
touch "${REMOTE_ROOT}/index.php"

# Reset OPcache via CLI if available
OPCACHE_RESULT=\$(/usr/local/bin/php -r "echo function_exists('opcache_reset') ? (opcache_reset() ? 'opcache_reset:ok' : 'opcache_reset:failed') : 'opcache_reset:unavailable';" 2>/dev/null || echo "opcache_reset:cli-error")
echo "🔄 OPcache reset result: \${OPCACHE_RESULT}"

# Deactivate Maintenance Mode
rm -f "${REMOTE_ROOT}/.maintenance"
echo "✅ Maintenance mode: OFF"
EOF

################################################################################
# 10. LIVE HEALTH CHECK & AUDIT LOG
################################################################################

echo -e "\n🩺 [8/8] Conducting Live Health Check on ${HEALTH_URL}..."

echo "⏳ Waiting ${HEALTH_WAIT}s for application to warm up..."
sleep "$HEALTH_WAIT"
HTTP_CODE=$(curl -s -o /dev/null --max-time 30 -w "%{http_code}" "$HEALTH_URL" || echo "000")

if [[ "$HTTP_CODE" =~ ^(200|301|302)$ ]]; then
    echo "✅ Health check passed: HTTP ${HTTP_CODE} OK"
else
    echo "🛑 CRITICAL: Health check failed with HTTP ${HTTP_CODE}!"
    exit 1
fi

DURATION=$(( $(date +%s) - START_TIME ))
OUTCOME="SUCCESS"

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
LOG_FILE="${REMOTE_ROOT}/deploy.log"
REMOTE_TS=\$(date -u +%Y-%m-%dT%H:%M:%SZ)
echo "{\"timestamp\":\"\${REMOTE_TS}\",\"app\":\"${APP_NAME}\",\"deployer\":\"${USER_NAME}\",\"branch\":\"${BRANCH}\",\"commit\":\"${COMMIT:0:8}\",\"duration_s\":${DURATION},\"outcome\":\"${OUTCOME}\"}" >> "\${LOG_FILE}"
chmod 644 "\${LOG_FILE}" 2>/dev/null || true
echo "📋 Deployment audit entry written to \${LOG_FILE}"
EOF

echo ""
echo "======================================================================"
echo " 🏆 DIRECT ATOMIC DEPLOYMENT COMPLETED SUCCESSFULLY"
echo " App     : ${APP_NAME}"
echo " Target  : ${REMOTE_ROOT}"
echo " Commit  : ${COMMIT:0:8} [${BRANCH}]"
echo " Duration: ${DURATION}s"
echo "======================================================================"
