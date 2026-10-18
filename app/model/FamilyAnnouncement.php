<?php
declare(strict_types=1);

namespace App\model;

use Src\Select;

final class FamilyAnnouncement extends Select
{
    /**
     * Retrieve active announcements for a family group
     *
     * @param string $familyCode
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public static function getActiveAnnouncements(string $familyCode, int $limit = 5): array
    {
        $query = "SELECT fa.*, p.firstName, p.lastName, pp.img AS author_avatar
                  FROM family_announcements fa
                  LEFT JOIN personal p ON fa.author_id = p.id
                  LEFT JOIN profilePics pp ON fa.author_id = pp.id
                  WHERE fa.family_code = ? 
                    AND (fa.expires_at IS NULL OR fa.expires_at >= CURDATE())
                  ORDER BY fa.is_pinned DESC, 
                           CASE fa.priority WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END ASC,
                           fa.created_at DESC
                  LIMIT " . (int)$limit;

        /** @var array<int, array<string, mixed>> $result */
        $result = parent::selectFn2(query: $query, bind: [$familyCode]);
        return $result;
    }

    /**
     * Create a new family announcement
     *
     * @param array<string, mixed> $data
     * @return int
     */
    public static function createAnnouncement(array $data): int
    {
        $db = self::connect2();
        $stmt = $db->prepare("
            INSERT INTO family_announcements (family_code, author_id, title, message, priority, type, is_pinned, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['family_code'],
            $data['author_id'],
            $data['title'],
            $data['message'],
            $data['priority'] ?? 'important',
            $data['type'] ?? 'general',
            $data['is_pinned'] ?? 1,
            $data['expires_at'] ?? null
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Delete an announcement with family isolation and author check
     *
     * @param int|string $id
     * @param string $familyCode
     * @param string $authorId
     * @param bool $isAdmin
     * @return bool
     */
    public static function deleteAnnouncement(int|string $id, string $familyCode, string $authorId, bool $isAdmin = false): bool
    {
        $db = self::connect2();
        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM family_announcements WHERE id = ? AND family_code = ?");
            $stmt->execute([(string)$id, $familyCode]);
            return $stmt->rowCount() > 0;
        }

        $stmt = $db->prepare("DELETE FROM family_announcements WHERE id = ? AND family_code = ? AND author_id = ?");
        $stmt->execute([(string)$id, $familyCode, $authorId]);
        return $stmt->rowCount() > 0;
    }
}
