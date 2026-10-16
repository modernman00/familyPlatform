<?php

declare(strict_types=1);

namespace App\services;

use App\model\PollData;
use App\model\Post;
use Src\Db;
use PDO;

/**
 * FamilyTriviaService
 *
 * Powers Sunday Family Trivia & "Guess Who" challenges for FamilyPlatform.
 * Generates ego-centric family trivia from ancestral records, creates weekly polls,
 * and sparks multi-generational conversation across the family network.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FamilyTriviaService
{
    /**
     * Check if an automated weekly trivia has already been published for this family in the last 7 days.
     */
    public static function hasActiveWeeklyTrivia(string $familyCode): bool
    {
        $db = Db::connect2();
        $stmt = $db->prepare(
            "SELECT post_no 
             FROM post 
             WHERE postFamCode = ? 
               AND postMessage LIKE '%Weekly Family Trivia%' 
               AND date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
             LIMIT 1"
        );
        $stmt->execute([$familyCode]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Generate and publish a dynamic heritage trivia challenge for a family.
     *
     * @param string $familyCode Target family code
     * @param string|null $actorUserId Optional user ID initiating or authoring
     * @return array{
     *     post_no: int,
     *     question: string,
     *     options: array<int, string>,
     *     category: string
     * }|null Returns null if an active trivia already exists
     */
    public static function generateWeeklyHeritageTrivia(string $familyCode, ?string $actorUserId = null): ?array
    {
        if (self::hasActiveWeeklyTrivia($familyCode)) {
            return null;
        }

        $db = Db::connect2();

        // 1. Fetch family nodes to formulate ground-truth questions
        $stmt = $db->prepare(
            'SELECT id, first_name, last_name, maiden_name, gender, birth_date, birth_year, birth_place, generation_level
             FROM family_nodes
             WHERE family_code = ?
             ORDER BY generation_level ASC, id ASC'
        );
        $stmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $nodes */
        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $triviaData = self::synthesizeTriviaQuestion($familyCode, $nodes);

        // 2. Insert the post into the family feed
        $authorId = !empty($actorUserId) ? $actorUserId : 'HERITAGE_BOT';
        $authorName = 'FamilyPlatform Heritage';
        $postMessage = "🧠 Weekly Family Trivia Challenge!\n\n" . $triviaData['question'] . "\n\nCast your vote below and share your family memories in the comments!";

        $postStmt = $db->prepare(
            'INSERT INTO post (id, fullName, postMessage, postFamCode, post_time, post_status, date_created)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $postTime = (string)milliSeconds();
        $postStmt->execute([
            $authorId,
            $authorName,
            $postMessage,
            $familyCode,
            $postTime,
            'publish',
        ]);

        $postNo = (int)$db->lastInsertId();

        // 3. Attach the poll with options
        PollData::createPoll($postNo, $triviaData['question'], $triviaData['options']);

        // Explicitly update status as published
        Post::updatePostByStatusAsPublished($postNo);

        return [
            'post_no'  => $postNo,
            'question' => $triviaData['question'],
            'options'  => $triviaData['options'],
            'category' => $triviaData['category'],
        ];
    }

    /**
     * Synthesize an authentic trivia question from family nodes.
     *
     * @param string $familyCode
     * @param array<int, array<string, mixed>> $nodes
     * @return array{question: string, options: array<int, string>, category: string}
     */
    private static function synthesizeTriviaQuestion(string $familyCode, array $nodes): array
    {
        // Category A: Birth Decade of an Elder / Ancestor
        foreach ($nodes as $node) {
            $year = 0;
            if (!empty($node['birth_year'])) {
                $year = (int)$node['birth_year'];
            } elseif (!empty($node['birth_date']) && is_string($node['birth_date'])) {
                $ts = strtotime($node['birth_date']);
                if ($ts !== false) {
                    $year = (int)date('Y', $ts);
                }
            }

            if ($year >= 1880 && $year <= 2010) {
                $decade = (int)(floor($year / 10) * 10);
                $relName = trim(($node['first_name'] ?? '') . ' ' . ($node['last_name'] ?? ''));

                $options = [
                    $decade . 's',
                    ($decade - 10) . 's',
                    ($decade + 10) . 's',
                    ($decade + 20) . 's',
                ];
                shuffle($options);

                return [
                    'question' => "In which decade was {$relName} born?",
                    'options'  => array_values(array_unique($options)),
                    'category' => 'ancestral_decade',
                ];
            }
        }

        // Category B: Ancestral Birth Place
        foreach ($nodes as $node) {
            $place = trim((string)($node['birth_place'] ?? ''));
            if ($place !== '' && strlen($place) >= 3) {
                $relName = trim(($node['first_name'] ?? '') . ' ' . ($node['last_name'] ?? ''));
                $distractors = ['London, UK', 'Lagos, Nigeria', 'Manchester, UK', 'Ibadan, Nigeria'];
                $options = array_slice(array_unique(array_merge([$place], $distractors)), 0, 4);
                shuffle($options);

                return [
                    'question' => "Where was {$relName}'s ancestral birthplace recorded?",
                    'options'  => $options,
                    'category' => 'ancestral_place',
                ];
            }
        }

        // Category C: Multi-Generational Depth
        $generations = [];
        foreach ($nodes as $node) {
            $gen = (int)($node['generation_level'] ?? 0);
            $generations[$gen] = true;
        }
        $genCount = count($generations);
        if ($genCount >= 2) {
            $options = [
                (string)$genCount . ' Generations',
                (string)max(1, $genCount - 1) . ' Generations',
                (string)($genCount + 1) . ' Generations',
                (string)($genCount + 2) . ' Generations',
            ];
            shuffle($options);

            return [
                'question' => "How many generations are currently recorded on our family tree?",
                'options'  => array_values(array_unique($options)),
                'category' => 'generation_depth',
            ];
        }

        // Category D: Fallback Clan Lore
        return [
            'question' => "Which family branch has the longest roots on our {$familyCode} tree?",
            'options'  => [
                'Paternal Ancestors',
                'Maternal Ancestors',
                'The Younger Generation',
                'Still Documenting!',
            ],
            'category' => 'clan_lore',
        ];
    }
}
