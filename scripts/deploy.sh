#!/bin/bash

# ==============================================================================
# DEPLOYMENT SCRIPT (Local -> Shared Hosting via Rsync)
# Architect: James
# ==============================================================================
# This script builds the application locally and syncs the compiled output
# to the Namecheap shared server, avoiding server-side memory exhaustion.
# ==============================================================================

set -e # Exit immediately if a command exits with a non-zero status.

# --- Configuration ---
# TODO: Update these variables with actual Namecheap SSH details
SSH_USER="bestiias"
SSH_HOST="premium145.web-hosting.com"
SSH_PORT="21098" # Default Namecheap SSH port is often 21098
REMOTE_DIR="/home/bestiias/familyplatform"
# ---------------------

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
        read -p "Do you want to force the deployment anyway? (y/n): " force_deploy < /dev/tty
        if [[ "$force_deploy" != "y" ]]; then
            echo "🛑 Deployment aborted."
            exit 1
        fi
    fi
else
    echo "⏭️  No test script found, skipping."
fi

echo "🔄 3/3: Syncing files to Namecheap ($SSH_HOST)..."

rsync -avz --delete \
    -e "ssh -p $SSH_PORT" \
    --exclude='.*' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='scripts' \
    --exclude='scratch' \
    --exclude='*.pem' \
    --exclude='*.sql' \
    --exclude='*.sqlite' \
    --exclude='*.key' \
    --exclude='*.crt' \
    --exclude='*.cert' \
    --exclude='*.md' \
    --exclude='*.sh' \
    --exclude='playwright-report' \
    --exclude='test-results' \
    --exclude='*test*.php' \
    --exclude='debug.txt' \
    --exclude='*output.txt' \
    --exclude='phpstan.neon' \
    --exclude='phpstan-baseline.neon' \
    ./ ${SSH_USER}@${SSH_HOST}:${REMOTE_DIR}

echo "✅ Deployment Complete! The live server is updated."

echo "=================================================="
echo "🐙 4/4: GitHub Sync"
echo "=================================================="
read -p "Do you want to securely commit and push your safe files to GitHub? (y/n): " push_github < /dev/tty
if [[ "$push_github" == "y" ]]; then
    read -p "Enter a commit message: " commit_msg < /dev/tty
    
    echo "Staging safe files (unsafe files blocked by .gitignore)..."
    git add .
    git commit -m "$commit_msg"
    
    echo "Pushing to GitHub..."
    git push origin HEAD
    echo "✅ Successfully synced to GitHub!"
else
    echo "⏭️  Skipping GitHub push."
fi
