#!/usr/bin/env bash

set -e

echo "======================================"
echo "    Running Local Quality Checks      "
echo "======================================"

if [ -f "vendor/bin/phpstan" ]; then
    echo "--- Running PHPStan ---"
    ./vendor/bin/phpstan analyze
fi

if [ -f "vendor/bin/psalm" ]; then
    echo "--- Running Psalm ---"
    ./vendor/bin/psalm
fi

if [ -f "vendor/bin/phpunit" ]; then
    echo "--- Running PHPUnit ---"
    ./vendor/bin/phpunit
fi

echo "======================================"
echo " ✅ All checks passed! "
echo "======================================"
