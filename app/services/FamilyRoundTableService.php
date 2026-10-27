<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use Src\Ai\HuggingFaceInferenceGateway;
use Src\Ai\ResilientAiChat;
use App\services\NotificationOrchestrator;
use PDO;

/**
 * Family RoundTable — Kinship Reconciliation & Family Council Engine
 *
 * Implements prestigious, collaborative dispute resolution:
 * - Can be initiated by a direct disputant (Party A) OR a concerned 3rd-party peacemaker/elder.
 * - Multi-party testimonies from Party A, Party B, and Named Eyewitnesses.
 * - Non-punitive, empathetic, firm, and reconciliatory council accords.
 * - Resilient multi-tier AI inference with deterministic fallback engine.
 */
class FamilyRoundTableService
{
    private HuggingFaceInferenceGateway $gateway;
    private ResilientAiChat $fallbackChat;

    public function __construct(
        ?HuggingFaceInferenceGateway $gateway = null,
        ?ResilientAiChat $fallbackChat = null
    ) {
        $this->gateway = $gateway ?? new HuggingFaceInferenceGateway();
        $this->fallbackChat = $fallbackChat ?? new ResilientAiChat();
    }

    /**
     * File a new dispute docket before the Family RoundTable.
     *
     * @param string $famCode Family Code
     * @param string $initiatorId User ID of person filing
     * @param bool $isThirdParty True if filed by a peacemaker/observer on behalf of 2 members in disagreement
     * @param string $partyAId First party in dispute
     * @param string $partyBId Second party in dispute
     * @param array<int, string> $witnessIds Optional list of witness User IDs
     * @param string $title Headline / topic of dispute
     * @param string $category Category (e.g. chores, communication, property, event)
     * @param string $grievance Detailed initial account
     * @return array{success: bool, dispute_id?: string, error?: string}
     */
    public function fileDispute(
        string $famCode,
        string $initiatorId,
        bool $isThirdParty,
        string $partyAId,
        string $partyBId,
        array $witnessIds,
        string $title,
        string $category,
        string $grievance
    ): array {
        $cleanTitle = trim(htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));
        $cleanGrievance = trim(htmlspecialchars($grievance, ENT_QUOTES, 'UTF-8'));
        $cleanCategory = trim(htmlspecialchars($category, ENT_QUOTES, 'UTF-8')) ?: 'general_harmony';

        if ($cleanTitle === '' || $cleanGrievance === '') {
            return ['success' => false, 'error' => 'A title and grievance statement are required.'];
        }

        if ($partyAId === $partyBId) {
            return ['success' => false, 'error' => 'A council hearing requires two distinct family members.'];
        }

        $pdo = Db::connect2();

        // Verify parties belong to the family
        $stmt = $pdo->prepare("SELECT id, firstName, lastName FROM personal WHERE id IN (?, ?) AND famCode = ?");
        $stmt->execute([$partyAId, $partyBId, $famCode]);
        $verifiedParties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($verifiedParties) < 2) {
            return ['success' => false, 'error' => 'Both parties must be registered members of your family code.'];
        }

        // Clean and filter valid witnesses
        $cleanWitnessIds = [];
        if (!empty($witnessIds)) {
            $placeholders = implode(',', array_fill(0, count($witnessIds), '?'));
            $params = array_merge($witnessIds, [$famCode]);
            $wStmt = $pdo->prepare(
                sprintf("SELECT id FROM personal WHERE id IN (%s) AND famCode = ?", $placeholders)
            );
            $wStmt->execute($params);
            $cleanWitnessIds = $wStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $cleanWitnessIds = array_values(array_diff($cleanWitnessIds, [$partyAId, $partyBId]));
        }

        $disputeId = 'rtb_' . bin2hex(random_bytes(12));
        $witnessJson = json_encode($cleanWitnessIds);

        // Insert dispute docket
        $ins = $pdo->prepare("
            INSERT INTO family_disputes 
            (id, famCode, initiator_id, is_third_party, party_a_id, party_b_id, witness_ids, title, category, grievance, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'awaiting_statements', NOW())
        ");
        $ins->execute([
            $disputeId,
            $famCode,
            $initiatorId,
            $isThirdParty ? 1 : 0,
            $partyAId,
            $partyBId,
            $witnessJson,
            $cleanTitle,
            $cleanCategory,
            $cleanGrievance,
        ]);

        // Insert initial statement
        $statementRole = $isThirdParty ? 'initiator' : 'party_a';
        $stmtIns = $pdo->prepare("
            INSERT INTO family_dispute_statements (id, dispute_id, user_id, role, statement, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmtIns->execute([
            'stm_' . bin2hex(random_bytes(10)),
            $disputeId,
            $initiatorId,
            $statementRole,
            $cleanGrievance,
        ]);

        // Fetch initiator name for notifications
        $initStmt = $pdo->prepare("SELECT firstName, lastName FROM personal WHERE id = ?");
        $initStmt->execute([$initiatorId]);
        $initUser = $initStmt->fetch(PDO::FETCH_ASSOC) ?: ['firstName' => 'A family member', 'lastName' => ''];
        $initiatorName = trim(($initUser['firstName'] ?? '') . ' ' . ($initUser['lastName'] ?? ''));

        // Dispatch notifications via NotificationOrchestrator
        $actionUrl = '/family/roundtable?dispute_id=' . $disputeId;

        if ($isThirdParty) {
            NotificationOrchestrator::dispatch(
                userId: $partyAId,
                category: 'Kinship',
                priority: 'high',
                title: '🛡️ Family RoundTable Council Convened',
                body: "{$initiatorName} has invited the Family RoundTable to mediate '{$cleanTitle}'. Tap to share your side.",
                actionUrl: $actionUrl,
                tag: 'family_roundtable_hearing',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );

            NotificationOrchestrator::dispatch(
                userId: $partyBId,
                category: 'Kinship',
                priority: 'high',
                title: '🛡️ Family RoundTable Council Convened',
                body: "{$initiatorName} has invited the Family RoundTable to mediate '{$cleanTitle}'. Tap to share your side.",
                actionUrl: $actionUrl,
                tag: 'family_roundtable_hearing',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );
        } else {
            NotificationOrchestrator::dispatch(
                userId: $partyBId,
                category: 'Kinship',
                priority: 'high',
                title: '🛡️ Family RoundTable Hearing Request',
                body: "{$initiatorName} has brought a matter before the Family RoundTable: '{$cleanTitle}'. Tap to submit your perspective.",
                actionUrl: $actionUrl,
                tag: 'family_roundtable_hearing',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );
        }

        // Notify witnesses
        foreach ($cleanWitnessIds as $wId) {
            NotificationOrchestrator::dispatch(
                userId: (string)$wId,
                category: 'Kinship',
                priority: 'normal',
                title: '🛡️ Eyewitness Insight Requested',
                body: "{$initiatorName} has requested your objective insight on '{$cleanTitle}' at the Family RoundTable.",
                actionUrl: $actionUrl,
                tag: 'family_roundtable_hearing',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );
        }

        return [
            'success' => true,
            'dispute_id' => $disputeId,
        ];
    }

    /**
     * Submit a statement or counter-argument for an ongoing council hearing.
     *
     * @param string $disputeId
     * @param string $userId
     * @param string $statement
     * @param string|null $famCode
     * @return array{success: bool, is_ready?: bool, error?: string}
     */
    public function submitStatement(string $disputeId, string $userId, string $statement, ?string $famCode = null): array
    {
        $cleanStatement = trim(htmlspecialchars($statement, ENT_QUOTES, 'UTF-8'));
        if ($cleanStatement === '') {
            return ['success' => false, 'error' => 'Your statement cannot be empty.'];
        }

        $pdo = Db::connect2();

        $stmt = $pdo->prepare("SELECT * FROM family_disputes WHERE id = ?");
        $stmt->execute([$disputeId]);
        $dispute = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dispute) {
            return ['success' => false, 'error' => 'Dispute docket not found.'];
        }

        if ($famCode !== null && $famCode !== '') {
            $normDisputeFam = strtoupper(str_replace('#', '', trim((string)($dispute['famCode'] ?? ''))));
            $normCallerFam = strtoupper(str_replace('#', '', trim($famCode)));
            if ($normDisputeFam !== $normCallerFam) {
                return ['success' => false, 'error' => 'Access denied: dispute belongs to another family network.'];
            }
        }

        if ($dispute['status'] === 'resolved') {
            return ['success' => false, 'error' => 'This council hearing has already concluded with a sealed accord.'];
        }

        // Determine user's role
        $role = null;
        $partyA = (string)$dispute['party_a_id'];
        $partyB = (string)$dispute['party_b_id'];
        $initiator = (string)$dispute['initiator_id'];
        $witnesses = json_decode((string)($dispute['witness_ids'] ?? '[]'), true) ?: [];

        if ($userId === $partyA) {
            $role = 'party_a';
        } elseif ($userId === $partyB) {
            $role = 'party_b';
        } elseif ($userId === $initiator) {
            $role = 'initiator';
        } elseif (in_array($userId, $witnesses, true)) {
            $role = 'witness';
        } else {
            // Any authenticated member of the same family network may contribute their perspective
            $userCheck = $pdo->prepare("SELECT id, famCode FROM personal WHERE id = ?");
            $userCheck->execute([$userId]);
            $userRow = $userCheck->fetch(PDO::FETCH_ASSOC);
            if ($userRow) {
                $normDisputeFam = strtoupper(str_replace('#', '', trim((string)($dispute['famCode'] ?? ''))));
                $normUserFam = strtoupper(str_replace('#', '', trim((string)($userRow['famCode'] ?? ''))));
                if ($normDisputeFam !== '' && $normDisputeFam === $normUserFam) {
                    $role = 'relative';
                }
            }
        }

        if ($role === null) {
            return ['success' => false, 'error' => 'You are not a registered member of this family network.'];
        }

        // Insert or update statement
        $checkStmt = $pdo->prepare("SELECT id FROM family_dispute_statements WHERE dispute_id = ? AND user_id = ?");
        $checkStmt->execute([$disputeId, $userId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $update = $pdo->prepare("UPDATE family_dispute_statements SET statement = ?, created_at = NOW() WHERE id = ?");
            $update->execute([$cleanStatement, $existing['id']]);
        } else {
            $ins = $pdo->prepare("
                INSERT INTO family_dispute_statements (id, dispute_id, user_id, role, statement, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $ins->execute([
                'stm_' . bin2hex(random_bytes(10)),
                $disputeId,
                $userId,
                $role,
                $cleanStatement,
            ]);
        }

        // Check if both Party A and Party B have submitted
        $rolesStmt = $pdo->prepare("SELECT DISTINCT role FROM family_dispute_statements WHERE dispute_id = ?");
        $rolesStmt->execute([$disputeId]);
        $submittedRoles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $hasPartyA = in_array('party_a', $submittedRoles, true);
        $hasPartyB = in_array('party_b', $submittedRoles, true);
        $isReady = $hasPartyA && $hasPartyB;

        if ($isReady && $dispute['status'] === 'awaiting_statements') {
            $upStatus = $pdo->prepare("UPDATE family_disputes SET status = 'ready_for_judgement' WHERE id = ?");
            $upStatus->execute([$disputeId]);
        }

        return [
            'success' => true,
            'is_ready' => $isReady,
        ];
    }

    /**
     * Adjudicate a multi-party dispute through the Family RoundTable reconciliatory council.
     *
     * @param string $disputeId
     * @param string|null $famCode
     * @return array{
     *     success: bool,
     *     verdict_title: string,
     *     finding_of_fact: string,
     *     reconciliatory_bridge: string,
     *     restorative_action_plan: array<string, mixed>,
     *     yommy_wisdom: string,
     *     error?: string
     * }
     */
    public function adjudicate(string $disputeId, ?string $famCode = null): array
    {
        $pdo = Db::connect2();

        $stmt = $pdo->prepare("SELECT * FROM family_disputes WHERE id = ?");
        $stmt->execute([$disputeId]);
        $dispute = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dispute) {
            return [
                'success' => false,
                'verdict_title' => '',
                'finding_of_fact' => '',
                'reconciliatory_bridge' => '',
                'restorative_action_plan' => [],
                'yommy_wisdom' => '',
                'error' => 'Dispute not found.',
            ];
        }

        if ($famCode !== null && $famCode !== '') {
            $normDisputeFam = strtoupper(str_replace('#', '', trim((string)($dispute['famCode'] ?? ''))));
            $normCallerFam = strtoupper(str_replace('#', '', trim($famCode)));
            if ($normDisputeFam !== $normCallerFam) {
                return [
                    'success' => false,
                    'verdict_title' => '',
                    'finding_of_fact' => '',
                    'reconciliatory_bridge' => '',
                    'restorative_action_plan' => [],
                    'yommy_wisdom' => '',
                    'error' => 'Access denied: dispute belongs to another family network.',
                ];
            }
        }

        // Fetch statements
        $sStmt = $pdo->prepare("
            SELECT s.*, p.firstName, p.lastName 
            FROM family_dispute_statements s
            LEFT JOIN personal p ON s.user_id = p.id
            WHERE s.dispute_id = ?
            ORDER BY s.created_at ASC
        ");
        $sStmt->execute([$disputeId]);
        $statements = $sStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Fetch party profiles
        $pStmt = $pdo->prepare("SELECT id, firstName, lastName FROM personal WHERE id IN (?, ?, ?)");
        $pStmt->execute([$dispute['party_a_id'], $dispute['party_b_id'], $dispute['initiator_id']]);
        $usersById = [];
        while ($u = $pStmt->fetch(PDO::FETCH_ASSOC)) {
            $usersById[(string)$u['id']] = $u;
        }

        $partyAName = trim(($usersById[(string)$dispute['party_a_id']]['firstName'] ?? 'Party A') . ' ' . ($usersById[(string)$dispute['party_a_id']]['lastName'] ?? ''));
        $partyBName = trim(($usersById[(string)$dispute['party_b_id']]['firstName'] ?? 'Party B') . ' ' . ($usersById[(string)$dispute['party_b_id']]['lastName'] ?? ''));
        $initiatorName = trim(($usersById[(string)$dispute['initiator_id']]['firstName'] ?? 'Initiator') . ' ' . ($usersById[(string)$dispute['initiator_id']]['lastName'] ?? ''));
        $isThirdParty = (bool)$dispute['is_third_party'];

        // Build context
        $promptContext = "Dispute Title: {$dispute['title']}\nCategory: {$dispute['category']}\n";
        if ($isThirdParty) {
            $promptContext .= "Initiator (Concerned Peacemaker): {$initiatorName}\nInitial Observation: \"{$dispute['grievance']}\"\n";
        }
        $promptContext .= "First Party (Party A): {$partyAName}\n";
        $promptContext .= "Second Party (Party B): {$partyBName}\n\n";
        $promptContext .= "Submitted Testimonies:\n";

        foreach ($statements as $st) {
            $sender = trim(($st['firstName'] ?? 'Family Member') . ' ' . ($st['lastName'] ?? ''));
            $roleLabel = match ($st['role']) {
                'party_a' => "Party A ({$partyAName})",
                'party_b' => "Party B ({$partyBName})",
                'initiator' => "Concerned Peacemaker ({$initiatorName})",
                'relative' => "Family Relative ({$sender})",
                default => "Eyewitness ({$sender})",
            };
            $promptContext .= "- {$roleLabel}: \"{$st['statement']}\"\n";
        }

        $verdictData = $this->callReconciliatoryEngine(
            $partyAName,
            $partyBName,
            $promptContext,
            $isThirdParty ? $initiatorName : null,
            (string)($dispute['category'] ?? 'general_harmony'),
            (string)($dispute['grievance'] ?? '')
        );

        // Update database with decree
        $actionPlanJson = json_encode($verdictData['restorative_action_plan']);
        $up = $pdo->prepare("
            UPDATE family_disputes 
            SET status = 'resolved',
                verdict_title = ?,
                finding_of_fact = ?,
                reconciliatory_bridge = ?,
                restorative_action_plan = ?,
                yommy_wisdom = ?,
                resolved_at = NOW()
            WHERE id = ?
        ");
        $up->execute([
            $verdictData['verdict_title'],
            $verdictData['finding_of_fact'],
            $verdictData['reconciliatory_bridge'],
            $actionPlanJson,
            $verdictData['yommy_wisdom'],
            $disputeId,
        ]);

        // Dispatch resolution notifications
        $notifyUsers = array_unique(array_filter([
            (string)$dispute['initiator_id'],
            (string)$dispute['party_a_id'],
            (string)$dispute['party_b_id'],
        ]));

        $witnessList = json_decode((string)($dispute['witness_ids'] ?? '[]'), true) ?: [];
        foreach ($witnessList as $wId) {
            $notifyUsers[] = (string)$wId;
        }

        $famCode = (string)$dispute['famCode'];
        $actionUrl = '/family/roundtable?dispute_id=' . $disputeId;

        foreach ($notifyUsers as $targetId) {
            NotificationOrchestrator::dispatch(
                userId: $targetId,
                category: 'Kinship',
                priority: 'high',
                title: '🛡️ Family RoundTable Accord Sealed',
                body: "A reconciliatory accord has been sealed for '{$dispute['title']}'. Tap to view the family agreement.",
                actionUrl: $actionUrl,
                tag: 'family_roundtable_accord',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );
        }

        return [
            'success' => true,
            'verdict_title' => $verdictData['verdict_title'],
            'finding_of_fact' => $verdictData['finding_of_fact'],
            'reconciliatory_bridge' => $verdictData['reconciliatory_bridge'],
            'restorative_action_plan' => $verdictData['restorative_action_plan'],
            'yommy_wisdom' => $verdictData['yommy_wisdom'],
        ];
    }

    /**
     * Fast-turn instant 2-minute peacemaker referee.
     *
     * @param string $partyAName
     * @param string $partyAClaim
     * @param string $partyBName
     * @param string $partyBClaim
     * @param string $context
     * @param bool $isThirdParty
     * @param string|null $initiatorName
     * @return array{
     *     success: bool,
     *     verdict_title: string,
     *     finding_of_fact: string,
     *     reconciliatory_bridge: string,
     *     restorative_action_plan: array<string, mixed>,
     *     yommy_wisdom: string,
     *     error?: string
     * }
     */
    public function arbitrateInstant(
        string $partyAName,
        string $partyAClaim,
        string $partyBName,
        string $partyBClaim,
        string $context = 'household_harmony',
        bool $isThirdParty = false,
        ?string $initiatorName = null
    ): array {
        $nameA = htmlspecialchars(trim($partyAName), ENT_QUOTES, 'UTF-8');
        $claimA = htmlspecialchars(trim($partyAClaim), ENT_QUOTES, 'UTF-8');
        $nameB = htmlspecialchars(trim($partyBName), ENT_QUOTES, 'UTF-8');
        $claimB = htmlspecialchars(trim($partyBClaim), ENT_QUOTES, 'UTF-8');

        if ($nameA === '' || $nameB === '' || $claimA === '' || $claimB === '') {
            return [
                'success' => false,
                'verdict_title' => '',
                'finding_of_fact' => '',
                'reconciliatory_bridge' => '',
                'restorative_action_plan' => [],
                'yommy_wisdom' => '',
                'error' => 'Both parties must provide names and statements.',
            ];
        }

        $promptContext = "Dispute Context: {$context}\n";
        if ($isThirdParty && $initiatorName !== null) {
            $promptContext .= "Initiated by Concerned Peacemaker: {$initiatorName}\n";
        }
        $promptContext .= "Party A ({$nameA}): \"{$claimA}\"\nParty B ({$nameB}): \"{$claimB}\"";

        return array_merge(['success' => true], $this->callReconciliatoryEngine($nameA, $nameB, $promptContext, $initiatorName, $context, $claimA . ' ' . $claimB));
    }

    /**
     * Internal Reconciliatory Prompt Engine for Family RoundTable
     *
     * @param string $partyA
     * @param string $partyB
     * @param string $docketDetails
     * @param string|null $initiatorName
     * @return array{
     *     verdict_title: string,
     *     finding_of_fact: string,
     *     reconciliatory_bridge: string,
     *     restorative_action_plan: array<string, string>,
     *     yommy_wisdom: string
     * }
     */
    /**
     * Internal Reconciliatory Prompt Engine for Family RoundTable
     *
     * Multi-tiered inference pipeline:
     * 1. Google Gemini 3.8 Flash (live, sub-2s response, structured JSON)
     * 2. DeepSeek Chat API (live secondary fallback)
     * 3. HuggingFace / FallbackChat Gateway (tertiary fallback)
     * 4. Smart Category & Keyword-Aware Deterministic Kinship Fallback
     *
     * @param string $partyA
     * @param string $partyB
     * @param string $docketDetails
     * @param string|null $initiatorName
     * @param string $category
     * @param string $grievance
     * @return array{
     *     verdict_title: string,
     *     finding_of_fact: string,
     *     reconciliatory_bridge: string,
     *     restorative_action_plan: array<string, string>,
     *     yommy_wisdom: string
     * }
     */
    private function callReconciliatoryEngine(
        string $partyA,
        string $partyB,
        string $docketDetails,
        ?string $initiatorName = null,
        string $category = 'general_harmony',
        string $grievance = ''
    ): array {
        $systemPrompt = <<<PROMPT
You are presiding over the 'Family RoundTable', an ancestral, collaborative, and dignified council of family elders and mediators.
Your mission is RECONCILIATION, KINSHIP UNITY, DOMESTIC HARMONY, EMPATHY, AND FIRM COOPERATION.
Under NO circumstances are you to be punitive, adversarial, shaming, or vindictive.
You NEVER declare one family member a villain or culprit.
Instead, your council mission is:
1. "verdict_title": A prestigious, uplifting title for the family accord (e.g. "The Olaogun Kinship Accord of Financial Relief & Mutual Respect").
2. "finding_of_fact": An objective, respectful summary of what occurred, explicitly validating how each person felt and why their frustration arose naturally. Must specifically address the real subject matter (e.g. financial loans, interest, borrowed items, or family boundaries).
3. "reconciliatory_bridge": A touching, deeply empathetic synthesis highlighting the mutual care, underlying family bond, reconciliatory spirit, and domestic harmony shared by both sides.
4. "restorative_action_plan": Concrete, practical, firm, and non-punitive commitments. Must be an object with keys "{$partyA}", "{$partyB}", and optionally "family_agreement", containing specific positive actions to solve the issue permanently.
5. "yommy_wisdom": A memorable, wise, or profound golden proverb from the Family RoundTable to seal lasting domestic peace.

You must output ONLY a valid JSON object matching this exact schema:
{
  "verdict_title": "string",
  "finding_of_fact": "string",
  "reconciliatory_bridge": "string",
  "restorative_action_plan": {
    "{$partyA}": "Positive restorative commitment",
    "{$partyB}": "Positive restorative commitment",
    "family_agreement": "Shared golden standard"
  },
  "yommy_wisdom": "string"
}
PROMPT;

        $parsed = null;

        // Tier 1: Google Gemini Flash Engine (Fast, High Quality, Native JSON Mode)
        try {
            $parsed = $this->callGeminiEngine($systemPrompt, $docketDetails);
        } catch (\Throwable $e) {
            $parsed = null;
        }

        // Tier 2: DeepSeek Chat API Engine (Secondary Live Tier)
        if (!is_array($parsed) || empty($parsed['finding_of_fact'])) {
            try {
                $parsed = $this->callDeepSeekEngine($systemPrompt, $docketDetails);
            } catch (\Throwable $e) {
                $parsed = null;
            }
        }

        // Tier 3: Gateway / Fallback Chat Interface (Tertiary Tier)
        if (!is_array($parsed) || empty($parsed['finding_of_fact'])) {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $docketDetails],
            ];
            try {
                $response = $this->gateway->chatCompletion($messages, 'meta-llama/Llama-3.2-3B-Instruct', [
                    'max_tokens' => 1024,
                    'temperature' => 0.5,
                ]);

                if ($response !== null && !empty($response['choices'][0]['message']['content'])) {
                    $content = (string)$response['choices'][0]['message']['content'];
                    $parsed = $this->extractJson($content);
                } elseif (method_exists($this->fallbackChat, 'chat')) {
                    $content = $this->fallbackChat->chat($messages, ['max_tokens' => 1024, 'temperature' => 0.5]);
                    $parsed = $this->extractJson((string)$content);
                }
            } catch (\Throwable $e) {
                $parsed = null;
            }
        }

        if (is_array($parsed) && !empty($parsed['finding_of_fact'])) {
            return [
                'verdict_title' => (string)($parsed['verdict_title'] ?? "The RoundTable Accord: {$partyA} & {$partyB}"),
                'finding_of_fact' => (string)($parsed['finding_of_fact'] ?? ''),
                'reconciliatory_bridge' => (string)($parsed['reconciliatory_bridge'] ?? ''),
                'restorative_action_plan' => is_array($parsed['restorative_action_plan'] ?? null) ? $parsed['restorative_action_plan'] : [
                    $partyA => 'Commits to active listening and transparent communication.',
                    $partyB => 'Commits to collaborative check-ins before assumptions form.',
                ],
                'yommy_wisdom' => (string)($parsed['yommy_wisdom'] ?? 'At the Family RoundTable, there are no losers; only kin who found their way back to harmony.'),
            ];
        }

        // Tier 4: Smart Context & Keyword-Aware Deterministic Reconciliatory Fallback
        return $this->deterministicReconciliatoryFallback($partyA, $partyB, $initiatorName, $category, $grievance, $docketDetails);
    }

    /**
     * Call Google Gemini API directly with JSON response formatting.
     *
     * @param string $systemPrompt
     * @param string $docketDetails
     * @return array<string, mixed>|null
     */
    private function callGeminiEngine(string $systemPrompt, string $docketDetails): ?array
    {
        $apiKey = $this->getEnvVar('GEMINI_API_KEY');
        if ($apiKey === '') {
            return null;
        }

        $model = $this->getEnvVar('GEMINI_MODEL');
        if ($model === '') {
            $model = 'gemini-3.8-flash';
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($model) . ':generateContent?key=' . urlencode($apiKey);

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $docketDetails]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.4,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !is_string($response) || trim($response) === '') {
            return null;
        }

        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        return $this->extractJson($text);
    }

    /**
     * Call DeepSeek Chat API directly with JSON object format.
     *
     * @param string $systemPrompt
     * @param string $docketDetails
     * @return array<string, mixed>|null
     */
    private function callDeepSeekEngine(string $systemPrompt, string $docketDetails): ?array
    {
        $apiKey = $this->getEnvVar('DEEPSEEK_API_KEY');
        if ($apiKey === '') {
            return null;
        }

        $payload = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $docketDetails],
            ],
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 1024,
            'temperature' => 0.4,
        ];

        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !is_string($response) || trim($response) === '') {
            return null;
        }

        $decoded = json_decode($response, true);
        $text = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        return $this->extractJson($text);
    }

    /**
     * Deterministic Reconciliatory Fallback when LLM gateways are offline.
     * Dynamically adapts to the subject matter (finances, loans, belongings, boundaries, events, chores).
     *
     * @param string $partyA
     * @param string $partyB
     * @param string|null $initiatorName
     * @param string $category
     * @param string $grievance
     * @param string $docketDetails
     * @return array{
     *     verdict_title: string,
     *     finding_of_fact: string,
     *     reconciliatory_bridge: string,
     *     restorative_action_plan: array<string, string>,
     *     yommy_wisdom: string
     * }
     */
    private function deterministicReconciliatoryFallback(
        string $partyA,
        string $partyB,
        ?string $initiatorName = null,
        string $category = 'general_harmony',
        string $grievance = '',
        string $docketDetails = ''
    ): array {
        $combined = strtolower($category . ' ' . $grievance . ' ' . $docketDetails);
        $peacemakerText = $initiatorName !== null
            ? " Brought forward with love by {$initiatorName} to preserve kinship harmony."
            : "";

        // Check if dispute involves financial commitments, loans, repayments, debt, interest
        $isFinancial = $category === 'finances_split'
            || (bool)preg_match('/(\b(money|loan|interest|debt|owe|owing|paid|pay|repay|repayment|pound|pounds|borrow|borrowed|cash|bank|fund|funds|financial|credit)\b|£|\$|€)/i', $combined);

        if ($isFinancial) {
            return [
                'verdict_title' => "The RoundTable Accord of Financial Honour & Kinship Relief: {$partyA} & {$partyB}",
                'finding_of_fact' => "The Family RoundTable has examined the financial accounts and perspectives presented. Money lent within a family is extended out of genuine love, trust, and kinship solidarity. Naturally, the strain of unpaid balances and accumulating interest creates acute anxiety and pressure for {$partyB}. Simultaneously, the Council recognises that {$partyA}'s delay arises from unforeseen financial pressures rather than malice or disregard. Both parties deserve relief, transparent accounting, and peace of mind.{$peacemakerText}",
                'reconciliatory_bridge' => "Kinship wealth is measured not by ledger balances, but by mutual trust, dignity, and generational unity. The bond between {$partyA} and {$partyB} far exceeds any monetary figure. By replacing silence and worry with a structured, transparent, and realistic repayment pathway, the family preserves both financial integrity and familial love.",
                'restorative_action_plan' => [
                    $partyA => "Agrees to commit to a structured, transparent monthly instalment schedule to steadily clear the balance and interest, providing proactive fortnightly communication updates.",
                    $partyB => "Agrees to provide transparent documentation of remaining balance and interest charges, and graciously accept the structured repayment schedule with patience as long as communication remains open.",
                    'family_agreement' => "Family elders and peacemakers will serve as supportive custodians, holding a gentle monthly review to celebrate repayment milestones and protect domestic peace.",
                ],
                'yommy_wisdom' => "Money borrowed between kin must never outvalue the hands that shared it; when honour guides repayment and patience tempers the journey, family wealth endures forever.",
            ];
        }

        // Check if dispute involves borrowed belongings or property
        $isProperty = $category === 'borrowed_property'
            || (bool)preg_match('/(\b(borrowed|lent|lend|return|item|belongings|tools|car|vehicle|clothing|clothes|property|gadget|laptop|phone)\b)/i', $combined);

        if ($isProperty) {
            return [
                'verdict_title' => "The RoundTable Accord of Property Care & Kinship Trust: {$partyA} & {$partyB}",
                'finding_of_fact' => "The Family RoundTable has reviewed the accounts regarding borrowed belongings. Sharing items among kin is an expression of closeness, but unreturned or damaged possessions naturally erode confidence and create daily friction. Both parties value mutual respect and clear boundaries.{$peacemakerText}",
                'reconciliatory_bridge' => "Possessions come and go, but mutual trust remains irreplaceable. Restoring borrowed items with gratitude and care revitalises the affection and goodwill between family members.",
                'restorative_action_plan' => [
                    $partyA => "Agrees to return all borrowed items promptly in clean, restored condition within 48 hours, or arrange prompt repair/replacement if damaged.",
                    $partyB => "Agrees to receive the returned items with warmth and close the matter without lingering grievance.",
                    'family_agreement' => "A clear family principle that borrowed items must be accompanied by an agreed return date and treated with higher care than one's own.",
                ],
                'yommy_wisdom' => "To borrow from kin is an act of trust; to return with gratitude and care is the cement of family honour.",
            ];
        }

        // Check if dispute involves personal boundaries, space, noise, or privacy
        $isBoundaries = $category === 'boundaries_space'
            || (bool)preg_match('/(\b(boundary|boundaries|privacy|private|space|room|quiet|noise|loud|music|sleep|door|knock)\b)/i', $combined);

        if ($isBoundaries) {
            return [
                'verdict_title' => "The RoundTable Accord of Sacred Sanctuary & Mutual Regard: {$partyA} & {$partyB}",
                'finding_of_fact' => "The Family RoundTable recognises that every family member requires personal sanctuary, emotional space, and rest to thrive. In a busy household, overlapping schedules, noise, and unintentional intrusions inevitably cause fatigue and short tempers. Both parties desire a peaceful environment where their space is honoured.{$peacemakerText}",
                'reconciliatory_bridge' => "Boundaries within a loving home are not walls built for division, but gentle gardens that allow each soul to recharge in safety and warmth.",
                'restorative_action_plan' => [
                    $partyA => "Agrees to knock and request permission before entering private spaces, respecting quiet hours and downtime.",
                    $partyB => "Agrees to communicate fatigue or the need for quiet calmly and early, before frustration builds.",
                    'family_agreement' => "Designated household quiet hours and a shared commitment that closed doors signal a request for restful privacy.",
                ],
                'yommy_wisdom' => "When we give our kin the space to breathe in peace, the entire home becomes a sanctuary of lasting joy.",
            ];
        }

        // Check if dispute involves family events or milestones
        $isEvents = $category === 'family_events'
            || (bool)preg_match('/(\b(event|holiday|birthday|celebration|wedding|party|dinner|vacation|trip|gathering|christmas|new year)\b)/i', $combined);

        if ($isEvents) {
            return [
                'verdict_title' => "The RoundTable Accord of Celebratory Unity & Shared Light: {$partyA} & {$partyB}",
                'finding_of_fact' => "The Family RoundTable acknowledges that major family gatherings carry heightened expectations, logistical burdens, and emotional weight. Misaligned schedules or uneven preparation duties naturally create strain during what should be joyful milestones.{$peacemakerText}",
                'reconciliatory_bridge' => "Family milestones exist to celebrate the enduring gift of kinship, not to foster contention. Shared laughter and presence are vastly more precious than flawless coordination.",
                'restorative_action_plan' => [
                    $partyA => "Agrees to confirm attendance and travel details with reasonable notice, sharing the logistical load generously.",
                    $partyB => "Agrees to extend flexibility for unexpected delays and focus on warm celebration rather than rigid perfection.",
                    'family_agreement' => "A shared family coordination checklist established 7 days prior to any major gathering to distribute roles evenly.",
                ],
                'yommy_wisdom' => "A modest gathering wrapped in patience and laughter feeds the soul deeper than a grand banquet served with bitter words.",
            ];
        }

        // Default: Household Harmony & Mutual Respect
        return [
            'verdict_title' => "The RoundTable Accord of Mutual Respect: {$partyA} & {$partyB}",
            'finding_of_fact' => "The Family RoundTable has examined the accounts presented. This disagreement does not stem from malice, but from competing pressures, fatigue, and mismatched expectations. {$partyA} felt unheard and unappreciated, whilst {$partyB} felt unfairly pressured and scrutinised. Both emotional viewpoints are valid and deserve honour.{$peacemakerText}",
            'reconciliatory_bridge' => "Underneath the friction lies a shared wish for a peaceful home and mutual recognition. You share a bond stronger than any chore, misunderstood remark, or schedule collision. The council decrees that neither party is at fault; rather, the family simply needs a clearer rhythm.",
            'restorative_action_plan' => [
                $partyA => "Agrees to express needs early with kindness, giving {$partyB} reasonable notice and grace.",
                $partyB => "Agrees to acknowledge {$partyA}'s contributions warmly and follow through on shared household tasks.",
                'family_agreement' => "A 5-minute Sunday evening family sync to coordinate schedules over tea or a favourite snack.",
            ],
            'yommy_wisdom' => "A strong family is not one that never clashes, but one that gathers at the RoundTable to forgive quickly, listen deeply, and laugh together afterwards.",
        ];
    }

    /**
     * Safely fetch environment variable from $_ENV, getenv, $_SERVER, or direct .env file.
     *
     * @param string $key
     * @return string
     */
    private function getEnvVar(string $key): string
    {
        $val = $_ENV[$key] ?? getenv($key) ?: ($_SERVER[$key] ?? '');
        if (is_string($val) && trim($val) !== '') {
            return trim($val);
        }

        static $fileEnv = null;
        if ($fileEnv === null) {
            $fileEnv = [];
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                        continue;
                    }
                    [$k, $v] = explode('=', $line, 2);
                    $fileEnv[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
                }
            }
        }

        return (string)($fileEnv[$key] ?? '');
    }

    /**
     * Extract valid JSON from raw output.
     *
     * @param string $raw
     * @return array<string, mixed>|null
     */
    private function extractJson(string $raw): ?array
    {
        $clean = trim($raw);
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{.*\})/s', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Fetch dispute details with statements and profiles.
     *
     * @param string $disputeId
     * @param string $famCode
     * @return array<string, mixed>|null
     */
    public function getDisputeDetails(string $disputeId, string $famCode): ?array
    {
        $pdo = Db::connect2();

        $stmt = $pdo->prepare("SELECT * FROM family_disputes WHERE id = ? AND famCode = ?");
        $stmt->execute([$disputeId, $famCode]);
        $dispute = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dispute) {
            return null;
        }

        $pStmt = $pdo->prepare("
            SELECT p.id, p.firstName, p.lastName, pp.img AS profilePics 
            FROM personal p 
            LEFT JOIN profilePics pp ON p.id = pp.id 
            WHERE p.famCode = ?
        ");
        $pStmt->execute([$famCode]);
        $familyProfiles = [];
        while ($prof = $pStmt->fetch(PDO::FETCH_ASSOC)) {
            $familyProfiles[(string)$prof['id']] = $prof;
        }

        $sStmt = $pdo->prepare("
            SELECT * FROM family_dispute_statements 
            WHERE dispute_id = ? 
            ORDER BY created_at ASC
        ");
        $sStmt->execute([$disputeId]);
        $statements = $sStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($statements as &$st) {
            $uid = (string)$st['user_id'];
            $st['author'] = $familyProfiles[$uid] ?? [
                'firstName' => 'Family',
                'lastName' => 'Member',
                'profilePics' => null,
            ];
        }
        unset($st);

        $dispute['party_a'] = $familyProfiles[(string)$dispute['party_a_id']] ?? null;
        $dispute['party_b'] = $familyProfiles[(string)$dispute['party_b_id']] ?? null;
        $dispute['initiator'] = $familyProfiles[(string)$dispute['initiator_id']] ?? null;

        $witnessIds = json_decode((string)($dispute['witness_ids'] ?? '[]'), true) ?: [];
        $dispute['witnesses'] = array_values(array_filter(array_map(
            fn($wid) => $familyProfiles[(string)$wid] ?? null,
            $witnessIds
        )));

        $dispute['statements'] = $statements;
        $dispute['action_plan_decoded'] = json_decode((string)($dispute['restorative_action_plan'] ?? '[]'), true) ?: [];

        return $dispute;
    }

    /**
     * List recent council hearings for a family code.
     *
     * @param string $famCode
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function listFamilyDisputes(string $famCode, int $limit = 25): array
    {
        $pdo = Db::connect2();

        $stmt = $pdo->prepare("
            SELECT d.*, 
                   pa.firstName AS partyA_firstName, pa.lastName AS partyA_lastName,
                   pb.firstName AS partyB_firstName, pb.lastName AS partyB_lastName,
                   pi.firstName AS initiator_firstName, pi.lastName AS initiator_lastName
            FROM family_disputes d
            LEFT JOIN personal pa ON d.party_a_id = pa.id
            LEFT JOIN personal pb ON d.party_b_id = pb.id
            LEFT JOIN personal pi ON d.initiator_id = pi.id
            WHERE d.famCode = ?
            ORDER BY d.created_at DESC
            LIMIT 25
        ");
        $stmt->execute([$famCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
