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
    /**
     * Return the ID of an identical entry created by the same author in the last 30s (double-submit guard), or null.
     */
    public static function findRecentDuplicate(string $familyCode, string $authorId, string $title): ?int
    {
        $stmt = self::connect2()->prepare("
            SELECT id FROM family_journals
            WHERE family_code = ? AND author_id = ? AND title = ?
              AND created_at >= (NOW() - INTERVAL 30 SECOND)
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$familyCode, $authorId, $title]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    /**
     * @param array<string, mixed> $data
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
            $deleted = $stmt->rowCount() > 0;
            if ($deleted) {
                $db->prepare("DELETE FROM family_journal_contributors WHERE journal_id = ?")->execute([(string)$id]);
            }
            return $deleted;
        }

        $stmt = $db->prepare("DELETE FROM family_journals WHERE id = ? AND family_code = ? AND author_id = ?");
        $stmt->execute([(string)$id, $familyCode, $authorId]);
        $deleted = $stmt->rowCount() > 0;
        if ($deleted) {
            $db->prepare("DELETE FROM family_journal_contributors WHERE journal_id = ?")->execute([(string)$id]);
        }
        return $deleted;
    }

    /**
     * @param array<string, mixed> $journal
     */
    public static function canManage(array $journal, string $userId, bool $isAdmin): bool
    {
        return $isAdmin || (string)($journal['author_id'] ?? '') === $userId;
    }

    /**
     * Author, admin or co-contributor may edit.
     *
     * @param array<string, mixed> $journal
     */
    public static function canEdit(array $journal, string $userId, bool $isAdmin): bool
    {
        if (self::canManage($journal, $userId, $isAdmin)) {
            return true;
        }
        return in_array($userId, self::getContributorIds((int)($journal['id'] ?? 0)), true);
    }

    /**
     * @return array<int, string> journal IDs the user co-contributes to
     */
    public static function getContributedJournalIds(string $userId): array
    {
        $stmt = self::connect2()->prepare("SELECT journal_id FROM family_journal_contributors WHERE user_id = ?");
        $stmt->execute([$userId]);
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }

    /**
     * @return array<int, string>
     */
    public static function getContributorIds(int $journalId): array
    {
        $stmt = self::connect2()->prepare("SELECT user_id FROM family_journal_contributors WHERE journal_id = ?");
        $stmt->execute([$journalId]);
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getContributors(int $journalId): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = parent::selectFn2(
            query: "SELECT p.id, p.firstName, p.lastName
                    FROM family_journal_contributors c
                    JOIN personal p ON p.id = c.user_id
                    WHERE c.journal_id = ?
                    ORDER BY c.created_at",
            bind: [$journalId]
        );
        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getFamilyMembers(string $familyCode): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = parent::selectFn2(
            query: "SELECT id, firstName, lastName FROM personal WHERE famCode = ? ORDER BY firstName, lastName",
            bind: [$familyCode]
        );
        // personal can hold duplicate rows per member; dedupe in the application layer
        $unique = [];
        foreach ($rows as $row) {
            $unique[(string)$row['id']] ??= $row;
        }
        return array_values($unique);
    }

    /**
     * Replace the co-contributor set. Only members of the same family (and never the author) are kept.
     *
     * @param array<int, string> $userIds
     */
    public static function setContributors(int $journalId, string $familyCode, string $authorId, array $userIds, string $addedBy): void
    {
        $db = self::connect2();
        $allowed = array_column(self::getFamilyMembers($familyCode), 'id');
        $allowed = array_map('strval', $allowed);
        $keep = array_values(array_unique(array_filter(
            $userIds,
            static fn (string $id): bool => $id !== $authorId && in_array($id, $allowed, true)
        )));

        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM family_journal_contributors WHERE journal_id = ?")->execute([$journalId]);
            $ins = $db->prepare("INSERT INTO family_journal_contributors (journal_id, user_id, added_by) VALUES (?, ?, ?)");
            foreach ($keep as $uid) {
                $ins->execute([$journalId, $uid, $addedBy]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function updateJournal(int $id, string $familyCode, array $data): void
    {
        $coverSql = '';
        $bind = [
            $data['title'], $data['summary'] ?? null, $data['content'],
            $data['category'] ?? 'Milestone', $data['mood'] ?? 'heartwarming', $data['status'] ?? 'published',
        ];
        if (!empty($data['cover_image'])) {
            $coverSql = ', cover_image = ?';
            $bind[] = $data['cover_image'];
        } elseif (!empty($data['remove_cover'])) {
            $coverSql = ', cover_image = NULL';
        }
        $bind[] = $id;
        $bind[] = $familyCode;

        self::connect2()->prepare(
            "UPDATE family_journals SET title = ?, summary = ?, content = ?, category = ?, mood = ?, status = ?{$coverSql}
             WHERE id = ? AND family_code = ?"
        )->execute($bind);
    }
}
