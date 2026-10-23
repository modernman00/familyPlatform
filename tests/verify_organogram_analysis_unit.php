<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$organogram = new \App\controller\members\Organogram();
$reflection = new \ReflectionClass($organogram);
$method = $reflection->getMethod('analyzeMissingNodes');

echo "=================================================================\n";
echo "🧪 UNIT TEST: Organogram analyzeMissingNodes() Invariant Guard\n";
echo "=================================================================\n\n";

// Test 1: Empty tree (0 parents, 0 relatives)
$res1 = $method->invoke($organogram, [], ['nodes' => []]);
assert($res1['completeness_score'] === 0, "Score should be 0% for empty tree");
assert(in_array('Parents', $res1['missing_labels'], true), "Parents should be missing in empty tree");
assert($res1['banner_title'] === 'Build your family tree!', "Title should prompt building tree");
assert(strpos($res1['banner_subtitle'], 'Parents added') === false, "Subtitle MUST NOT say 'Parents added' when parents are missing!");
echo "✅ Test 1 Passed: Empty tree correctly suggests adding Parents (Score: {$res1['completeness_score']}%, Title: '{$res1['banner_title']}').\n";

// Test 2: Only Father added
$res2 = $method->invoke($organogram, ['father' => ['fullName' => 'John Doe']], ['nodes' => [['generation_level' => -1]]]);
assert($res2['completeness_score'] === 20, "Score should be 20% with father only");
assert(in_array('Mother', $res2['missing_labels'], true), "Mother should be missing");
assert($res2['banner_title'] === 'Complete your parents’ lineage!', "Title should prompt completing parents");
assert(strpos($res2['banner_subtitle'], 'Father added! Now add your Mother') !== false, "Subtitle should prompt adding mother");
echo "✅ Test 2 Passed: Father only correctly prompts adding Mother (Score: {$res2['completeness_score']}%, Subtitle: '{$res2['banner_subtitle']}').\n";

// Test 3: Both parents added, Grandparents missing
$res3 = $method->invoke($organogram, [
    'father' => ['fullName' => 'John Doe'],
    'mother' => ['fullName' => 'Jane Doe']
], ['nodes' => [
    ['generation_level' => -1],
    ['generation_level' => -1]
]]);
assert($res3['completeness_score'] === 40, "Score should be 40% with both parents");
assert(in_array('Grandparents', $res3['missing_labels'], true), "Grandparents should be missing");
assert($res3['banner_title'] === 'Preserve your ancestral heritage!', "Title should prompt grandparents");
assert(strpos($res3['banner_subtitle'], 'Parents connected!') !== false, "Subtitle should say parents connected");
echo "✅ Test 3 Passed: Both parents present correctly unlocks Grandparents (Score: {$res3['completeness_score']}%, Title: '{$res3['banner_title']}').\n";

echo "\n🎉 ALL ORGANOGRAM LOGIC & INVARIANT TESTS PASSED WITH 100% SUCCESS!\n";
