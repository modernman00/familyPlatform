#!/usr/bin/env node
/**
 * Blade Inline Script AST Linter & Security Validator
 *
 * Scans all Blade templates under resources/views/ for inline <script> blocks,
 * strips Blade directives/interpolations safely, and parses the JavaScript AST.
 *
 * Exits with 0 if all inline scripts are syntactically valid.
 * Exits with 1 and prints the file, line number, and syntax error if invalid.
 */

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const VIEWS_DIR = path.resolve(__dirname, '../resources/views');

function getBladeFiles(dir) {
    let results = [];
    if (!fs.existsSync(dir)) return results;

    const list = fs.readdirSync(dir);
    list.forEach(file => {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat && stat.isDirectory()) {
            results = results.concat(getBladeFiles(fullPath));
        } else if (file.endsWith('.blade.php')) {
            results.push(fullPath);
        }
    });
    return results;
}

function sanitizeBladeForJs(code) {
    return code
        // Quoted Blade echos: "{{ ... }}" or '{{ ... }}' -> safe single string
        .replace(/["']\{\{[\s\S]*?\}\}["']/g, '"__BLADE_STR__"')
        .replace(/["']\{\!\![\s\S]*?\!\!\}["']/g, '"__BLADE_STR__"')
        // Raw Blade echo {!! ... !!} -> safe literal
        .replace(/\{\!\![\s\S]*?\!\!\}/g, '"__BLADE_RAW__"')
        // Unquoted Blade echo {{ ... }} -> safe literal
        .replace(/\{\{[\s\S]*?\}\}/g, '"__BLADE_VAL__"')
        // @json(...) with balanced parentheses (supports up to 3 nested levels)
        .replace(/@json\((?:[^()]+|\((?:[^()]+|\([^()]*\))*\))*\)/g, '{}')
        // @push / @endpush / @section / @endsection / @include
        .replace(/@(push|endpush|section|endsection|extends|stack|include)\b[^\n]*/g, '/* blade layout directive */')
        // Blade control directives -> comments
        .replace(/@(if|elseif|else|endif|foreach|endforeach|forelse|empty|endforelse|while|endwhile|switch|case|break|default|endswitch|php|endphp|auth|guest|endauth|endguest|isset|endisset|empty|endempty)\b[^\n]*/g, '/* blade directive */');
}

function lintBladeFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const scriptRegex = /<script\b([^>]*)>([\s\S]*?)<\/script>/gi;
    let match;
    let errors = [];
    let scriptCount = 0;

    while ((match = scriptRegex.exec(content)) !== null) {
        const attrs = match[1] || '';
        const body = match[2];

        // Skip scripts with src="..." (external scripts)
        if (/\bsrc\s*=/i.test(attrs)) continue;

        // Skip non-javascript scripts (e.g. application/ld+json or template types)
        const typeMatch = attrs.match(/\btype\s*=\s*["']?([^"'\s>]+)/i);
        if (typeMatch) {
            const typeVal = typeMatch[1].toLowerCase();
            if (typeVal !== 'text/javascript' && typeVal !== 'module' && typeVal !== 'application/javascript') {
                continue;
            }
        }

        scriptCount++;
        const lineOffset = content.substring(0, match.index).split('\n').length;
        const sanitized = sanitizeBladeForJs(body);

        try {
            // Validate using Node's vm.Script
            new vm.Script(sanitized, { filename: `${filePath}:inline-script-${scriptCount}` });
        } catch (err) {
            // Calculate approximate line number in file
            let errorLine = lineOffset;
            if (err.stack) {
                const stackMatch = err.stack.match(/:(\d+)(?::\d+)?/);
                if (stackMatch) {
                    errorLine = lineOffset + parseInt(stackMatch[1], 10) - 1;
                }
            }
            errors.push({
                file: path.relative(process.cwd(), filePath),
                line: errorLine,
                message: err.message
            });
        }
    }

    return { file: filePath, scriptCount, errors };
}

function run() {
    const files = getBladeFiles(VIEWS_DIR);
    let totalScripts = 0;
    let totalErrors = 0;
    let failedFiles = [];

    files.forEach(f => {
        const result = lintBladeFile(f);
        totalScripts += result.scriptCount;
        if (result.errors.length > 0) {
            totalErrors += result.errors.length;
            failedFiles.push(result);
        }
    });

    console.log(`[Blade Script Linter] Scanned ${files.length} Blade files (${totalScripts} inline <script> blocks found).`);

    if (totalErrors === 0) {
        console.log(`\x1b[32m[OK] Zero syntax errors detected across all ${totalScripts} inline Blade scripts.\x1b[0m`);
        process.exit(0);
    } else {
        console.error(`\x1b[31m[FAIL] Found ${totalErrors} syntax error(s) in Blade inline scripts:\x1b[0m\n`);
        failedFiles.forEach(f => {
            f.errors.forEach(e => {
                console.error(`  - \x1b[33m${e.file}:${e.line}\x1b[0m -> ${e.message}`);
            });
        });
        process.exit(1);
    }
}

run();
