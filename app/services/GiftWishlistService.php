<?php

declare(strict_types=1);

namespace App\services;

use InvalidArgumentException;
use PDO;
use Src\Db;

/**
 * FamilyPlatform Pinned Gift Wishlist Service.
 * Allows family members to post gift ideas & store links for Christmas, Birthdays, etc.
 * Posts remain pinned at the top for the initiator-chosen number of days (1..90).
 */
final class GiftWishlistService
{
    public const OCCASIONS = [
        'Christmas',
        'Birthday',
        'Anniversary',
        'Baby Shower',
        'Housewarming',
        'Wedding',
        'Other',
    ];

    private const MAX_PIN_DAYS = 90;
    private const MAX_ITEMS_PER_WISHLIST = 15;

    private bool $schemaEnsured = false;

    public function __construct(private ?PDO $db = null)
    {
    }

    private function db(): PDO
    {
        $pdo = $this->db ??= Db::connect2();
        if (!$this->schemaEnsured) {
            self::ensureSchema($pdo);
            $this->schemaEnsured = true;
        }
        return $pdo;
    }

    /**
     * Ensure wishlist tables and expected columns exist on both local and live environments.
     */
    public static function ensureSchema(PDO $pdo): void
    {
        try {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS `family_gift_wishlists` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` VARCHAR(255) NOT NULL,
                    `author_name` VARCHAR(120) NOT NULL,
                    `family_code` VARCHAR(64) NOT NULL,
                    `occasion` VARCHAR(60) NOT NULL DEFAULT \'Christmas\',
                    `title` VARCHAR(160) NOT NULL,
                    `notes` VARCHAR(500) NULL DEFAULT NULL,
                    `pin_days` INT UNSIGNED NOT NULL DEFAULT 14,
                    `pinned_until` DATETIME NOT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_fgw_fam_pin` (`family_code`, `pinned_until`, `created_at`),
                    KEY `idx_fgw_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS `family_gift_wishlist_items` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `wishlist_id` BIGINT UNSIGNED NOT NULL,
                    `item_title` VARCHAR(160) NOT NULL,
                    `item_url` VARCHAR(500) NULL DEFAULT NULL,
                    `approx_price` DECIMAL(10,2) NULL DEFAULT NULL,
                    `currency` VARCHAR(10) NOT NULL DEFAULT \'£\',
                    `claimed_by_user_id` VARCHAR(255) NULL DEFAULT NULL,
                    `claimed_by_name` VARCHAR(120) NULL DEFAULT NULL,
                    `claimed_at` DATETIME NULL DEFAULT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_fgwi_wishlist` (`wishlist_id`),
                    CONSTRAINT `fk_fgwi_wishlist` FOREIGN KEY (`wishlist_id`) REFERENCES `family_gift_wishlists` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $wStmt = $pdo->query('SHOW COLUMNS FROM `family_gift_wishlists`');
            $wishlistCols = $wStmt !== false ? ($wStmt->fetchAll(PDO::FETCH_COLUMN) ?: []) : [];
            $wishlistAdditions = [
                'author_name' => 'ALTER TABLE `family_gift_wishlists` ADD COLUMN `author_name` VARCHAR(120) NOT NULL DEFAULT \'Family Member\'',
                'occasion' => 'ALTER TABLE `family_gift_wishlists` ADD COLUMN `occasion` VARCHAR(60) NOT NULL DEFAULT \'Christmas\'',
                'notes' => 'ALTER TABLE `family_gift_wishlists` ADD COLUMN `notes` VARCHAR(500) NULL DEFAULT NULL',
                'pin_days' => 'ALTER TABLE `family_gift_wishlists` ADD COLUMN `pin_days` INT UNSIGNED NOT NULL DEFAULT 14',
                'pinned_until' => 'ALTER TABLE `family_gift_wishlists` ADD COLUMN `pinned_until` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ];
            foreach ($wishlistAdditions as $col => $alterSql) {
                if (!in_array($col, $wishlistCols, true)) {
                    $pdo->exec($alterSql);
                }
            }

            $iStmt = $pdo->query('SHOW COLUMNS FROM `family_gift_wishlist_items`');
            $itemCols = $iStmt !== false ? ($iStmt->fetchAll(PDO::FETCH_COLUMN) ?: []) : [];
            $itemAdditions = [
                'item_url' => 'ALTER TABLE `family_gift_wishlist_items` ADD COLUMN `item_url` VARCHAR(500) NULL DEFAULT NULL',
                'approx_price' => 'ALTER TABLE `family_gift_wishlist_items` ADD COLUMN `approx_price` DECIMAL(10,2) NULL DEFAULT NULL',
                'currency' => 'ALTER TABLE `family_gift_wishlist_items` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT \'£\'',
                'claimed_by_user_id' => 'ALTER TABLE `family_gift_wishlist_items` ADD COLUMN `claimed_by_user_id` VARCHAR(255) NULL DEFAULT NULL',
                'claimed_by_name' => 'ALTER TABLE `family_gift_wishlist_items` ADD COLUMN `claimed_by_name` VARCHAR(120) NULL DEFAULT NULL',
                'claimed_at' => 'ALTER TABLE `family_gift_wishlist_items` ADD COLUMN `claimed_at` DATETIME NULL DEFAULT NULL',
            ];
            foreach ($itemAdditions as $col => $alterSql) {
                if (!in_array($col, $itemCols, true)) {
                    $pdo->exec($alterSql);
                }
            }
        } catch (\Throwable $e) {
            error_log('[GiftWishlistService::ensureSchema] ' . $e->getMessage());
        }
    }

    /**
     * Create a new pinned gift wishlist for a family member.
     *
     * @param string $userId
     * @param string $authorName
     * @param string $familyCode
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function createWishlist(string $userId, string $authorName, string $familyCode, array $input): array
    {
        $cleanUserId = trim($userId);
        $cleanFamCode = trim($familyCode);
        if ($cleanUserId === '' || $cleanFamCode === '') {
            throw new InvalidArgumentException('Valid member and family context are required.');
        }

        $cleanAuthor = trim(strip_tags($authorName));
        if ($cleanAuthor === '') {
            $cleanAuthor = 'Family Member';
        }
        $cleanAuthor = mb_substr($cleanAuthor, 0, 120);

        $occasion = trim(strip_tags((string) ($input['occasion'] ?? 'Christmas')));
        if (!in_array($occasion, self::OCCASIONS, true)) {
            $occasion = 'Other';
        }

        $title = trim(strip_tags((string) ($input['title'] ?? '')));
        if ($title === '') {
            $title = "{$cleanAuthor}'s {$occasion} Gift Wishlist";
        }
        $title = mb_substr($title, 0, 160);

        $rawNotes = trim(strip_tags((string) ($input['notes'] ?? '')));
        $notes = $rawNotes !== '' ? mb_substr($rawNotes, 0, 500) : null;

        $pinDays = (int) ($input['pin_days'] ?? 14);
        if ($pinDays < 1) {
            $pinDays = 1;
        } elseif ($pinDays > self::MAX_PIN_DAYS) {
            $pinDays = self::MAX_PIN_DAYS;
        }

        $defaultCurrency = $this->sanitizeCurrency((string) ($input['currency'] ?? '£'));
        $rawItems = is_array($input['items'] ?? null) ? $input['items'] : [];
        $sanitizedItems = [];

        foreach (array_slice($rawItems, 0, self::MAX_ITEMS_PER_WISHLIST) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemTitle = trim(strip_tags((string) ($item['item_title'] ?? $item['title'] ?? '')));
            if ($itemTitle === '') {
                continue;
            }
            $itemTitle = mb_substr($itemTitle, 0, 160);
            $itemUrl = $this->sanitizeHttpUrl((string) ($item['item_url'] ?? $item['url'] ?? ''));
            $rawPrice = $item['approx_price'] ?? $item['price'] ?? null;
            $approxPrice = ($rawPrice !== null && $rawPrice !== '' && is_numeric($rawPrice) && (float) $rawPrice > 0)
                ? round((float) $rawPrice, 2)
                : null;

            $sanitizedItems[] = [
                'item_title' => $itemTitle,
                'item_url' => $itemUrl,
                'approx_price' => $approxPrice,
                'currency' => $this->sanitizeCurrency((string) ($item['currency'] ?? $defaultCurrency)),
            ];
        }

        if ($sanitizedItems === [] && $notes === null) {
            throw new InvalidArgumentException('Please add at least one gift idea or store link.');
        }

        $pinnedUntil = (new \DateTimeImmutable('now'))
            ->modify('+' . $pinDays . ' days')
            ->format('Y-m-d H:i:s');

        $stmt = $this->db()->prepare(
            'INSERT INTO family_gift_wishlists
                (user_id, author_name, family_code, occasion, title, notes, pin_days, pinned_until)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $cleanUserId,
            $cleanAuthor,
            $cleanFamCode,
            $occasion,
            $title,
            $notes,
            $pinDays,
            $pinnedUntil,
        ]);

        $wishlistId = (int) $this->db()->lastInsertId();

        if ($sanitizedItems !== []) {
            $itemStmt = $this->db()->prepare(
                'INSERT INTO family_gift_wishlist_items
                    (wishlist_id, item_title, item_url, approx_price, currency)
                 VALUES
                    (?, ?, ?, ?, ?)'
            );
            foreach ($sanitizedItems as $it) {
                $itemStmt->execute([
                    $wishlistId,
                    $it['item_title'],
                    $it['item_url'],
                    $it['approx_price'],
                    $it['currency'],
                ]);
            }
        }

        return [
            'id' => $wishlistId,
            'user_id' => $cleanUserId,
            'author_name' => $cleanAuthor,
            'family_code' => $cleanFamCode,
            'occasion' => $occasion,
            'title' => $title,
            'notes' => $notes,
            'pin_days' => $pinDays,
            'pinned_until' => $pinnedUntil,
            'items' => $sanitizedItems,
        ];
    }

    /**
     * List wishlists visible to the member's family codes.
     * Active pinned wishlists (pinned_until > NOW()) always persist at the top.
     *
     * @param array<int, string> $famCodes
     * @return array<int, array<string, mixed>>
     */
    public function listWishlistsForFamilyCodes(array $famCodes, ?string $occasionFilter = null): array
    {
        $codes = array_values(array_unique(array_filter(array_map('strval', $famCodes), static fn (string $c): bool => $c !== '')));
        if ($codes === []) {
            return [];
        }

        $marks = implode(',', array_fill(0, count($codes), '?'));
        $sql = sprintf(
            'SELECT id, user_id, author_name, family_code, occasion, title, notes, pin_days, pinned_until, created_at,
                    (pinned_until > NOW()) AS is_pinned,
                    GREATEST(0, TIMESTAMPDIFF(DAY, NOW(), pinned_until) + 1) AS days_remaining
             FROM family_gift_wishlists
             WHERE family_code IN (%s)',
            $marks
        );
        $params = $codes;

        if ($occasionFilter !== null && $occasionFilter !== '' && in_array($occasionFilter, self::OCCASIONS, true)) {
            $sql .= ' AND occasion = ?';
            $params[] = $occasionFilter;
        }

        $sql .= ' ORDER BY (pinned_until > NOW()) DESC, pinned_until DESC, created_at DESC LIMIT 80';

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $wishlists = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if ($wishlists === []) {
            return [];
        }

        $ids = array_map(static fn (array $w): int => (int) $w['id'], $wishlists);
        $idMarks = implode(',', array_fill(0, count($ids), '?'));
        $itemSql = sprintf(
            'SELECT * FROM family_gift_wishlist_items WHERE wishlist_id IN (%s) ORDER BY (claimed_at IS NOT NULL) ASC, id ASC',
            $idMarks
        );
        $itemStmt = $this->db()->prepare($itemSql);
        $itemStmt->execute($ids);
        $allItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $byWishlist = [];
        foreach ($allItems as $it) {
            $wid = (int) ($it['wishlist_id'] ?? 0);
            $byWishlist[$wid][] = $it;
        }

        foreach ($wishlists as &$w) {
            $w['is_pinned'] = (int) ($w['is_pinned'] ?? 0) === 1;
            $w['days_remaining'] = $w['is_pinned'] ? max(1, (int) ($w['days_remaining'] ?? 1)) : 0;
            $w['items'] = $byWishlist[(int) $w['id']] ?? [];
        }
        unset($w);

        return $wishlists;
    }

    /**
     * Atomically claim a wishlist item (scoped to the member's family codes).
     *
     * @param array<int, string> $famCodes
     * @return array<string, mixed>
     */
    public function claimItem(
        int $itemId,
        array $famCodes,
        string $claimerUserId,
        string $claimerName,
        bool $anonymous = false
    ): array {
        $codes = array_values(array_unique(array_filter(array_map('strval', $famCodes), static fn (string $c): bool => $c !== '')));
        if ($itemId <= 0 || $codes === []) {
            throw new InvalidArgumentException('Wishlist item not found.');
        }

        $marks = implode(',', array_fill(0, count($codes), '?'));
        $checkSql = sprintf(
            'SELECT i.id, i.claimed_at
             FROM family_gift_wishlist_items i
             INNER JOIN family_gift_wishlists w ON w.id = i.wishlist_id
             WHERE i.id = ? AND w.family_code IN (%s)
             LIMIT 1',
            $marks
        );
        $check = $this->db()->prepare($checkSql);
        $check->execute(array_merge([$itemId], $codes));
        $existing = $check->fetch(PDO::FETCH_ASSOC);
        if (!is_array($existing)) {
            throw new InvalidArgumentException('Wishlist item not found in your family.');
        }

        $storedName = $anonymous
            ? 'Secret Family Member'
            : (trim(strip_tags($claimerName)) !== '' ? mb_substr(trim(strip_tags($claimerName)), 0, 120) : 'Family Member');

        $upd = $this->db()->prepare(
            'UPDATE family_gift_wishlist_items
             SET claimed_by_user_id = ?, claimed_by_name = ?, claimed_at = NOW()
             WHERE id = ? AND claimed_at IS NULL'
        );
        $upd->execute([$claimerUserId, $storedName, $itemId]);

        if ($upd->rowCount() === 0) {
            throw new InvalidArgumentException('Someone has already claimed this gift!');
        }

        $fetch = $this->db()->prepare('SELECT * FROM family_gift_wishlist_items WHERE id = ? LIMIT 1');
        $fetch->execute([$itemId]);
        $row = $fetch->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : [];
    }

    /**
     * Publish an automatic announcement post to the family feed when a wishlist is created.
     *
     * @param array<string, mixed> $wishlist
     */
    public function publishFeedPost(string $userId, string $authorName, string $familyCode, array $wishlist): ?int
    {
        try {
            $pdo = $this->db();
            $uStmt = $pdo->prepare('SELECT img FROM profilePics WHERE id = ? LIMIT 1');
            $uStmt->execute([$userId]);
            $profileImg = (string) ($uStmt->fetchColumn() ?: 'default.png');

            $occasion = (string) ($wishlist['occasion'] ?? 'Gift');
            $title = (string) ($wishlist['title'] ?? "{$authorName}'s Wishlist");
            $pinDays = (int) ($wishlist['pin_days'] ?? 14);
            $wishlistId = (int) ($wishlist['id'] ?? 0);
            $items = is_array($wishlist['items'] ?? null) ? $wishlist['items'] : [];

            $lines = ["🎁 New {$occasion} Gift Wishlist: {$title}"];
            $previewCount = 0;
            foreach ($items as $it) {
                if ($previewCount >= 5) {
                    break;
                }
                $itemTitle = trim((string) ($it['item_title'] ?? ''));
                if ($itemTitle === '') {
                    continue;
                }
                $pricePart = '';
                if (!empty($it['approx_price'])) {
                    $curr = (string) ($it['currency'] ?? '£');
                    $pricePart = ' (' . $curr . number_format((float) $it['approx_price'], 2) . ')';
                }
                $lines[] = "• {$itemTitle}{$pricePart}";
                $previewCount++;
            }
            if (count($items) > 5) {
                $more = count($items) - 5;
                $lines[] = "• …and {$more} more gift " . ($more === 1 ? 'idea' : 'ideas');
            }
            $lines[] = "📌 Pinned for {$pinDays} " . ($pinDays === 1 ? 'day' : 'days') . " — view & claim gifts at /wishlists#wishlist-{$wishlistId}";

            $postMessage = implode("\n", $lines);
            $postTime = function_exists('milliSeconds') ? (string) milliSeconds() : date('Y-m-d H:i:s');

            $ins = $pdo->prepare(
                "INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created)
                 VALUES (?, ?, ?, ?, ?, ?, 'published', '', NOW())"
            );
            $ins->execute([$userId, $authorName, $profileImg, $postMessage, $familyCode, $postTime]);

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[GiftWishlistService::publishFeedPost] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a wishlist owned by the initiating user.
     */
    public function deleteWishlist(int $wishlistId, string $userId): bool
    {
        if ($wishlistId <= 0 || trim($userId) === '') {
            return false;
        }
        $stmt = $this->db()->prepare('DELETE FROM family_gift_wishlists WHERE id = ? AND user_id = ?');
        $stmt->execute([$wishlistId, $userId]);
        return $stmt->rowCount() > 0;
    }

    private function sanitizeCurrency(string $currency): string
    {
        $clean = trim(strip_tags($currency));
        $allowed = ['£', '$', '€', '₦', 'CAD', 'AUD', 'GBP', 'USD', 'EUR', 'NGN'];
        return in_array($clean, $allowed, true) ? $clean : '£';
    }

    private function sanitizeHttpUrl(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }
        if (!preg_match('#^https?://#i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }
        if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return null;
        }
        $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }
        return mb_substr($trimmed, 0, 500);
    }
}
