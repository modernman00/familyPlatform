<?php
declare(strict_types=1);

/**
 * Portfolio-Wide Pre-Flight & In-Flight Global Mutex & CPU Throttling Synchroniser
 * Commissioned by: Wally (CEO) | Executed by: Olutobi (Head of TAT) & Declan O'Connor (SRE)
 *
 * Enforces across all 8 portfolio applications:
 *   1. Global Cross-Chat Mutex Lock (/tmp/portfolio_gate_mutex.lock) with stale-PID recovery
 *   2. OS CPU Scheduler De-Prioritisation (renice -n 10 $$)
 *   3. Smart Incremental Git-Diff + Untracked PHP Syntax Linting (Gate 1)
 *   4. Runaway Headless Browser E2E Guard (RUN_BROWSER_E2E=1) in run_tests.sh / run-all-security-checks.sh
 */

$sitesDir = '/Users/waleolaogun/Sites';
$apps = [
    'bcp',
    'execmindapp',
    'familyPlatform',
    'iaccountapp',
    'idecideapp',
    'loaneasyfinance',
    'partyplatform',
    'scoretenant',
];

function buildMutexBlock(string $app, string $gateType): string
{
    $label = $gateType === 'preflight' ? 'pre-flight' : 'in-flight';
    return <<<BASH
ROOT_DIR="\$(cd "\$(dirname "\${BASH_SOURCE[0]}")/.." && pwd)"
cd "\$ROOT_DIR"

# ------------------------------------------------------------------------------
# ⚡ CPU Priority Throttling & Global Cross-Chat Mutex Queue (Olutobi & SRE Gate)
# Prevents concurrent chat sessions from stampeding CPU/RAM simultaneously.
# ------------------------------------------------------------------------------
renice -n 10 \$\$ >/dev/null 2>&1 || true

LOCK_DIR="/tmp/portfolio_gate_mutex.lock"
LOCK_ACQUIRED=0
WAIT_ANNOUNCED=0
WAIT_START=\$(date +%s)

cleanup_gate_lock() {
    if [ "\$LOCK_ACQUIRED" -eq 1 ] && [ -f "\$LOCK_DIR/pid" ]; then
        if [ "\$(cat "\$LOCK_DIR/pid" 2>/dev/null)" = "\$\$" ]; then
            rm -rf "\$LOCK_DIR" 2>/dev/null || true
        fi
    fi
}
trap cleanup_gate_lock EXIT INT TERM

while true; do
    if mkdir "\$LOCK_DIR" 2>/dev/null; then
        echo "\$\$" > "\$LOCK_DIR/pid"
        echo "{$app}:{$gateType}" > "\$LOCK_DIR/owner"
        date +%s > "\$LOCK_DIR/timestamp"
        LOCK_ACQUIRED=1
        if [ "\$WAIT_ANNOUNCED" -eq 1 ]; then
            echo -e "\${GREEN}⚡ [GLOBAL QUEUE] Slot acquired after \$(( \$(date +%s) - WAIT_START ))s. Proceeding with {$label}...\${NC}"
        fi
        break
    fi

    HOLDER_PID="\$(cat "\$LOCK_DIR/pid" 2>/dev/null || echo "")"
    HOLDER_OWNER="\$(cat "\$LOCK_DIR/owner" 2>/dev/null || echo "another workspace")"
    LOCK_TS="\$(cat "\$LOCK_DIR/timestamp" 2>/dev/null || echo "0")"
    NOW_TS=\$(date +%s)

    if [ -n "\$HOLDER_PID" ] && ! kill -0 "\$HOLDER_PID" 2>/dev/null; then
        rm -rf "\$LOCK_DIR" 2>/dev/null || true
        continue
    elif [ "\$LOCK_TS" -gt 0 ] && [ \$(( NOW_TS - LOCK_TS )) -gt 180 ]; then
        rm -rf "\$LOCK_DIR" 2>/dev/null || true
        continue
    fi

    if [ "\$WAIT_ANNOUNCED" -eq 0 ]; then
        echo -e "\${YELLOW}⏳ [GLOBAL QUEUE] Another gate is active in \${HOLDER_OWNER} (PID \${HOLDER_PID:-unknown}). Queuing to keep your Mac fast...\${NC}"
        WAIT_ANNOUNCED=1
    fi
    sleep 1
done

echo -e "\${BOLD}\${BLUE}================================================================\${NC}"
BASH;
}

$updatedCount = 0;

foreach ($apps as $app) {
    $appDir = "{$sitesDir}/{$app}";
    if (!is_dir($appDir)) {
        continue;
    }

    foreach (['preflight', 'inflight'] as $gateType) {
        $scriptPath = "{$appDir}/scripts/{$gateType}.sh";
        if (!is_file($scriptPath)) {
            continue;
        }

        $content = (string)file_get_contents($scriptPath);
        $modified = false;

        // 1. Inject Global Mutex + renice if not already present
        if (strpos($content, 'portfolio_gate_mutex.lock') === false) {
            $targetHeader = <<<'BASH'
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo -e "${BOLD}${BLUE}================================================================${NC}"
BASH;
            if (strpos($content, $targetHeader) !== false) {
                $content = str_replace($targetHeader, buildMutexBlock($app, $gateType), $content);
                $modified = true;
            }
        }

        // 2. Upgrade Gate 1 php -l loop to Smart Incremental Git-Diff + Untracked Scan
        if (strpos($content, 'FULL_LINT') === false) {
            $pattern = '/SYNTAX_ERRORS=0\s*\nwhile IFS= read -r file; do.*?\ndone < <\((find [^\n]+)\)/s';
            if (preg_match($pattern, $content, $matches)) {
                $findCmd = $matches[1];
                $replacement = <<<BASH
SYNTAX_ERRORS=0
LINT_COUNT=0
if [ "\${FULL_LINT:-0}" != "1" ] && git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    while IFS= read -r file; do
        [ -z "\$file" ] && continue
        [ ! -f "\$file" ] && continue
        LINT_COUNT=\$((LINT_COUNT + 1))
        if ! php -l "\$file" > /dev/null 2>&1; then
            echo -e "\${RED}  ❌ Syntax Error in: \$file\${NC}"
            php -l "\$file"
            SYNTAX_ERRORS=\$((SYNTAX_ERRORS + 1))
        fi
    done < <({ git diff --name-only HEAD 2>/dev/null; git diff --name-only --cached 2>/dev/null; git ls-files --others --exclude-standard 2>/dev/null; } | grep -E '\.php\$' | sort -u)
else
    while IFS= read -r file; do
        [ ! -f "\$file" ] && continue
        LINT_COUNT=\$((LINT_COUNT + 1))
        if ! php -l "\$file" > /dev/null 2>&1; then
            echo -e "\${RED}  ❌ Syntax Error in: \$file\${NC}"
            php -l "\$file"
            SYNTAX_ERRORS=\$((SYNTAX_ERRORS + 1))
        fi
    done < <({$findCmd})
fi
BASH;
                $content = (string)preg_replace($pattern, $replacement, $content, 1);
                $modified = true;
            }
        }

        if ($modified) {
            file_put_contents($scriptPath, $content);
            chmod($scriptPath, 0755);
            echo "✅ Updated {$app}/scripts/{$gateType}.sh\n";
            $updatedCount++;
        } else {
            echo "⚡ Already up-to-date: {$app}/scripts/{$gateType}.sh\n";
        }
    }

    // 3. Guard run_tests.sh against unrequested headless Cypress/Playwright launches
    $runTestsPath = "{$appDir}/scripts/run_tests.sh";
    if (is_file($runTestsPath)) {
        $rtContent = (string)file_get_contents($runTestsPath);
        $rtModified = false;

        if (strpos($rtContent, 'renice -n 10') === false) {
            $rtContent = (string)preg_replace('/^(set -e[^\n]*\n)/m', "$1renice -n 10 \$\$ >/dev/null 2>&1 || true\n", $rtContent, 1);
            $rtModified = true;
        }

        if (strpos($rtContent, 'RUN_BROWSER_E2E') === false && strpos($rtContent, 'npm run test:e2e') !== false) {
            $cypressPattern = '/if grep -q "\\\\"test:e2e\\\\"" package\.json.*?fi\n/s';
            if (preg_match($cypressPattern, $rtContent, $m)) {
                $wrapped = "if [ \"\${RUN_BROWSER_E2E:-0}\" = \"1\" ]; then\n" . $m[0] . "fi\n";
                $rtContent = str_replace($m[0], $wrapped, $rtContent);
                $rtModified = true;
            }
        }

        if ($rtModified) {
            file_put_contents($runTestsPath, $rtContent);
            chmod($runTestsPath, 0755);
            echo "✅ Updated {$app}/scripts/run_tests.sh\n";
            $updatedCount++;
        }
    }
}

echo "\n🎯 Portfolio Gate Mutex & CPU Throttling Sync Complete ({$updatedCount} scripts updated).\n";
