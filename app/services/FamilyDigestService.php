<?php

declare(strict_types=1);

namespace App\services;

use App\services\FamilyTriviaService;
use App\services\LineageQuestService;
use App\services\MemoryMilestoneService;
use Src\Db;
use Src\functionality\SendEmailFunctionality;
use PDO;

/**
 * FamilyDigestService
 *
 * Powers the Sunday Family Sanctuary Digest.
 * Aggregates Sunday Trivia, upcoming birthdays/anniversaries in the next 7 days,
 * and lineage quests into a personalized weekly dispatch that brings inactive
 * family members back to the platform.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FamilyDigestService
{
    /**
     * Build the personalized digest payload for a family member.
     *
     * @param string $familyCode
     * @param string $userId
     * @return array{
     *     trivia: array<string, mixed>|null,
     *     milestones: array<int, array<string, mixed>>,
     *     quest: array<string, mixed>|null
     * }
     */
    public static function buildDigestForMember(string $familyCode, string $userId): array
    {
        // 1. Ensure Sunday Trivia exists for this week
        if (!FamilyTriviaService::hasActiveWeeklyTrivia($familyCode)) {
            FamilyTriviaService::generateWeeklyHeritageTrivia($familyCode, $userId);
        }

        $db = Db::connect2();
        $stmt = $db->prepare(
            "SELECT p.post_no, pp.question
             FROM post p
             JOIN post_polls pp ON pp.post_no = p.post_no
             WHERE (p.postFamCode = ? OR REPLACE(p.postFamCode, '#', '') = REPLACE(?, '#', ''))
               AND p.postMessage LIKE '%Weekly Family Trivia%'
               AND p.date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY p.post_no DESC
             LIMIT 1"
        );
        $stmt->execute([$familyCode, $familyCode]);
        /** @var array<string, mixed>|false $triviaRow */
        $triviaRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $trivia = $triviaRow ? [
            'post_no'  => (int)$triviaRow['post_no'],
            'question' => (string)$triviaRow['question'],
        ] : null;

        // 2. Fetch upcoming celebrations (next 7 days)
        $memoryService = new MemoryMilestoneService();
        $milestones = $memoryService->getUpcomingMilestones($userId, [$familyCode], 7);

        // 3. Fetch top lineage quest
        $questsData = LineageQuestService::getFamilyQuests($familyCode, $userId);
        $topQuest = !empty($questsData['quests']) ? $questsData['quests'][0] : null;

        return [
            'trivia'     => $trivia,
            'milestones' => array_slice($milestones, 0, 3),
            'quest'      => $topQuest,
        ];
    }

    /**
     * Dispatch the Sunday Digest across all eligible registered members.
     *
     * @param bool $dryRun If true, computes matches without firing outbound SMTP
     * @return array{families_scanned: int, members_notified: int, dry_run: bool}
     */
    public static function dispatchSundayDigest(bool $dryRun = false): array
    {
        $db = Db::connect2();

        $stmt = $db->query(
            'SELECT DISTINCT a.email, p.famCode, p.firstName, p.lastName, a.id
             FROM account a
             INNER JOIN personal p ON a.id = p.id
             WHERE a.email IS NOT NULL 
               AND a.email != "" 
               AND p.famCode IS NOT NULL 
               AND p.famCode != ""'
        );
        /** @var array<int, array<string, mixed>> $members */
        $members = ($stmt instanceof \PDOStatement) ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        $sentCount = 0;
        $familiesSeen = [];

        foreach ($members as $member) {
            $userId = (string)($member['id'] ?? '');
            $famCode = (string)($member['famCode'] ?? '');
            $email = trim((string)($member['email'] ?? ''));

            if ($userId === '' || $famCode === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $familiesSeen[$famCode] = true;
            $digest = self::buildDigestForMember($famCode, $userId);

            $payload = [
                'id'         => $userId,
                'firstName'  => (string)($member['firstName'] ?? 'Family Member'),
                'lastName'   => (string)($member['lastName'] ?? ''),
                'email'      => $email,
                'famCode'    => $famCode,
                'trivia'     => $digest['trivia'],
                'milestones' => $digest['milestones'],
                'quest'      => $digest['quest'],
            ];

            if (!$dryRun) {
                try {
                    SendEmailFunctionality::email(
                        'msg/lifecycle/sunday_family_digest',
                        'Your Sunday Family Sanctuary Digest & Trivia',
                        $payload,
                        'member'
                    );
                    $sentCount++;
                } catch (\Throwable $e) {
                    error_log('[FamilyDigestService] Email send failed for ' . $userId . ': ' . $e->getMessage());
                }
            } else {
                $sentCount++;
            }
        }

        return [
            'families_scanned' => count($familiesSeen),
            'members_notified' => $sentCount,
            'dry_run'          => $dryRun,
        ];
    }
}
