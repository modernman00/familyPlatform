<?php
declare(strict_types=1);

namespace App\model;

use Src\Select;
use Src\Insert;
use Src\Update;
use Src\Delete;

final class FamilyJournal extends Select
{
    /**
     * Retrieve journals for a specific family group
     *
     * @param string $familyCode
     * @param int $limit
     * @param int $offset
     * @return array<int, array<string, mixed>>
     */
    public static function getFamilyJournals(string $familyCode, int $limit = 20, int $offset = 0): array
    {
        $query = "SELECT fj.*, p.firstName, p.lastName, pp.img AS author_avatar
                  FROM family_journals fj
                  LEFT JOIN personal p ON fj.author_id = p.id
                  LEFT JOIN profilePics pp ON fj.author_id = pp.id
                  WHERE fj.family_code = ? AND fj.status = 'published'
                  ORDER BY fj.created_at DESC
                  LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        /** @var array<int, array<string, mixed>> $result */
        $result = parent::selectFn2(query: $query, bind: [$familyCode]);
        return $result;
    }

    /**
     * Retrieve a single journal by ID with family code isolation check
     *
     * @param int|string $id
     * @param string $familyCode
     * @return array<string, mixed>|null
     */
    public static function getJournalById(int|string $id, string $familyCode): ?array
    {
        $query = "SELECT fj.*, p.firstName, p.lastName, pp.img AS author_avatar
                  FROM family_journals fj
                  LEFT JOIN personal p ON fj.author_id = p.id
                  LEFT JOIN profilePics pp ON fj.author_id = pp.id
                  WHERE fj.id = ? AND fj.family_code = ?
                  LIMIT 1";

        /** @var array<int, array<string, mixed>> $result */
        $result = parent::selectFn2(query: $query, bind: [(string)$id, $familyCode]);
        return $result[0] ?? null;
    }

    /**
     * Create a new family journal entry
     *
     * @param array<string, mixed> $data
     * @return int
     */
    public static function createJournal(array $data): int
    {
        $db = self::connect2();
        $stmt = $db->prepare("
            INSERT INTO family_journals (family_code, author_id, title, slug, summary, content, cover_image, category, mood, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['family_code'],
            $data['author_id'],
            $data['title'],
            $data['slug'],
            $data['summary'] ?? null,
            $data['content'],
            $data['cover_image'] ?? null,
            $data['category'] ?? 'Milestone',
            $data['mood'] ?? 'heartwarming',
            $data['status'] ?? 'published'
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Delete a family journal entry with author or admin authorization check
     *
     * @param int|string $id
     * @param string $familyCode
     * @param string $authorId
     * @param bool $isAdmin
     * @return bool
     */
    public static function deleteJournal(int|string $id, string $familyCode, string $authorId, bool $isAdmin = false): bool
    {
        $db = self::connect2();
        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM family_journals WHERE id = ? AND family_code = ?");
            $stmt->execute([(string)$id, $familyCode]);
            return $stmt->rowCount() > 0;
        }

        $stmt = $db->prepare("DELETE FROM family_journals WHERE id = ? AND family_code = ? AND author_id = ?");
        $stmt->execute([(string)$id, $familyCode, $authorId]);
        return $stmt->rowCount() > 0;
    }
}
