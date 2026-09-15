#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

export PATH="/opt/homebrew/bin:/opt/homebrew/opt/rsync/bin:/usr/local/bin:$PATH"
cd "$(dirname "$0")/.."

DEPLOY_APP_NAME="FamilyPlatform"
DEPLOY_SSH_USER="bestiias"
DEPLOY_SSH_HOST="premium145.web-hosting.com"
DEPLOY_SSH_PORT="21098"
DEPLOY_REMOTE_DIR="/home/bestiias/myfamilyplatform"
DEPLOY_HEALTH_URL="https://myfamilyplatform.com"
DEPLOY_HEALTH_WAIT=3

APP_NAME="${DEPLOY_APP_NAME}"
SSH_USER="${DEPLOY_SSH_USER}"
SSH_HOST="${DEPLOY_SSH_HOST}"
SSH_PORT="${DEPLOY_SSH_PORT}"
REMOTE_ROOT="${DEPLOY_REMOTE_DIR}"
HEALTH_URL="${DEPLOY_HEALTH_URL}"
HEALTH_WAIT="${DEPLOY_HEALTH_WAIT}"

START_TIME=$(date +%s)
RELEASE_ID=$(date +"%Y%m%d%H%M%S")
COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
USER_NAME=$(whoami)

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
echo " 🚀 [$APP_NAME] DEPLOYING WITH EXISTING ASSETS"
echo " Target  : ${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}"
echo " Commit  : ${COMMIT:0:8} [${BRANCH}]"
echo " Deployer: ${USER_NAME}"
echo "======================================================================"

echo -e "\n🔌 [1/5] Verifying SSH connectivity..."
if ! ssh -p "$SSH_PORT" -o ConnectTimeout=10 -o BatchMode=yes -o StrictHostKeyChecking=accept-new \
         "${SSH_USER}@${SSH_HOST}" exit 2>/tmp/_deploy_ssh_check.txt; then
    echo "🛑 FATAL: Cannot establish SSH connection"
    rm -f /tmp/_deploy_ssh_check.txt
    exit 1
fi
rm -f /tmp/_deploy_ssh_check.txt
echo "✅ SSH connection verified."

echo "🔒 Acquiring remote deploy lock..."
if ! ssh -p "$SSH_PORT" -o ConnectTimeout=10 -o BatchMode=yes "${SSH_USER}@${SSH_HOST}" \
    "mkdir '${DEPLOY_LOCK_PATH}' 2>/dev/null" ; then
    echo "🛑 FATAL: Deployment already in progress."
    exit 1
fi
DEPLOY_LOCK_HELD=true
echo "✅ Deploy lock acquired."

SANDBOX=$(mktemp -d "/tmp/${APP_NAME}_deploy_XXXXXX")

echo -e "\n📦 [2/5] Preparing existing assets for deployment..."
for item in app bootstrap public resources vendor composer.json composer.lock index.php offline.html .htaccess; do
    if [ -e "$item" ]; then
        cp -R "$item" "$SANDBOX/" 2>/dev/null || true
    fi
done
if [ -f "manifest.json" ]; then
    cp manifest.json "$SANDBOX/" 2>/dev/null || true
fi

# Clean local cache before rsync
rm -rf "$SANDBOX/bootstrap/cache/"*
rm -rf "$SANDBOX/bootstrap/log/"*
touch "$SANDBOX/bootstrap/cache/.gitkeep"
touch "$SANDBOX/bootstrap/log/.gitkeep"

echo "✅ Assets prepared."

echo -e "\n🚀 [3/5] Synchronizing Application..."
ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "touch '${REMOTE_ROOT}/.maintenance' 2>/dev/null || true"

rsync -azL \
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
    --exclude='public/img/*' \
    --exclude='public/img/**' \
    --exclude='public/images/*' \
    --exclude='public/images/**' \
    --exclude='resources/images/*' \
    --exclude='resources/images/**' \
    --exclude='resources/videos/*' \
    --exclude='resources/videos/**' \
    --exclude='storage/uploads/*' \
    --exclude='storage/uploads/**' \
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

echo -e "\n⚡ [3.5/5] Verifying autoloader on remote..."
ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" REMOTE_ROOT="$REMOTE_ROOT" "bash -s" << 'EOF'
cd "$REMOTE_ROOT" || exit 1
if [ -d "vendor" ]; then
    echo "✅ Vendor directory exists on remote."
else
    echo "⚠️ Vendor directory not found — autoloader may not work."
fi
EOF

echo -e "\n🧹 [4/5] Enforcing Permissions & Clearing Caches..."
ssh -p "$SSH_PORT" "${SSH_USER}@${SSH_HOST}" "bash -s" << EOF
set -e
mkdir -p "${REMOTE_ROOT}/storage/logs"
mkdir -p "${REMOTE_ROOT}/storage/framework/cache"
mkdir -p "${REMOTE_ROOT}/storage/framework/sessions"
mkdir -p "${REMOTE_ROOT}/storage/framework/views"
mkdir -p "${REMOTE_ROOT}/bootstrap/cache"
mkdir -p "${REMOTE_ROOT}/bootstrap/log"
mkdir -p "${REMOTE_ROOT}/public/uploads"

find "${REMOTE_ROOT}" -maxdepth 2 -not -path "*/storage/*" -not -path "*/.maintenance" -type d | xargs chmod 755 2>/dev/null || true
find "${REMOTE_ROOT}" -maxdepth 2 -not -path "*/storage/*" -type f | xargs chmod 644 2>/dev/null || true
[ -f "${REMOTE_ROOT}/.env" ] && chmod 600 "${REMOTE_ROOT}/.env" 2>/dev/null || true
chmod -R 775 "${REMOTE_ROOT}/storage" 2>/dev/null || true
chmod -R 775 "${REMOTE_ROOT}/bootstrap/log" 2>/dev/null || true
chmod 775 "${REMOTE_ROOT}/bootstrap/cache" 2>/dev/null || true

rm -rf "${REMOTE_ROOT}/storage/framework/views/"* 2>/dev/null || true
rm -f "${REMOTE_ROOT}/bootstrap/cache/"*.bladec 2>/dev/null || true
rm -rf "${REMOTE_ROOT}/storage/framework/cache/"* 2>/dev/null || true
echo "🗑️  Cleared compiled view and framework cache."

touch "${REMOTE_ROOT}/index.php"
rm -f "${REMOTE_ROOT}/.maintenance"
echo "✅ Maintenance mode: OFF"
EOF

echo -e "\n🩺 [5/5] Conducting Live Health Check..."
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

echo ""
echo "======================================================================"
echo " 🏆 DEPLOYMENT WITH EXISTING ASSETS COMPLETED SUCCESSFULLY"
echo " App     : ${APP_NAME}"
echo " Target  : ${REMOTE_ROOT}"
echo " Commit  : ${COMMIT:0:8} [${BRANCH}]"
echo " Duration: ${DURATION}s"
echo "======================================================================"
