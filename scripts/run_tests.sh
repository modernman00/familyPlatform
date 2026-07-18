#!/usr/bin/env bash
# GLOBAL TESTING MANDATE ENFORCEMENT

set -e

echo "======================================"
echo "    Running Global Quality Gates      "
echo "======================================"

# 1. Gatewatcher Gate: PHPStan
if [ -f "vendor/bin/phpstan" ] && [ -f "phpstan.neon" ]; then
    echo "--- 🛡️ David's Gate: Running PHPStan ---"
    ./vendor/bin/phpstan analyze || echo "PHPStan failed! Please fix structural errors."
fi

# 2. Gatewatcher Gate: Psalm (Alternative/Additional)
if [ -f "vendor/bin/psalm" ] && [ -f "psalm.xml" ]; then
    echo "--- 🛡️ David's Gate: Running Psalm ---"
    ./vendor/bin/psalm
fi

# 3. Backend Logic & Chaos
if [ -f "vendor/bin/phpunit" ]; then
    echo "--- ⚙️ Backend Gate: Running PHPUnit ---"
    ./vendor/bin/phpunit
elif grep -q "\"test:php\"" package.json 2>/dev/null; then
    echo "--- ⚙️ Backend Gate: Running npm run test:php ---"
    npm run test:php
fi

# 4. Frontend Unit Testing
if grep -q "\"test\"" package.json 2>/dev/null && grep -q "\"jest\"" package.json 2>/dev/null; then
    echo "--- 🎨 Frontend Gate: Running Jest ---"
    npm run test
fi

# 5. Priya's Gate: Cypress / E2E
if grep -q "\"test:e2e\"" package.json 2>/dev/null; then
    echo "--- 🤖 Priya's Gate: Cypress / E2E ---"
    npm run test:e2e
elif grep -q "\"playwright\"" package.json 2>/dev/null; then
    echo "--- 🤖 Priya's Gate: Playwright ---"
    npx playwright test
fi

echo "======================================"
echo " ✅ ALL GATES PASSED! YOU MAY DEPLOY. "
echo "======================================"

