<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;

/**
 * GDPR Art. 15 (access) + Art. 20 (portability) — assembles a machine-readable
 * copy of everything the platform holds about one member.
 *
 * The table list is a hardcoded allowlist ({table => key column}); nothing here
 * is caller-controlled, so `SELECT * FROM {$table}` is safe. Sensitive columns
 * (password hashes, tokens, push keys) are stripped from the output.
 */
final class DataExportService
{
    /**
     * table => column that holds this user's account id.
     * `__email` means "match on the user's email instead of id".
     * `__notification` is the sender/receiver special case.
     *
     * @var array<string, string>
     */
    private const TABLES = [
        'account'               => 'id',
        'personal'              => 'id',
        'contact'               => 'id',
        'work'                  => 'id',
        'children'              => 'id',
        'sibling'               => 'id',
        'family_biographies'    => 'user_id',
        'user_families'         => 'user_id',
        'family_nodes'          => 'user_id',
        'post'                  => 'id',
        'comment'               => 'id',
        'post_reactions'        => 'user_id',
        'comment_reactions'     => 'id',
        'post_poll_votes'       => 'user_id',
        'events'                => 'id',
        'images'                => 'id',
        'profilePics'           => 'id',
        'uploadPics'            => 'id',
        'family_reels'          => 'user_id',
        'family_reel_comments'  => 'user_id',
        'family_reel_reactions' => 'user_id',
        'requestMgt'            => '__request',
        'notification'          => '__notification',
        'kinship_dismissed'     => 'user_id',
        'platform_analytics'    => 'user_id',
        'audit_logs'            => '__email',
        'login_events'          => 'user_id',
    ];

    /** Columns that must never leave the building, per table. */
    private const REDACT = [
        'account'         => ['password', 'token', 'token_version'],
        'pushNotification' => ['p256dhKey', 'authKey'],
    ];

    public function __construct(
        private readonly string $userId,
        private readonly string $email,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function collect(): array
    {
        $db = Db::connect2();
        $out = [];

        foreach (self::TABLES as $table => $key) {
            try {
                [$sql, $bind] = $this->queryFor($table, $key);
                $stmt = $db->prepare($sql);
                $stmt->execute($bind);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($rows) {
                    $out[$table] = $this->redact($table, $rows);
                }
            } catch (\Throwable $e) {
                // A missing table or column must not abort the whole export.
                error_log("[DataExport] {$table}: " . $e->getMessage());
                $out[$table] = ['__error' => 'could not be read'];
            }
        }

        return [
            'export_format'  => 'familyplatform.data-export.v1',
            'generated_at'   => gmdate('c'),
            'account_id'     => $this->userId,
            'email'          => $this->email,
            'notice'        => 'This file contains the personal data we hold about your account. '
                              . 'Shared family content created by other members is not included.',
            'data'           => $out,
        ];
    }

    /**
     * @return array{0:string,1:array<int,string>}
     */
    private function queryFor(string $table, string $key): array
    {
        if ($key === '__notification') {
            // Notifications the user *received* only — exporting everything they
            // ever sent would hand back a list of other members' ids
            // (GDPR Art. 15(4): must not adversely affect the rights of others).
            $r = $this->receiverIds();
            $ph = implode(',', array_fill(0, count($r), '?'));
            return ["SELECT * FROM `{$table}` WHERE receiver_id IN ($ph)", $r];
        }

        return match ($key) {
            '__email'   => ["SELECT * FROM `{$table}` WHERE email = ?", [$this->email]],
            '__request' => ["SELECT * FROM `{$table}` WHERE requester_id = ? OR approver_id = ?", [$this->userId, $this->userId]],
            default     => ["SELECT * FROM `{$table}` WHERE `{$key}` = ?", [$this->userId]],
        };
    }

    /**
     * The user's own id plus their family codes — a notification's `receiver_id`
     * is one of these.
     *
     * @return array<int, string>
     */
    private function receiverIds(): array
    {
        $ids = [$this->userId];
        $fam = $_SESSION['famCodes'] ?? [];
        if (is_string($fam)) {
            $fam = [$fam];
        }
        if (is_array($fam)) {
            foreach ($fam as $fc) {
                if (is_string($fc) && $fc !== '') {
                    $ids[] = $fc;
                }
            }
        }
        if (!empty($_SESSION['famCode']) && is_string($_SESSION['famCode'])) {
            $ids[] = $_SESSION['famCode'];
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function redact(string $table, array $rows): array
    {
        $drop = self::REDACT[$table] ?? [];
        if (!$drop) {
            return $rows;
        }
        return array_map(static function (array $row) use ($drop): array {
            foreach ($drop as $col) {
                if (array_key_exists($col, $row)) {
                    $row[$col] = '[redacted]';
                }
            }
            return $row;
        }, $rows);
    }
}
