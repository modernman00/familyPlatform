<?php
declare(strict_types=1);

/**
 * Find unions where one partner is the other's own parent (these wreck the tree layout).
 *
 *   php scripts/repair_parent_child_unions.php [--family=CODE]          report only
 *   php scripts/repair_parent_child_unions.php [--family=CODE] --apply  delete childless offenders
 *
 * Unions that still have children are reported but never deleted. Back up
 * family_unions first (`mysqldump family family_unions family_node_children`).
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
$sql = "SELECT u.id, u.family_code, u.partner_1_id, u.partner_2_id,
               (SELECT COUNT(*) FROM family_node_children k WHERE k.union_id = u.id) AS kids
        FROM family_unions u
        WHERE u.partner_1_id <> u.partner_2_id
          AND EXISTS (
              SELECT 1 FROM family_node_children c JOIN family_unions pu ON pu.id = c.union_id
              WHERE (c.child_id = u.partner_1_id AND u.partner_2_id IN (pu.partner_1_id, pu.partner_2_id))
                 OR (c.child_id = u.partner_2_id AND u.partner_1_id IN (pu.partner_1_id, pu.partner_2_id))
          )" . ($family !== null ? ' AND u.family_code = ?' : '');
$stmt = $db->prepare($sql);
$stmt->execute($family !== null ? [$family] : []);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$del = $db->prepare('DELETE FROM family_unions WHERE id = ? AND NOT EXISTS (SELECT 1 FROM family_node_children WHERE union_id = ?)');
echo "union_id\tfamily_code\tp1\tp2\tchildren\taction\n";
foreach ($rows as $r) {
    $deletable = (int)$r['kids'] === 0;
    if ($apply && $deletable) {
        $del->execute([$r['id'], $r['id']]);
    }
    echo implode("\t", [$r['id'], $r['family_code'], $r['partner_1_id'], $r['partner_2_id'], $r['kids'], $deletable ? ($apply ? 'deleted' : 'deletable') : 'manual (has children)']) . "\n";
}
echo sprintf("\n%d parent-child unions found%s\n", count($rows), $apply ? ' (applied)' : ' (dry run)');
