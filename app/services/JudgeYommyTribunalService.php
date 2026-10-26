<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use Src\Ai\HuggingFaceInferenceGateway;
use Src\Ai\ResilientAiChat;
use App\services\NotificationOrchestrator;
use PDO;

/**
 * Judge Yommy Court of Family Harmony & Reconciliatory Tribunal
 *
 * Implements complex domestic dispute settlement:
 * - Can be initiated by a direct disputant (Party A) OR a concerned 3rd-party peacemaker/elder.
 * - Multi-party testimonies from Party A, Party B (Respondent), and Named Witnesses.
 * - Non-punitive, empathetic, firm, and reconciliatory adjudication.
 * - Resilient multi-tier AI inference with deterministic fallback engine.
 */
class JudgeYommyTribunalService
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
     * File a new dispute docket.
     *
     * @param string $famCode Family Code
     * @param string $initiatorId User ID of person filing
     * @param bool $isThirdParty True if filed by a peacemaker/observer on behalf of 2 feuding members
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
            return ['success' => false, 'error' => 'A dispute requires two distinct family members.'];
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
            // Remove parties themselves from witness list
            $cleanWitnessIds = array_values(array_diff($cleanWitnessIds, [$partyAId, $partyBId]));
        }

        $disputeId = 'dsp_' . bin2hex(random_bytes(12));
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
        $actionUrl = '/family/fun-place?dispute_id=' . $disputeId;

        if ($isThirdParty) {
            // Both Party A and Party B need to be notified that a 3rd party peacemaker opened a hearing
            NotificationOrchestrator::dispatch(
                userId: $partyAId,
                category: 'Kinship',
                priority: 'high',
                title: '⚖️ Judge Yommy Hearing Initiated',
                body: "{$initiatorName} has invited Judge Yommy to help settle a dispute regarding '{$cleanTitle}'. Tap to share your side.",
                actionUrl: $actionUrl,
                tag: 'judge_yommy_dispute',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );

            NotificationOrchestrator::dispatch(
                userId: $partyBId,
                category: 'Kinship',
                priority: 'high',
                title: '⚖️ Judge Yommy Hearing Initiated',
                body: "{$initiatorName} has invited Judge Yommy to help settle a dispute regarding '{$cleanTitle}'. Tap to share your side.",
                actionUrl: $actionUrl,
                tag: 'judge_yommy_dispute',
                familyCode: $famCode,
                metadata: ['dispute_id' => $disputeId]
            );
        } else {
            // Party A filed against Party B
            NotificationOrchestrator::dispatch(
                userId: $partyBId,
                category: 'Kinship',
                priority: 'high',
                title: '⚖️ Judge Yommy Hearing Request',
                body: "{$initiatorName} has brought a matter before Judge Yommy: '{$cleanTitle}'. Tap to submit your counter-statement.",
                actionUrl: $actionUrl,
                tag: 'judge_yommy_dispute',
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
                title: '⚖️ Eyewitness Input Requested',
                body: "{$initiatorName} has requested your objective insight on '{$cleanTitle}' before Judge Yommy.",
                actionUrl: $actionUrl,
                tag: 'judge_yommy_dispute',
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
     * Submit a statement or counter-argument for an ongoing dispute.
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
            return ['success' => false, 'error' => 'This hearing has already been concluded by Judge Yommy.'];
        }

        // Determine user's role in this dispute
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
        }

        if ($role === null) {
            return ['success' => false, 'error' => 'You are not a registered party or witness in this hearing.'];
        }

        // Insert or update statement for this user in this dispute
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
     * Adjudicate a multi-party dispute through Judge Yommy's reconciliatory engine.
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

        // Fetch all statements
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

        // Build prompt context
        $promptContext = "Dispute Title: {$dispute['title']}\nCategory: {$dispute['category']}\n";
        if ($isThirdParty) {
            $promptContext .= "Initiator (Concerned Third-Party Peacemaker): {$initiatorName}\nInitial Observation: \"{$dispute['grievance']}\"\n";
        }
        $promptContext .= "First Party (Party A): {$partyAName}\n";
        $promptContext .= "Second Party (Party B): {$partyBName}\n\n";
        $promptContext .= "Submitted Testimonies:\n";

        foreach ($statements as $st) {
            $sender = trim(($st['firstName'] ?? 'Family Member') . ' ' . ($st['lastName'] ?? ''));
            $roleLabel = match ($st['role']) {
                'party_a' => "Party A ({$partyAName})",
                'party_b' => "Party B ({$partyBName})",
                'initiator' => "Third-Party Peacemaker ({$initiatorName})",
                default => "Eyewitness ({$sender})",
            };
            $promptContext .= "- {$roleLabel}: \"{$st['statement']}\"\n";
        }

        // Call Judge Yommy Reconciliatory Engine
        $verdictData = $this->callReconciliatoryEngine(
            $partyAName,
            $partyBName,
            $promptContext,
            $isThirdParty ? $initiatorName : null
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

        // Dispatch resolution notifications to all involved parties
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
        $actionUrl = '/family/fun-place?dispute_id=' . $disputeId;

        foreach ($notifyUsers as $targetId) {
            NotificationOrchestrator::dispatch(
                userId: $targetId,
                category: 'Kinship',
                priority: 'high',
                title: '⚖️ Judge Yommy Has Delivered Judgement',
                body: "Official decree issued for '{$dispute['title']}': A reconciliatory and firm resolution is now sealed.",
                actionUrl: $actionUrl,
                tag: 'judge_yommy_verdict',
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
     * Fast-turn instant 2-minute arbitration referee for domestic banter.
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

        return array_merge(['success' => true], $this->callReconciliatoryEngine($nameA, $nameB, $promptContext, $initiatorName));
    }

    /**
     * Internal Reconciliatory Prompt Engine with Multi-Tier Fallback
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
    private function callReconciliatoryEngine(
        string $partyA,
        string $partyB,
        string $docketDetails,
        ?string $initiatorName = null
    ): array {
        $systemPrompt = <<<PROMPT
You are 'Judge Yommy', an exceptionally wise, compassionate, dignified, and emotionally intelligent British family arbiter and referee presiding over the Court of Family Harmony.
Your tone is FIRM, FAIR, WARM, AND STRICTLY RECONCILIATORY.
Under NO circumstances are you to be punitive, shaming, or vindictive.
You NEVER declare one person an enemy or villain.
Instead, your judicial mission is:
1. "verdict_title": A regal, uplifting title for the judicial decree (e.g. "The Great Kitchen Accord of 2026").
2. "finding_of_fact": An objective, respectful summary of what occurred, explicitly validating how each person felt and why their frustration arose naturally.
3. "reconciliatory_bridge": A touching, deeply empathetic synthesis that highlights the mutual care, underlying family bond, and common ground shared by both sides.
4. "restorative_action_plan": Concrete, practical, firm, and non-punitive commitments. Must be an object with keys "{$partyA}", "{$partyB}", and optionally "family_agreement", containing specific positive actions to solve the issue permanently.
5. "yommy_wisdom": A memorable, witty, or profound golden proverb from Judge Yommy to seal family peace.

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

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $docketDetails],
        ];

        $content = null;
        try {
            $response = $this->gateway->chatCompletion($messages, 'meta-llama/Llama-3.2-3B-Instruct', [
                'max_tokens' => 1024,
                'temperature' => 0.5,
            ]);

            if ($response !== null && !empty($response['choices'][0]['message']['content'])) {
                $content = (string)$response['choices'][0]['message']['content'];
            } else {
                // Secondary fallback via ResilientAiChat
                if (method_exists($this->fallbackChat, 'chat')) {
                    $content = $this->fallbackChat->chat($messages, ['max_tokens' => 1024, 'temperature' => 0.5]);
                } else {
                    $res = $this->fallbackChat->complete($messages, ['max_tokens' => 1024, 'temperature' => 0.5]);
                    $content = ($res['success'] && trim($res['content']) !== '') ? $res['content'] : null;
                }
            }
        } catch (\Throwable $e) {
            $content = null;
        }

        $parsed = null;
        if ($content !== null && trim($content) !== '') {
            $parsed = $this->extractJson($content);
        }

        if (is_array($parsed) && !empty($parsed['finding_of_fact'])) {
            return [
                'verdict_title' => (string)($parsed['verdict_title'] ?? "Decree of Harmony between {$partyA} and {$partyB}"),
                'finding_of_fact' => (string)($parsed['finding_of_fact'] ?? ''),
                'reconciliatory_bridge' => (string)($parsed['reconciliatory_bridge'] ?? ''),
                'restorative_action_plan' => is_array($parsed['restorative_action_plan'] ?? null) ? $parsed['restorative_action_plan'] : [
                    $partyA => 'Commits to active listening and transparent communication.',
                    $partyB => 'Commits to collaborative check-ins before assumptions form.',
                ],
                'yommy_wisdom' => (string)($parsed['yommy_wisdom'] ?? 'In the court of family, true victory is not being right, but being reconciled.'),
            ];
        }

        // Deterministic Fallback Engine (Guarantees zero-downtime reconciliatory decree)
        return $this->deterministicReconciliatoryFallback($partyA, $partyB, $initiatorName);
    }

    /**
     * Deterministic Reconciliatory Fallback when LLM gateways are offline.
     *
     * @param string $partyA
     * @param string $partyB
     * @param string|null $initiatorName
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
        ?string $initiatorName = null
    ): array {
        $peacemakerText = $initiatorName !== null
            ? " Brought forward with love by {$initiatorName} to safeguard kinship."
            : "";

        return [
            'verdict_title' => "The Covenant of Mutual Respect: {$partyA} & {$partyB}",
            'finding_of_fact' => "Judge Yommy has examined the accounts presented. This disagreement does not stem from malice, but from competing pressures, fatigue, and mismatched expectations. {$partyA} felt unheard and unappreciated, whilst {$partyB} felt unfairly pressured and scrutinised. Both emotional viewpoints are valid and deserve honour.{$peacemakerText}",
            'reconciliatory_bridge' => "Underneath the frustration lies a shared wish for a peaceful home and mutual recognition. You share a bond stronger than any chore, misunderstood remark, or schedule collision. The court decrees that neither party is 'guilty'; rather, the family system simply needs a clearer rhythm.",
            'restorative_action_plan' => [
                $partyA => "Agrees to express needs early with kindness, giving {$partyB} reasonable notice and grace.",
                $partyB => "Agrees to acknowledge {$partyA}'s contributions warmly and follow through on shared household tasks.",
                'family_agreement' => "A 5-minute Sunday evening family sync to coordinate schedules over tea or a favourite snack.",
            ],
            'yommy_wisdom' => "A strong family is not one that never clashes, but one that knows how to forgive quickly, listen deeply, and laugh together afterwards. Court is adjourned in peace.",
        ];
    }

    /**
     * Extract valid JSON from raw model output.
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
     * Fetch dispute with full statements and participants for UI rendering.
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

        // Fetch user profiles for participants
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

        // Fetch statements
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
     * List recent disputes for a family code.
     *
     * @param string $famCode
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function listFamilyDisputes(string $famCode, int $limit = 10): array
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
