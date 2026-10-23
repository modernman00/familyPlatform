<?php
declare(strict_types=1);

/**
 * Audit family_nodes whose gender is not an explicit Male/Female (these render with neutral kinship titles).
 *
 *   php scripts/audit_unknown_gender_nodes.php [--family=CODE]          report only (default, read-only)
 *   php scripts/audit_unknown_gender_nodes.php [--family=CODE] --apply  copy personal.gender onto registered nodes
 *
 * --apply only touches nodes whose linked account (personal.gender) holds an explicit Male/Female value.
 * Everything else is listed for a human to set via the tree's "Gender not set" prompt. Take a
 * `mysqldump family family_nodes` backup before using --apply.
 */

use Src\Db;

if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}

require_once __DIR__ . '/../app/config/init.php';

$apply = in_array('--apply', $argv, true);
$family = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--family=')) {
        $family = substr($arg, 9);
    }
}

$db = Db::connect2();
$sql = "SELECT fn.id, fn.family_code, fn.first_name, fn.last_name, fn.gender, fn.user_id, p.gender AS account_gender
        FROM family_nodes fn
        LEFT JOIN personal p ON p.id = fn.user_id
        WHERE (fn.gender IS NULL OR fn.gender NOT IN ('Male', 'Female'))
          AND COALESCE(fn.is_placeholder, 0) = 0" . ($family !== null ? ' AND fn.family_code = ?' : '') . "
        ORDER BY fn.family_code, fn.id";
$stmt = $db->prepare($sql);
$stmt->execute($family !== null ? [$family] : []);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fixable = 0;
$upd = $db->prepare("UPDATE family_nodes SET gender = ? WHERE id = ? AND (gender IS NULL OR gender NOT IN ('Male', 'Female'))");
echo "id\tfamily_code\tname\tnode_gender\tuser_id\taccount_gender\taction\n";
foreach ($rows as $r) {
    $acct = ucfirst(strtolower(trim((string)($r['account_gender'] ?? ''))));
    $canFix = $r['user_id'] !== null && in_array($acct, ['Male', 'Female'], true);
    $action = $canFix ? ($apply ? "set {$acct}" : "fixable ({$acct})") : 'manual';
    if ($canFix) {
        $fixable++;
        if ($apply) {
            $upd->execute([$acct, $r['id']]);
        }
    }
    echo implode("\t", [$r['id'], $r['family_code'], trim($r['first_name'] . ' ' . $r['last_name']), $r['gender'] ?? 'NULL', $r['user_id'] ?? '-', $r['account_gender'] ?? '-', $action]) . "\n";
}
echo sprintf("\n%d unknown-gender nodes, %d auto-fixable from account%s\n", count($rows), $fixable, $apply ? ' (applied)' : ' (dry run)');
