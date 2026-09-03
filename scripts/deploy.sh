#!/bin/bash

# ==============================================================================
# DEPLOYMENT SCRIPT (Local -> Shared Hosting via Rsync)
# Architect: James
# ==============================================================================
# This script builds the application locally and syncs the compiled output
# to the Namecheap shared server, avoiding server-side memory exhaustion.
# ==============================================================================

set -e # Exit immediately if a command exits with a non-zero status.

# Always operate from the repo root (composer/npm/rsync-filter paths assume it).
cd "$(dirname "$0")/.."

# --- Configuration ---
# TODO: Update these variables with actual Namecheap SSH details
SSH_USER="bestiias"
SSH_HOST="premium145.web-hosting.com"
SSH_PORT="21098" # Default Namecheap SSH port is often 21098
REMOTE_DIR="/home/bestiias/myfamilyplatform"
# ---------------------

# Captured now so the audit log records the commit actually being synced,
# not one created later by the GitHub-sync step below.
DEPLOY_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
DEPLOY_USER=$(whoami)

echo "🚀 Starting Deployment Pipeline..."

echo "📦 1/3: Installing & optimizing PHP dependencies (Local)..."
composer install --no-dev --optimize-autoloader

echo "🎨 2/3: Compiling frontend assets (Local)..."
if [ -f "package.json" ]; then
    npm install
    if grep -q '"prod":' package.json; then
        npm run prod
    elif grep -q '"build":' package.json; then
        npm run build
    else
        echo "⏭️  No prod or build script found, skipping JS compilation."
    fi
else
    echo "⏭️  No package.json found, skipping JS compilation."
fi

echo "🧪 Running automated tests (if any)..."
TEST_SCRIPT=""
if [ -f "scripts/run_tests.sh" ]; then
    TEST_SCRIPT="scripts/run_tests.sh"
elif [ -f "run_tests.sh" ]; then
    TEST_SCRIPT="run_tests.sh"
elif [ -f "run_all_tests.sh" ]; then
    TEST_SCRIPT="run_all_tests.sh"
fi

if [ -n "$TEST_SCRIPT" ]; then
    # We temporarily disable set -e so the test failure doesn't kill the script immediately
    set +e
    bash "$TEST_SCRIPT"
    TEST_RESULT=$?
    set -e
    
    if [ $TEST_RESULT -ne 0 ]; then
        echo -e "\n⚠️  WARNING: Some tests failed."
        read -p "Do you want to force the deployment anyway? (y/n): " force_deploy
        if [[ "$force_deploy" != "y" ]]; then
            echo "🛑 Deployment aborted."
            exit 1
        fi
    fi
else
    echo "⏭️  No test script found, skipping."
fi

echo "🔁 Stamping service worker version..."
SW_FILE="service-worker.js"
SW_BUILD="g${DEPLOY_COMMIT:0:12}-$(date +%s)"
if [ -f "$SW_FILE" ]; then
    # Safety net: restore the file if the script dies before the explicit revert below.
    trap 'git checkout -- "$SW_FILE" 2>/dev/null || true' EXIT
    # Portable in-place edit (BSD/macOS + GNU sed): rewrite the whole SW_VERSION line.
    sed -i.bak -E "s|^const SW_VERSION = .*|const SW_VERSION = '${SW_BUILD}'; // deployed $(date -u +%Y-%m-%dT%H:%M:%SZ)|" "$SW_FILE"
    rm -f "${SW_FILE}.bak"
    grep -m1 'SW_VERSION' "$SW_FILE"
fi

echo "🔄 3/3: Syncing files to Namecheap ($SSH_HOST)..."

# macOS ships openrsync, whose filter support is unreliable. Require GNU rsync 3.x
# (brew install rsync) so the .rsync-filter allowlist behaves predictably.
RSYNC_BIN="$(command -v rsync)"
for cand in /opt/homebrew/bin/rsync /usr/local/bin/rsync; do
    [ -x "$cand" ] && RSYNC_BIN="$cand" && break
done
if ! "$RSYNC_BIN" --version 2>/dev/null | head -1 | grep -q "version 3"; then
    echo "🛑 GNU rsync 3.x required for the deploy allowlist. Run: brew install rsync"
    exit 1
fi

# ALLOWLIST via .rsync-filter in the repo root: only paths matched by a '+' rule
# are sent; everything else (dev scripts, cypress/, migrations/, tests/, …) stays
# local. --delete-excluded prunes anything stale already on the server; P(rotect)
# rules in .rsync-filter keep server-only runtime data (uploads, storage, .env).
"$RSYNC_BIN" -avz --delete --delete-excluded \
    -e "ssh -p $SSH_PORT" \
    -f 'merge .rsync-filter' \
    ./ ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}

# Restore the committed SW_VERSION line so the working tree / GitHub-sync stays clean.
git checkout -- "$SW_FILE" 2>/dev/null || true
trap - EXIT

echo "✅ Deployment Complete! The live server is updated (SW: ${SW_BUILD})."

echo "📝 Recording deployment audit log (ISO 27001 A.12.4)..."
php scripts/audit_deploy.php "$DEPLOY_COMMIT" "$DEPLOY_USER" || echo "⚠️  Audit log failed to record (non-fatal)."

echo "=================================================="
echo "🐙 4/4: GitHub Sync"
echo "=================================================="
read -p "Do you want to securely commit and push your safe files to GitHub? (y/n): " push_github
if [[ "$push_github" == "y" ]]; then
    read -p "Enter a commit message: " commit_msg
    
    echo "Staging safe files (unsafe files blocked by .gitignore)..."
    git add .
    git commit -m "$commit_msg"
    
    echo "Pushing to GitHub..."
    git push origin HEAD
    echo "✅ Successfully synced to GitHub!"
else
    echo "⏭️  Skipping GitHub push."
fi
