#!/usr/bin/env bash
# ==============================================================================
# 🚀 WORLD-CLASS ATOMIC DEPLOYMENT PIPELINE WITH AUTO-ROLLBACK  v2.0
# ==============================================================================
# Architecture : Zero-Downtime Atomic Symlinks, Automated Rollback,
#                Shared Persistence Isolation, Service Worker Stamping, ISO 27001
# Framework    : Custom PHP 8.2 (AltoRouter + Twig/BladeOne + modernman00/shared-lib)
#                NO Laravel — zero Artisan dependency
# Target System: Namecheap cPanel / Cloud VPS (iAccountApp & Portfolio)
# Maintenance  : File-based .maintenance flag (index.php checks __DIR__/.maintenance)
# Cron jobs    : HTTP-triggered via /api/cron/* endpoints (not CLI-based)
# ==============================================================================
# USAGE:
#   source .deploy.env && bash scripts/deploy.sh
#
# REQUIRED ENV VARS (no silent defaults — script aborts if unset):
#   DEPLOY_APP_NAME   — Human-readable app name, e.g. "iAccountApp"
#   DEPLOY_SSH_USER   — SSH username
#   DEPLOY_SSH_HOST   — SSH hostname
#   DEPLOY_SSH_PORT   — SSH port (typically 21098 for Namecheap)
#   DEPLOY_REMOTE_DIR — Absolute remote path, e.g. /home/bestiias/iaccountapp
#   DEPLOY_HEALTH_URL — Full health check URL, e.g. https://www.iaccountapp.com
#
# OPTIONAL ENV VARS:
#   DEPLOY_KEEP_RELEASES  — Number of releases to retain (default: 5)
#   DEPLOY_HEALTH_WAIT    — Seconds to wait before health check (default: 3)
#
# TEMPLATE: Copy .deploy.env.example → .deploy.env (gitignored) per-app.
# ==============================================================================

set -euo pipefail
IFS=$'\n\t'

# Always operate from the repository root
cd "$(dirname "$0")/.."

################################################################################
# 1. MANDATORY ENVIRONMENT VARIABLE VALIDATION
#    No silent defaults. If a variable is unset the script aborts immediately
#    before touching anything. This prevents deploying the wrong app to the
#    wrong server when env is not properly sourced.
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
# 2. CONFIGURATION (all from validated env vars — no hardcoded production values)
################################################################################

APP_NAME="${DEPLOY_APP_NAME}"
SSH_USER="${DEPLOY_SSH_USER}"
SSH_HOST="${DEPLOY_SSH_HOST}"
SSH_PORT="${DEPLOY_SSH_PORT}"

REMOTE_ROOT="${DEPLOY_REMOTE_DIR}"
RELEASES_DIR="${REMOTE_ROOT}/releases"
CURRENT_LINK="${REMOTE_ROOT}/current"
SHARED_DIR="${REMOTE_ROOT}/shared"
BACKUP_DIR="${REMOTE_ROOT}/backups"

HEALTH_URL="${DEPLOY_HEALTH_URL}"
KEEP_RELEASES="${DEPLOY_KEEP_RELEASES:-5}"
HEALTH_WAIT="${DEPLOY_HEALTH_WAIT:-3}"

START_TIME=$(date +%s)
RELEASE_ID=$(date +"%Y%m%d%H%M%S")

COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
USER_NAME=$(whoami)

# Sandboxed isolated build directory (cleaned on exit)
SANDBOX=$(mktemp -d "/tmp/${APP_NAME}_release_XXXXXX")

# Remote deploy lock path (released in cleanup)
DEPLOY_LOCK_PATH="${REMOTE_ROOT}/deploy.lock"
DEPLOY_LOCK_HELD=false

cleanup() {
    rm -rf "$SANDBOX"
    # Release remote deploy lock if we acquired it
    if [ "$DEPLOY_LOCK_HELD" = "true" ]; then
        ssh -p "$SSH_PORT" -o ConnectTimeout=10 -o BatchMode=yes \
            "${SSH_USER}@${SSH_HOST}" "rmdir '${DEPLOY_LOCK_PATH}' 2>/dev/null || true" 2>/dev/null || true
        echo "🔓 Deploy lock released."
    fi
}
trap cleanup EXIT INT TERM

echo "======================================================================"
echo " 🚀 [$APP_NAME] INITIATING ATOMIC ZERO-DOWNTIME DEPLOYMENT"
echo " Target  : ${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}"
echo " Release : ${RELEASE_ID} | Commit: ${COMMIT:0:8} [${BRANCH}]"
echo " Deployer: ${USER_NAME}"
echo "======================================================================"

################################################################################
# 3. PRE-FLIGHT: SSH CONNECTIVITY CHECK
#    Verify SSH access BEFORE any local work begins. Fails fast — saves wasted
#    sandbox-build time if credentials or network are wrong.
################################################################################

echo -e "\n🔌 [0/9] Verifying SSH connectivity to ${SSH_HOST}..."
if ! ssh -p "$SSH_PORT" \
         -o ConnectTimeout=10 \
         -o BatchMode=yes \
         -o StrictHostKeyChecking=accept-new \
         -o LogLevel=INFO \
         "${SSH_USER}@${SSH_HOST}" exit 2>/tmp/_deploy_ssh_check.txt; then
    echo "🛑 FATAL: Cannot establish SSH connection to ${SSH_USER}@${SSH_HOST}:${SSH_PORT}"
    echo "   Check your SSH key, host, port, and network connectivity."
    rm -f /tmp/_deploy_ssh_check.txt
    exit 1
fi
# Log host fingerprint for audit trail (Rajan: TOFU transparency)
SSH_FINGERPRINT=$(ssh-keyscan -p "$SSH_PORT" -H "$SSH_HOST" 2>/dev/null | ssh-keygen -lf - 2>/dev/null | head -1 || echo "fingerprint-unavailable")
echo "🔑 Host fingerprint: ${SSH_FINGERPRINT}"
rm -f /tmp/_deploy_ssh_check.txt
echo "✅ SSH connection verified."

# Acquire remote deploy lock (atomic mkdir — POSIX-safe)
echo "🔒 Acquiring deploy lock..."
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

echo -e "\n🔍 [1/9] Verifying Git & Workspace Integrity..."

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
        # Stage only already-tracked modified files — never untracked files
        # (Rajan R-01: git add . risks staging secrets/artifacts not in .gitignore)
        git add -u
        git commit -m "$COMMIT_MSG"
        COMMIT=$(git rev-parse HEAD)
        echo "✅ Changes committed successfully (${COMMIT:0:8})."
    else
        echo "🛑 FATAL: Enterprise policy forbids deploying uncommitted code. Aborting."
        exit 1
    fi
fi

# Guard against accidental secret leaks — both untracked AND staged files
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
    echo "   Unstage with: git reset HEAD <file>"
    exit 1
fi

echo "✅ Git workspace clean. No secret leaks detected."

################################################################################
# 5. QUALITY GATES (PHP Lint → PHPStan → PHPUnit → Semgrep)
################################################################################

echo -e "\n🔍 [2/9] Running PHP Syntax Linting (Full Backend — parallelised)..."
# Parallelised across 4 cores. Redirect stdout only (success lines) — stderr
# (error output) passes through so the developer sees which file failed.
# (Rajan R-08: > /dev/null was hiding lint error messages)
find app bootstrap -type f -name "*.php" \
    | xargs -P4 -I{} php -l {} 1>/dev/null \
    || { echo "🛑 FATAL: PHP syntax error detected. See error above."; exit 1; }
echo "✅ PHP syntax clean."

if [ -f "phpstan.neon" ]; then
    echo -e "\n🔍 [3/9] Running PHPStan Static Analysis..."
    vendor/bin/phpstan analyse --no-progress --quiet || {
        echo "🛑 FATAL: PHPStan static analysis failed."
        exit 1
    }
    echo "✅ PHPStan passed."
fi

if [ -f "phpunit.xml" ]; then
    echo -e "\n🧪 [4/9] Running PHPUnit Test Suite..."
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
# 6. GITHUB PUSH (before deployment — source control must be ahead of live)
#    Enterprise rule: source control is the source of truth. Push first, deploy second.
################################################################################

if [ "$BRANCH" = "master" ] || [ "$BRANCH" = "main" ]; then
    echo -e "\n🐙 [5/9] Pushing ${BRANCH} to GitHub origin (before deployment)..."
    git push origin "$BRANCH" || {
        echo "🛑 FATAL: GitHub push failed. Aborting deployment to keep source control aligned."
        exit 1
    }
    echo "✅ GitHub push successful."
fi

################################################################################
# 7. ASSET COMPILATION & SANDBOX ASSEMBLY
################################################################################

echo -e "\n📦 [6/9] Compiling Production Frontend Assets & Assembling Sandbox..."

if [ -f "package.json" ]; then
    if grep -q '"prod":' package.json; then
        npm run prod --silent
    elif grep -q '"build":' package.json; then
        npm run build --silent
    fi
fi

# Copy only production application code into a clean isolated sandbox
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

# manifest.json integrity guard — a missing manifest silently breaks PWA installability
if [ -f "manifest.json" ]; then
    copy_item manifest.json
else
    echo "⚠️  WARNING: manifest.json not found in project root. PWA installability will be broken."
fi
[ -f "$SANDBOX/manifest.json" ] || echo "⚠️  WARNING: manifest.json was not copied into sandbox successfully."

# ------------------------------------------------------------------------------
# STRICT EXCLUSION: Strip all local cache, compiled views, and logs
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

# Check root-level service-worker.js (some apps place it at root, not /public)
SW_ROOT_FILE="$SANDBOX/service-worker.js"
if [ -f "service-worker.js" ]; then
    copy_item service-worker.js
    SW_BUILD="v${RELEASE_ID}-${COMMIT:0:8}"
    sed -i.bak -E "s|^const SW_VERSION = .*|const SW_VERSION = '${SW_BUILD}';|" "$SW_ROOT_FILE"
    rm -f "${SW_ROOT_FILE}.bak"
    echo "🔁 Stamped Root Service Worker: ${SW_BUILD}"
fi

# Vendor strategy: rsync local vendor/ (no remote deploy key needed), then build the
# optimized production autoloader *inside the sandbox* before upload. This removes the
# hard dependency on Composer existing on the shared host. dump-autoload only (re)writes
# pure-PHP autoloader files (autoload_classmap/static/etc.) — it never compiles
# platform-specific binaries — so generating it locally is safe across PHP versions.
if command -v composer >/dev/null 2>&1; then
    echo "⚡ Optimizing Composer autoloader in sandbox (local)..."
    if ! (cd "$SANDBOX" && composer dump-autoload --optimize --no-dev --no-interaction --quiet); then
        echo "⚠️  WARNING: local composer dump-autoload failed; uploading existing vendor/autoload.php as-is."
    fi
else
    echo "⚠️  WARNING: composer not found locally; uploading existing vendor/autoload.php as-is."
fi
echo "✅ Sandbox assembled."

################################################################################
# 8. REMOTE DIRECTORY PREPARATION & SHARED STORAGE INITIALIZATION
################################################################################

echo -e "\n🔌 [7/9] Preparing Remote Structure on ${SSH_HOST}..."

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
set -e
mkdir -p "${RELEASES_DIR}/${RELEASE_ID}"
mkdir -p "${SHARED_DIR}/storage/logs"
mkdir -p "${SHARED_DIR}/storage/framework/cache"
mkdir -p "${SHARED_DIR}/storage/framework/sessions"
mkdir -p "${SHARED_DIR}/storage/framework/views"
mkdir -p "${SHARED_DIR}/bootstrap/log"
mkdir -p "${SHARED_DIR}/public/uploads"
mkdir -p "${BACKUP_DIR}"

# Seed shared .env from current release if shared .env does not yet exist
if [ ! -f "${SHARED_DIR}/.env" ] && [ -f "${CURRENT_LINK}/.env" ]; then
    cp "${CURRENT_LINK}/.env" "${SHARED_DIR}/.env"
    chmod 600 "${SHARED_DIR}/.env"
    echo "ℹ️  Seeded shared .env from current release."
fi
EOF

################################################################################
# 9. RSYNC RELEASE TO REMOTE
################################################################################

echo -e "\n🚀 Uploading Release ${RELEASE_ID}..."

rsync -az --delete \
    --timeout=120 \
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
    --exclude='scripts' \
    "$SANDBOX/" \
    "${SSH_USER}@${SSH_HOST}:${RELEASES_DIR}/${RELEASE_ID}/"

echo "✅ Rsync upload complete."

# Autoloader was already optimized locally in the sandbox and uploaded via rsync.
# No remote Composer binary is required — this removes the shared-host dependency.
echo "✅ Autoloader uploaded with release (no remote Composer required)."

################################################################################
# 10. ACTIVATE ATOMIC SYMLINK, BIND PERSISTENT STORAGE & WARM CACHES
################################################################################

echo -e "\n🔄 [8/9] Activating Release via Inode Symlink..."

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
set -e

# ── MAINTENANCE MODE ON (file-based — matches index.php .maintenance check) ──
# Guard: CURRENT_LINK may not exist on first deployment (Rajan R-05)
if [ -L "${CURRENT_LINK}" ]; then
    touch "${CURRENT_LINK}/.maintenance"
    echo "🔧 Maintenance mode: ON"
else
    echo "ℹ️  First deployment — no existing release to put into maintenance."
fi

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

# 6. Hardened Permissions — using xargs for batch efficiency (PERF-02 fix)
find "${RELEASES_DIR}/${RELEASE_ID}" -not -path "*/storage/*" -not -path "*/.maintenance" -type d \
    | xargs chmod 755 2>/dev/null || true
find "${RELEASES_DIR}/${RELEASE_ID}" -not -path "*/storage/*" -type f \
    | xargs chmod 644 2>/dev/null || true
chmod -R 775 "${SHARED_DIR}/storage" 2>/dev/null || true
chmod -R 775 "${SHARED_DIR}/bootstrap/log" 2>/dev/null || true

# 7. Invalidate framework view cache (compiled templates from previous release)
rm -rf "${SHARED_DIR}/storage/framework/views/"* 2>/dev/null || true
echo "🗑️  Cleared compiled view cache."

# 8. Invalidate framework file cache (stale serialized objects)
rm -rf "${SHARED_DIR}/storage/framework/cache/"* 2>/dev/null || true
echo "🗑️  Cleared framework file cache."

# 9. ⚡ Truly Atomic Symlink Switch via mv -T (single rename() syscall)
# ln -sfn on existing symlinks is a two-step unlink+symlink on some kernels.
# mv -T is a single rename() call — guaranteed atomic on all Linux filesystems.
# (Rajan R-04: ln -sfn not guaranteed atomic when replacing existing symlink)
ln -sfn "${RELEASES_DIR}/${RELEASE_ID}" "${CURRENT_LINK}_next"
mv -T "${CURRENT_LINK}_next" "${CURRENT_LINK}"
echo "⚡ Symlink atomically switched to release ${RELEASE_ID}"

# 10. MAINTENANCE MODE OFF on the new release
rm -f "${RELEASES_DIR}/${RELEASE_ID}/.maintenance"
echo "✅ Maintenance mode: OFF"

# 11. OPcache Invalidation — touch entry point to bump mtime
touch "${RELEASES_DIR}/${RELEASE_ID}/index.php"
# Hard OPcache reset via PHP CLI over SSH — no webroot exposure (Marcus: SEC hardening)
OPCACHE_RESULT=\$(/usr/local/bin/php -r "echo function_exists('opcache_reset') ? (opcache_reset() ? 'opcache_reset:ok' : 'opcache_reset:failed') : 'opcache_reset:unavailable';" 2>/dev/null || echo "opcache_reset:cli-error")
echo "🔄 OPcache reset result: \${OPCACHE_RESULT}"

# 12. Remove stale compiled assets
rm -f "${RELEASES_DIR}/${RELEASE_ID}/bootstrap/cache/"*.bladec 2>/dev/null || true
EOF

################################################################################
# 11. LIVE HEALTH CHECK & AUTOMATED INSTANT ROLLBACK WITH VERIFICATION
################################################################################

echo -e "\n🩺 [9/9] Conducting Live Health Check on ${HEALTH_URL}..."

# Configurable health check wait (Rajan R-10: DEPLOY_HEALTH_WAIT env var)
echo "⏳ Waiting ${HEALTH_WAIT}s for application to warm up..."
sleep "$HEALTH_WAIT"
HTTP_CODE=$(curl -s -o /dev/null --max-time 30 -w "%{http_code}" "$HEALTH_URL" || echo "000")

if [[ "$HTTP_CODE" =~ ^(200|301|302)$ ]]; then
    echo "✅ Health check passed: HTTP ${HTTP_CODE} OK"

    # Prune old releases (retain last N)
    echo "🧹 Pruning old releases (retention: last ${KEEP_RELEASES})..."
    ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
# Use find+sort on directory name (timestamp) not mtime — reliable ordering.
# (Rajan R-06: ls -1t sorts by mtime; a touched file skews pruning order)
find "${RELEASES_DIR}" -maxdepth 1 -mindepth 1 -type d -name '[0-9]*' -printf '%f\\n' \\
    | sort -rn | tail -n +$((KEEP_RELEASES + 1)) | while IFS= read -r old_release; do
    rm -rf "${RELEASES_DIR}/\${old_release}" 2>/dev/null || true
    echo "  🗑️  Removed old release: \${old_release}"
done
EOF

else
    echo "🛑 CRITICAL: Health check failed with HTTP ${HTTP_CODE}!"
    echo "⚠️  Initiating automated rollback..."

    PREVIOUS_RELEASE=$(ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" \
        "ls -1t '${RELEASES_DIR}' | grep -E '^[0-9]{14}$' | sed -n '2p'" || true)

    if [ -n "$PREVIOUS_RELEASE" ]; then
        # Sanitize: ensure it's a valid timestamp-format release ID (14 digits)
        if [[ "$PREVIOUS_RELEASE" =~ ^[0-9]{14}$ ]]; then
            echo "⏪ Reverting symlink to previous release: ${PREVIOUS_RELEASE}..."
            ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" \
                "ln -sfn '${RELEASES_DIR}/${PREVIOUS_RELEASE}' '${CURRENT_LINK}' && touch '${RELEASES_DIR}/${PREVIOUS_RELEASE}/index.php' && rm -f '${RELEASES_DIR}/${PREVIOUS_RELEASE}/.maintenance'"

            # Verify rollback actually restored the site
            sleep 2
            ROLLBACK_CODE=$(curl -s -o /dev/null --max-time 30 -w "%{http_code}" "$HEALTH_URL" || echo "000")
            if [[ "$ROLLBACK_CODE" =~ ^(200|301|302)$ ]]; then
                echo "✅ Rollback successful. Site restored to ${PREVIOUS_RELEASE} (HTTP ${ROLLBACK_CODE})."
            else
                echo "🛑 FATAL: Rollback also failed (HTTP ${ROLLBACK_CODE}). MANUAL INTERVENTION REQUIRED."
                echo "   Both current release (${RELEASE_ID}) and previous (${PREVIOUS_RELEASE}) are unhealthy."
            fi
        else
            echo "🛑 FATAL: Previous release ID '${PREVIOUS_RELEASE}' is not a valid release format. Skipping rollback."
        fi
    else
        echo "🛑 FATAL: No previous release found. Cannot rollback. MANUAL INTERVENTION REQUIRED."
    fi
    exit 1
fi

################################################################################
# 12. POST-DEPLOYMENT AUDIT LOG
#     Append-only structured log entry on the remote server for ISO 27001
#     compliance. Records: deployer, commit, branch, release, duration, outcome.
################################################################################

DURATION=$(( $(date +%s) - START_TIME ))
OUTCOME="SUCCESS"

ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
LOG_FILE="${SHARED_DIR}/deploy.log"
# Timestamp runs on the REMOTE server for reliable ISO 27001 audit accuracy.
# (Rajan R-07: local \$(date) expands on dev machine — clock drift skews log)
REMOTE_TS=\$(date -u +%Y-%m-%dT%H:%M:%SZ)
echo "{\"timestamp\":\"\${REMOTE_TS}\",\"app\":\"${APP_NAME}\",\"deployer\":\"${USER_NAME}\",\"branch\":\"${BRANCH}\",\"commit\":\"${COMMIT:0:8}\",\"release\":\"${RELEASE_ID}\",\"duration_s\":${DURATION},\"outcome\":\"${OUTCOME}\"}" >> "\${LOG_FILE}"
chmod 644 "\${LOG_FILE}" 2>/dev/null || true
echo "📋 Deployment audit entry written to \${LOG_FILE}"
EOF

echo ""
echo "======================================================================"
echo " 🏆 ATOMIC DEPLOYMENT COMPLETED SUCCESSFULLY"
echo " App     : ${APP_NAME}"
echo " Commit  : ${COMMIT:0:8} [${BRANCH}]"
echo " Release : ${RELEASE_ID}"
echo " Duration: ${DURATION}s"
echo "======================================================================"
