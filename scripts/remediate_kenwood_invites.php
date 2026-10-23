<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

use App\service\FamilyCodeApprovalService;
use App\services\InviteTokenService;
use Src\Db;

$pdo = Db::connect2();

try {
    echo "=== STEP 1: INVALIDATING ROGUE INVITE TOKENS ===\n";
    $tokenIds = [20575, 20576, 20582, 20583, 20650, 20651, 20652, 20653];
    $placeholders = implode(',', array_fill(0, count($tokenIds), '?'));
    $stmtInvalidate = $pdo->prepare(
        "UPDATE invite_tokens 
         SET use_count = max_uses, 
             expires_at = DATE_SUB(NOW(), INTERVAL 1 DAY),
             used_at = NOW() 
         WHERE id IN ($placeholders)"
    );
    $stmtInvalidate->execute($tokenIds);
    echo "Invalidated " . $stmtInvalidate->rowCount() . " tokens with husband family code OLAO60446.\n";

    echo "\n=== STEP 2: VERIFYING KENWOOD MAIDEN FAMILY CODE ===\n";
    $stmtCheck = $pdo->prepare("SELECT otherFamCode FROM otherFamily WHERE id = ?");
    $stmtCheck->execute(['318422WALLY']);
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    $maidenCode = $row['otherFamCode'] ?? '';

    if (empty($maidenCode)) {
        $approvalService = new FamilyCodeApprovalService($pdo);
        $maidenCode = $approvalService->generateUniqueSoloFamilyCode('KENWOOD');
        $stmtOtherFam = $pdo->prepare("UPDATE otherFamily SET otherFamCode = ? WHERE id = ?");
        $stmtOtherFam->execute([$maidenCode, '318422WALLY']);
    }
    echo "Active Maiden Family Code: {$maidenCode}\n";

    echo "\n=== STEP 3: MIGRATING PARENT AND SIBLING NODES TO MAIDEN TREE ===\n";
    $nodeIds = [25605, 25606, 26682]; // Yomi (Father), JIBS (Mother), London (Sibling)
    $nodePlaceholders = implode(',', array_fill(0, count($nodeIds), '?'));
    
    // Move nodes to maiden family code
    $stmtMoveNodes = $pdo->prepare("UPDATE family_nodes SET family_code = ? WHERE id IN ($nodePlaceholders)");
    $stmtMoveNodes->execute(array_merge([$maidenCode], $nodeIds));
    echo "Migrated nodes to family code: {$maidenCode}\n";

    // Also update family_unions associated with these nodes if any
    $stmtMoveUnions = $pdo->prepare(
        "UPDATE family_unions 
         SET family_code = ? 
         WHERE (partner_1_id = 25605 AND partner_2_id = 25606) 
            OR (partner_1_id = 25606 AND partner_2_id = 25605)"
    );
    $stmtMoveUnions->execute([$maidenCode]);
    echo "Migrated parent union(s) to family code: {$maidenCode}\n";

    echo "\n=== STEP 4: FRESH INVITE LINKS UNDER MAIDEN CODE ===\n";
    $baseUrl = rtrim((string)(getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');

    // Fetch existing valid onboarding token for KEN852 if any, or create one
    $stmtFindToken = $pdo->prepare("SELECT token FROM invite_tokens WHERE family_code = ? AND type = 'onboarding' AND expires_at > NOW() AND use_count < max_uses LIMIT 1");
    $stmtFindToken->execute([$maidenCode]);
    $existing = $stmtFindToken->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $kenwoodOnboardingToken = $existing['token'];
    } else {
        $kenwoodOnboardingToken = InviteTokenService::create($maidenCode, [
            'invited_by' => '318422WALLY',
        ], 'onboarding', 2592000, 50);
    }
    $kenwoodOnboardingUrl = InviteTokenService::getRegisterUrl($kenwoodOnboardingToken, $baseUrl);
    echo "Kenwood Family Onboarding Invite Link:\n";
    echo "  Token: {$kenwoodOnboardingToken}\n";
    echo "  URL:   {$kenwoodOnboardingUrl}\n";
    echo "  Code:  {$maidenCode}\n";

    echo "\n>>> REMEDIATION CONFIRMED SUCCESSFULLY! <<<\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
