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
REMOTE_ROOT="${DEPLOY_REMOTE_DIR:-${DEPLOY_REMOTE_ROOT:-/home/bestiias/myfamilyplatform}}"
RELEASES_DIR="${REMOTE_ROOT}/releases"
CURRENT_LINK="${REMOTE_ROOT}/current"
SHARED_DIR="${REMOTE_ROOT}/shared"
BACKUP_DIR="${REMOTE_ROOT}/backups"
LIVE_HEALTH_URL="${DEPLOY_HEALTH_URL:-https://myfamilyplatform.com}" # Update with live domain
HEALTH_URL="${DEPLOY_HEALTH_URL:-https://myfamilyplatform.com}" # Update with live domain
KEEP_RELEASES=5

START_TIME=$(date +%s)
RELEASE_ID=$(date +"%Y%m%d%H%M%S")

COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
USER_NAME=$(whoami)

# Sandboxed isolated build directory
SANDBOX=$(mktemp -d /tmp/${APP_NAME}_release_XXXXXX)

cleanup() {
    rm -rf "$SANDBOX"
}
trap cleanup EXIT INT TERM

echo "======================================================================"
echo " 🚀 [$APP_NAME] INITIATING ATOMIC ZERO-DOWNTIME DEPLOYMENT"
echo " Target : ${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}"
echo " Release: ${RELEASE_ID} | Commit: ${COMMIT:0:8} [${BRANCH}]"
echo "======================================================================"

################################################################################
# 2. PRE-FLIGHT INTEGRITY & QUALITY GATES
################################################################################

echo -e "\n🔍 [1/8] Verifying Git & Workspace Integrity..."

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
        git add .
        git commit -m "$COMMIT_MSG"
        COMMIT=$(git rev-parse HEAD)
        echo "✅ Changes committed successfully (${COMMIT:0:8})."
    else
        echo "🛑 FATAL: Enterprise policy forbids deploying uncommitted code. Aborting."
        exit 1
    fi
fi

# Guard against accidental secret leaks in the tree
UNTRACKED_SECRETS=$(git status --porcelain | grep -E "^\?\?.*(\.env|\.pem|\.sql|\.key|\.crt|\.backup)" || true)
if [ -n "$UNTRACKED_SECRETS" ]; then
    echo "🛑 SEC-OPS ALERT: Potential untracked secret files detected in workspace:"
    echo "$UNTRACKED_SECRETS"
    exit 1
fi

echo "🔍 [2/8] Running PHP Syntax Linting (Full Backend)..."
find app bootstrap -type f -name "*.php" | while read -r php_file; do
    php -l "$php_file" >/dev/null || {
        echo "🛑 FATAL: Syntax error in $php_file"
        exit 1
    }
done
echo "✅ PHP syntax clean."

if [ -f "phpstan.neon" ]; then
    echo "🔍 [3/8] Running PHPStan Static Analysis..."
    vendor/bin/phpstan analyse --no-progress --quiet || {
        echo "🛑 FATAL: PHPStan static analysis failed."
        exit 1
    }
    echo "✅ PHPStan passed."
fi

if [ -f "phpunit.xml" ]; then
    echo "🧪 [4/8] Running PHPUnit Test Suite..."
    vendor/bin/phpunit --no-coverage || {
        echo "🛑 FATAL: Automated tests failed."
        exit 1
    }
    echo "✅ Tests passed."
fi

if command -v semgrep >/dev/null 2>&1; then
    echo "🛡️  Running Semgrep SecOps Scan..."
    semgrep scan --config="p/phpcs-security-audit" --config="p/owasp-top-ten" --error --quiet app/ index.php 2>/dev/null || {
        echo "🛑 FATAL: Semgrep identified structural security vulnerabilities."
        exit 1
    }
    echo "✅ Security scan clean."
fi

################################################################################
# 3. ASSET COMPILATION & SANDBOX ASSEMBLY
################################################################################

echo -e "\n📦 [5/8] Compiling Production Frontend Assets & Sandbox..."

if [ -f "package.json" ]; then
    if grep -q '"prod":' package.json; then
        npm run prod --silent
    elif grep -q '"build":' package.json; then
        npm run build --silent
    fi
fi

# Copy only production application code into the clean isolated sandbox
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
copy_item manifest.json
copy_item offline.html
copy_item .htaccess

# ------------------------------------------------------------------------------
# STRICT EXCLUSION: Strip all local cache, compiled Blade templates, and logs
# ------------------------------------------------------------------------------
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

# Optimize Autoloader inside Sandbox (Offline, No-Dev)
echo "⚡ Optimizing composer autoloader (--no-dev, --optimize)..."
(cd "$SANDBOX" && COMPOSER_DISABLE_NETWORK=1 composer dump-autoload --optimize --no-dev --no-interaction --quiet 2>/dev/null || true)

################################################################################
# 4. REMOTE DIRECTORY PREPARATION & SHARED STORAGE INITIALIZATION
################################################################################

echo -e "\n🔌 [6/8] Preparing Remote Structure on ${SSH_HOST}..."

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
mkdir -p "${RELEASES_DIR}/${RELEASE_ID}"
mkdir -p "${SHARED_DIR}/storage/logs"
mkdir -p "${SHARED_DIR}/storage/framework/cache"
mkdir -p "${SHARED_DIR}/storage/framework/sessions"
mkdir -p "${SHARED_DIR}/storage/framework/views"
mkdir -p "${SHARED_DIR}/bootstrap/log"
mkdir -p "${SHARED_DIR}/public/uploads"
mkdir -p "${BACKUP_DIR}"

# Seed shared .env from current release if it exists and shared .env does not
if [ ! -f "${SHARED_DIR}/.env" ] && [ -f "${CURRENT_LINK}/.env" ]; then
    cp "${CURRENT_LINK}/.env" "${SHARED_DIR}/.env"
    chmod 600 "${SHARED_DIR}/.env"
fi
EOF

################################################################################
# 5. RSYNC RELEASE TO REMOTE
################################################################################

echo "🚀 Uploading Release ${RELEASE_ID}..."

rsync -az --delete \
    -e "ssh -p ${SSH_PORT}" \
    --exclude='.git*' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='cypress*' \
    --exclude='storage' \
    --exclude='bootstrap/cache/*.bladec' \
    --exclude='bootstrap/cache/*' \
    --exclude='bootstrap/log/*.log' \
    --exclude='bootstrap/log/*' \
    "$SANDBOX/" \
    "${SSH_USER}@${SSH_HOST}:${RELEASES_DIR}/${RELEASE_ID}/"

################################################################################
# 6. ACTIVATE ATOMIC SYMLINK & BIND PERSISTENT STORAGE
################################################################################

echo -e "\n🔄 [7/8] Activating Release via Inode Symlink..."

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
set -e

# 1. Symlink Persistent Shared Storage into New Release
rm -rf "${RELEASES_DIR}/${RELEASE_ID}/storage"
ln -sfn "${SHARED_DIR}/storage" "${RELEASES_DIR}/${RELEASE_ID}/storage"

# 2. Symlink Persistent Bootstrap Log directory
rm -rf "${RELEASES_DIR}/${RELEASE_ID}/bootstrap/log"
ln -sfn "${SHARED_DIR}/bootstrap/log" "${RELEASES_DIR}/${RELEASE_ID}/bootstrap/log"

# 3. Ensure bootstrap/cache is fresh and writable
mkdir -p "${RELEASES_DIR}/${RELEASE_ID}/bootstrap/cache"
chmod 775 "${RELEASES_DIR}/${RELEASE_ID}/bootstrap/cache"

# 4. Symlink persistent .env
if [ -f "${SHARED_DIR}/.env" ]; then
    ln -sfn "${SHARED_DIR}/.env" "${RELEASES_DIR}/${RELEASE_ID}/.env"
fi

# 5. Symlink persistent user uploads
if [ -d "${SHARED_DIR}/public/uploads" ]; then
    rm -rf "${RELEASES_DIR}/${RELEASE_ID}/public/uploads"
    ln -sfn "${SHARED_DIR}/public/uploads" "${RELEASES_DIR}/${RELEASE_ID}/public/uploads"
fi

# 6. Hardened File & Folder Permissions
find "${RELEASES_DIR}/${RELEASE_ID}" -type d -exec chmod 755 {} + 2>/dev/null || true
find "${RELEASES_DIR}/${RELEASE_ID}" -type f -exec chmod 644 {} + 2>/dev/null || true
chmod -R 775 "${SHARED_DIR}/storage" 2>/dev/null || true
chmod -R 775 "${SHARED_DIR}/bootstrap/log" 2>/dev/null || true

# 7. Microsecond Atomic Symlink Switch
ln -sfn "${RELEASES_DIR}/${RELEASE_ID}" "${CURRENT_LINK}"

# 8. Invalidate OPcache via File Timestamp Touch
touch "${RELEASES_DIR}/${RELEASE_ID}/index.php"
rm -f "${RELEASES_DIR}/${RELEASE_ID}/bootstrap/cache/"*.bladec 2>/dev/null || true
EOF

################################################################################
# 7. LIVE HEALTH CHECK & AUTOMATED INSTANT ROLLBACK
################################################################################

echo -e "\n🩺 [8/8] Conducting Live Health Check on ${HEALTH_URL}..."

sleep 3
HTTP_CODE=$(curl -k -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" || echo "000")

if [[ "$HTTP_CODE" =~ ^(200|301|302)$ ]]; then
    echo "✅ Health check passed: HTTP ${HTTP_CODE} OK"
    
    # Prune old releases (Keep last 5)
    echo "🧹 Pruning old releases (retention: last ${KEEP_RELEASES})..."
    ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
cd "${RELEASES_DIR}"
ls -1t | tail -n +$((KEEP_RELEASES + 1)) | xargs -I {} rm -rf "{}" 2>/dev/null || true
EOF
else
    echo "🛑 CRITICAL: Health check failed with HTTP ${HTTP_CODE}!"
    echo "⚠️ Initiating automated rollback..."

    PREVIOUS_RELEASE=$(ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "ls -1t ${RELEASES_DIR} | sed -n '2p'")

    if [ -n "$PREVIOUS_RELEASE" ]; then
        echo "⏪ Reverting symlink to previous release: ${PREVIOUS_RELEASE}..."
        ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "ln -sfn ${RELEASES_DIR}/${PREVIOUS_RELEASE} ${CURRENT_LINK} && touch ${RELEASES_DIR}/${PREVIOUS_RELEASE}/index.php"
        echo "✅ Rollback complete. Site restored to ${PREVIOUS_RELEASE}."
    else
        echo "🛑 FATAL: No previous release found to rollback to."
    fi
    exit 1
fi

################################################################################
# 8. POST-DEPLOYMENT GITHUB SYNC
################################################################################

if [ "$BRANCH" = "master" ] || [ "$BRANCH" = "main" ]; then
    echo "🐙 Pushing ${BRANCH} to GitHub origin..."
    git push origin "$BRANCH" || echo "⚠️ GitHub push skipped/failed."
fi

DURATION=$(( $(date +%s) - START_TIME ))
echo ""
echo "======================================================================"
echo " 🏆 ATOMIC DEPLOYMENT COMPLETED SUCCESSFULLY"
echo " Commit  : ${COMMIT:0:8} [${BRANCH}]"
echo " Release : ${RELEASE_ID}"
echo " Duration: ${DURATION}s"
echo "======================================================================"
