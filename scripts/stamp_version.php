<?php
/**
 * Dynamic Version & Release Notes Generator
 * Stamping script executed prior to deployment to ensure public/version.json
 * reflects the latest version tag, commit hash, and release notes.
 */

$rootDir = dirname(__DIR__);
$versionFile = $rootDir . '/public/version.json';

// 1. Determine Version Tag
$gitTag = trim(shell_exec('git describe --tags --abbrev=0 2>/dev/null') ?: '');
$gitCommitCount = trim(shell_exec('git rev-list --count HEAD 2>/dev/null') ?: '1');
$commitHash = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?: 'dev');

if (empty($gitTag)) {
    $versionTag = 'v2.4.' . $gitCommitCount;
} else {
    $versionTag = $gitTag;
}

// 2. Extract recent git log messages for release notes
$gitLogRaw = shell_exec('git log -n 12 --pretty=format:"%s" 2>/dev/null');
$notes = [];

if ($gitLogRaw) {
    $lines = explode("\n", trim($gitLogRaw));
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip automated commits or merge messages
        if (empty($line) || 
            preg_match('/^(Merge|chore|wip|bump|refactor\(deps\))/i', $line)) {
            continue;
        }
        
        // Clean up commit message prefix if any (e.g., "feat: ", "fix: ")
        $cleanLine = preg_replace('/^(feat|fix|docs|style|refactor|perf|test):\s*/i', '', $line);
        $cleanLine = ucfirst(trim($cleanLine));

        // Avoid duplicate notes
        if (!in_array($cleanLine, $notes, true)) {
            $notes[] = $cleanLine;
        }
        
        if (count($notes) >= 3) {
            break;
        }
    }
}

// Fallbacks if git notes are minimal
if (empty($notes)) {
    $notes = [
        "Performance enhancements and stability fixes.",
        "Instant check-in and guest list optimizations.",
        "Enhanced offline reliability and page loading speeds."
    ];
}

$title = $notes[0] ?? "Performance & Feature Improvements";

$versionData = [
    'version' => $versionTag,
    'title' => $title,
    'notes' => array_values($notes),
    'released_at' => date('Y-m-d'),
    'commit' => $commitHash
];

file_put_contents(
    $versionFile, 
    json_encode($versionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);

echo "✅ Stamped public/version.json: {$versionTag} ({$commitHash}) - '{$title}'\n";
