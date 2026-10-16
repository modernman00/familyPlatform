<?php

declare(strict_types=1);

namespace App\services;

use App\controller\BaseController;
use Src\Db;
use PDO;

/**
 * LineageQuestService
 *
 * Powers the gamified "Missing Link" WhatsApp Quests flywheel for FamilyPlatform.
 * Analyzes the family graph to detect missing ancestors, unclaimed living relatives,
 * and incomplete lineage data, transforming them into high-conversion WhatsApp quests.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class LineageQuestService
{
    /**
     * Compute lineage quests and tree completeness for a family.
     *
     * @param string $familyCode Target family code
     * @param string $userId Current active user ID
     * @return array{
     *     family_code: string,
     *     completeness_percentage: int,
     *     total_nodes: int,
     *     registered_nodes: int,
     *     unclaimed_nodes_count: int,
     *     missing_ancestors_count: int,
     *     quests: array<int, array<string, mixed>>
     * }
     */
    public static function getFamilyQuests(string $familyCode, string $userId): array
    {
        $db = Db::connect2();

        // 1. Fetch all nodes for the family
        $stmt = $db->prepare(
            'SELECT id, user_id, first_name, last_name, maiden_name, gender, birth_date, birth_year, is_deceased, generation_level, email, mobile
             FROM family_nodes
             WHERE family_code = ?
             ORDER BY generation_level ASC, id ASC'
        );
        $stmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $nodes */
        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalNodes = count($nodes);
        if ($totalNodes === 0) {
            return [
                'family_code' => $familyCode,
                'completeness_percentage' => 15,
                'total_nodes' => 0,
                'registered_nodes' => 0,
                'unclaimed_nodes_count' => 0,
                'missing_ancestors_count' => 1,
                'quests' => [
                    self::buildSeedQuest($familyCode, $userId)
                ],
            ];
        }

        // 2. Fetch union and child relations
        $unionStmt = $db->prepare('SELECT id, partner_1_id, partner_2_id FROM family_unions WHERE family_code = ?');
        $unionStmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $unions */
        $unions = $unionStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $registeredCount = 0;
        $unclaimedLiving = [];
        $elders = [];
        $missingMaiden = [];
        $generations = [];

        foreach ($nodes as $node) {
            $gen = (int)($node['generation_level'] ?? 0);
            $generations[$gen] = true;

            $hasAccount = !empty($node['user_id']);
            $isDeceased = !empty($node['is_deceased']);

            if ($hasAccount) {
                $registeredCount++;
            } elseif (!$isDeceased) {
                $unclaimedLiving[] = $node;
            }

            if ($gen <= -1) {
                $elders[] = $node;
            }

            // Female ancestor with no maiden name recorded
            if (($node['gender'] ?? '') === 'Female' && empty($node['maiden_name']) && $gen <= 0) {
                $missingMaiden[] = $node;
            }
        }

        // 3. Compute Completeness Percentage (0-100%)
        $genCount = count($generations);
        $depthScore = min(40, $genCount * 12); // up to 40% for multi-generational depth
        $claimScore = (int)round(($registeredCount / $totalNodes) * 35); // up to 35% for members joined
        $richnessScore = 0;
        if ($totalNodes >= 5) {
            $richnessScore += 10;
        }
        if (count($unions) >= 2) {
            $richnessScore += 10;
        }
        if (count($elders) >= 2) {
            $richnessScore += 5;
        }

        $completeness = max(20, min(100, $depthScore + $claimScore + $richnessScore));

        // 4. Formulate Actionable Quests
        $quests = [];

        // Quest A: Unclaimed Living Relatives (High Priority Conversion)
        foreach (array_slice($unclaimedLiving, 0, 3) as $unclaimed) {
            $nodeId = (int)$unclaimed['id'];
            $relName = trim(($unclaimed['first_name'] ?? '') . ' ' . ($unclaimed['last_name'] ?? ''));
            $quests[] = self::buildUnclaimedNodeQuest($familyCode, $userId, $nodeId, $relName);
        }

        // Quest B: Missing Ancestral Roots / Grandparents
        if (count($elders) < 4) {
            $quests[] = self::buildAncestorsQuest($familyCode, $userId, count($elders));
        }

        // Quest C: Missing Maternal Lineage / Maiden Names
        if (!empty($missingMaiden)) {
            $target = $missingMaiden[0];
            $nodeId = (int)$target['id'];
            $targetName = trim(($target['first_name'] ?? '') . ' ' . ($target['last_name'] ?? ''));
            $quests[] = self::buildMaidenNameQuest($familyCode, $userId, $nodeId, $targetName);
        }

        // Quest D: General Family WhatsApp Rally (Always available as anchor)
        $quests[] = self::buildRallyQuest($familyCode, $userId, $completeness, $totalNodes);

        return [
            'family_code' => $familyCode,
            'completeness_percentage' => $completeness,
            'total_nodes' => $totalNodes,
            'registered_nodes' => $registeredCount,
            'unclaimed_nodes_count' => count($unclaimedLiving),
            'missing_ancestors_count' => max(0, 4 - count($elders)),
            'quests' => $quests,
        ];
    }

    /**
     * Build quest for an unclaimed relative already on the tree.
     *
     * @return array<string, mixed>
     */
    private static function buildUnclaimedNodeQuest(string $familyCode, string $userId, int $nodeId, string $relName): array
    {
        $token = InviteTokenService::create(
            $familyCode,
            [
                'node_id'    => $nodeId,
                'invited_by' => $userId,
            ],
            'organogram'
        );
        $inviteUrl = InviteTokenService::getRegisterUrl($token);

        $waText = "👋 Hi {$relName}! I've added you to our family tree on FamilyPlatform. Tap here to view where you sit on the tree and claim your profile: " . $inviteUrl;

        return [
            'id'            => 'unclaimed_node_' . $nodeId,
            'type'          => 'unclaimed_node',
            'node_id'       => $nodeId,
            'title'         => "Invite {$relName} to Claim Profile",
            'subtitle'      => "{$relName} is in the tree but hasn't activated their account yet.",
            'badge'         => '1-Tap Claim',
            'badge_color'   => 'success',
            'icon'          => 'bi-person-check-fill',
            'invite_url'    => $inviteUrl,
            'whatsapp_url'  => 'https://api.whatsapp.com/send?text=' . rawurlencode($waText),
            'whatsapp_text' => $waText,
        ];
    }

    /**
     * Build quest for missing elders / ancestors.
     *
     * @return array<string, mixed>
     */
    private static function buildAncestorsQuest(string $familyCode, string $userId, int $currentElders): array
    {
        $token = InviteTokenService::create(
            $familyCode,
            ['invited_by' => $userId],
            'organogram'
        );
        $inviteUrl = InviteTokenService::getRegisterUrl($token);

        $waText = "🌳 Hey family! We're building our ancestral lineage on FamilyPlatform. Can anyone with knowledge of our grandparents and great-grandparents help us complete the tree? Tap here to join: " . $inviteUrl;

        return [
            'id'            => 'missing_ancestors',
            'type'          => 'missing_ancestors',
            'node_id'       => null,
            'title'         => 'Unlock Ancestral Roots & Grandparents',
            'subtitle'      => "We only have {$currentElders} of 4 elder anchors recorded. Ask family elders on WhatsApp.",
            'badge'         => '+25% Tree Depth',
            'badge_color'   => 'warning',
            'icon'          => 'bi-hourglass-split',
            'invite_url'    => $inviteUrl,
            'whatsapp_url'  => 'https://api.whatsapp.com/send?text=' . rawurlencode($waText),
            'whatsapp_text' => $waText,
        ];
    }

    /**
     * Build quest for a missing maiden name.
     *
     * @return array<string, mixed>
     */
    private static function buildMaidenNameQuest(string $familyCode, string $userId, int $nodeId, string $targetName): array
    {
        $token = InviteTokenService::create(
            $familyCode,
            [
                'node_id'    => $nodeId,
                'invited_by' => $userId,
            ],
            'organogram'
        );
        $inviteUrl = InviteTokenService::getRegisterUrl($token);

        $waText = "🔍 Family trivia challenge! Does anyone know {$targetName}'s original maiden name / maternal roots? We're updating our official family tree here: " . $inviteUrl;

        return [
            'id'            => 'maiden_name_' . $nodeId,
            'type'          => 'missing_detail',
            'node_id'       => $nodeId,
            'title'         => "Discover {$targetName}'s Maiden Name",
            'subtitle'      => 'Preserve the maternal heritage branch and origin lore.',
            'badge'         => 'Heritage Mystery',
            'badge_color'   => 'info',
            'icon'          => 'bi-journal-bookmark-fill',
            'invite_url'    => $inviteUrl,
            'whatsapp_url'  => 'https://api.whatsapp.com/send?text=' . rawurlencode($waText),
            'whatsapp_text' => $waText,
        ];
    }

    /**
     * Build general family WhatsApp rally quest.
     *
     * @return array<string, mixed>
     */
    private static function buildRallyQuest(string $familyCode, string $userId, int $completeness, int $totalNodes): array
    {
        $token = InviteTokenService::create(
            $familyCode,
            ['invited_by' => $userId],
            'onboarding'
        );
        $inviteUrl = InviteTokenService::getRegisterUrl($token);

        $waText = "🎉 Our family tree on FamilyPlatform is now {$completeness}% complete with {$totalNodes} relatives connected! Join the family sanctuary, view our lineage, and share memories: " . $inviteUrl;

        return [
            'id'            => 'family_rally',
            'type'          => 'family_rally',
            'node_id'       => null,
            'title'         => 'Rally the Entire Family Group',
            'subtitle'      => "Drop the {$completeness}% tree update into your WhatsApp group to invite all branches.",
            'badge'         => 'Viral Flywheel',
            'badge_color'   => 'primary',
            'icon'          => 'bi-megaphone-fill',
            'invite_url'    => $inviteUrl,
            'whatsapp_url'  => 'https://api.whatsapp.com/send?text=' . rawurlencode($waText),
            'whatsapp_text' => $waText,
        ];
    }

    /**
     * Fallback quest when tree has 0 nodes.
     *
     * @return array<string, mixed>
     */
    private static function buildSeedQuest(string $familyCode, string $userId): array
    {
        $token = InviteTokenService::create(
            $familyCode,
            ['invited_by' => $userId],
            'onboarding'
        );
        $inviteUrl = InviteTokenService::getRegisterUrl($token);
        $waText = "🌳 Let's build our official family tree together on FamilyPlatform! Join me here: " . $inviteUrl;

        return [
            'id'            => 'seed_quest',
            'type'          => 'seed',
            'node_id'       => null,
            'title'         => 'Kickstart Your Family Tree',
            'subtitle'      => 'Add your parents and immediate relatives to anchor your dynasty.',
            'badge'         => 'First Step',
            'badge_color'   => 'primary',
            'icon'          => 'bi-tree-fill',
            'invite_url'    => $inviteUrl,
            'whatsapp_url'  => 'https://api.whatsapp.com/send?text=' . rawurlencode($waText),
            'whatsapp_text' => $waText,
        ];
    }
}
