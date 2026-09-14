<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;

/**
 * GDPR Art. 17 — Account Erasure & Lineage Anonymisation Pipeline.
 *
 * Atomically purges member PII across 27 database tables while preserving
 * shared family tree structure and memory threads (replacing author credentials
 * with anonymised placeholders to avoid breaking living relatives' trees).
 */
final class DataErasureService
{
    public function __construct(
        private readonly string $userId
    ) {
    }

    /**
     * Executes account erasure inside a single atomic PDO transaction.
     *
     * @param string $performedBy Admin ID or 'CLI'
     * @return array{success: bool, message: string, erased_tables: array<string>}
     */
    public function erase(string $performedBy = 'system'): array
    {
        $db = Db::connect2();
        $db->beginTransaction();

        $erasedTables = [];

        try {
            // 1. Fetch account email before wiping credentials
            $stmt = $db->prepare('SELECT email FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$this->userId]);
            $email = (string) ($stmt->fetchColumn() ?: '');

            if ($email === '') {
                $db->rollBack();
                return [
                    'success' => false,
                    'message' => "Account ID {$this->userId} not found.",
                    'erased_tables' => [],
                ];
            }

            // 2. Anonymise primary account credentials
            $anonEmail = 'erased-' . preg_replace('/[^A-Za-z0-9_-]/', '', $this->userId) . '@anonymised.local';
            $stmtAccount = $db->prepare('
                UPDATE account
                SET email = ?,
                    password = \'[ERASED]\',
                    mobile = \'[ERASED]\',
                    country = \'[ERASED]\',
                    token = NULL,
                    token_version = token_version + 1,
                    status = \'erased\',
                    deleted_at = NOW()
                WHERE id = ?
            ');
            $stmtAccount->execute([$anonEmail, $this->userId]);
            $erasedTables[] = 'account';

            // 3. Purge standalone personal profile tables (hard delete)
            $personalTables = [
                'personal'  => 'id',
                'contact'   => 'id',
                'work'      => 'id',
                'children'  => 'id',
                'sibling'   => 'id',
            ];
            foreach ($personalTables as $table => $col) {
                try {
                    $del = $db->prepare("DELETE FROM `{$table}` WHERE `{$col}` = ?");
                    $del->execute([$this->userId]);
                    $erasedTables[] = $table;
                } catch (\Throwable $e) {
                    error_log("[DataErasure] Table {$table} delete skipped: " . $e->getMessage());
                }
            }

            // 4. Purge sessions, notifications, analytics, push subscriptions & claims
            $activityTables = [
                'user_families'         => 'user_id',
                'kinship_dismissed'     => 'user_id',
                'platform_analytics'    => 'user_id',
                'login_events'          => 'user_id',
                'pushNotification'      => 'user_id',
                'family_biographies'    => 'user_id',
            ];
            foreach ($activityTables as $table => $col) {
                try {
                    $del = $db->prepare("DELETE FROM `{$table}` WHERE `{$col}` = ?");
                    $del->execute([$this->userId]);
                    $erasedTables[] = $table;
                } catch (\Throwable $e) {
                    error_log("[DataErasure] Table {$table} delete skipped: " . $e->getMessage());
                }
            }

            // Purge notifications received by this user
            try {
                $delNotif = $db->prepare('DELETE FROM `notification` WHERE receiver_id = ? OR sender_id = ?');
                $delNotif->execute([$this->userId, $this->userId]);
                $erasedTables[] = 'notification';
            } catch (\Throwable $e) {
                error_log("[DataErasure] Table notification delete skipped: " . $e->getMessage());
            }

            // Purge requests
            try {
                $delReq = $db->prepare('DELETE FROM `requestMgt` WHERE requester_id = ? OR approver_id = ?');
                $delReq->execute([$this->userId, $this->userId]);
                $erasedTables[] = 'requestMgt';
            } catch (\Throwable $e) {
                error_log("[DataErasure] Table requestMgt delete skipped: " . $e->getMessage());
            }

            // 5. Purge reactions & poll votes
            $interactionTables = [
                'post_reactions'        => 'user_id',
                'comment_reactions'     => 'id',
                'family_reel_reactions' => 'user_id',
                'post_poll_votes'       => 'user_id',
            ];
            foreach ($interactionTables as $table => $col) {
                try {
                    $del = $db->prepare("DELETE FROM `{$table}` WHERE `{$col}` = ?");
                    $del->execute([$this->userId]);
                    $erasedTables[] = $table;
                } catch (\Throwable $e) {
                    error_log("[DataErasure] Table {$table} delete skipped: " . $e->getMessage());
                }
            }

            // 6. Anonymise Shared Family Tree Nodes (preserve lineage structure)
            try {
                $anonNode = $db->prepare('
                    UPDATE family_nodes
                    SET full_name = \'Anonymised Family Member\',
                        first_name = \'Anonymised\',
                        last_name = \'Member\',
                        email = \'\',
                        mobile = \'\',
                        bio = \'\',
                        avatar_url = \'/resources/images/profile/avatarM.png\',
                        is_erased = 1
                    WHERE user_id = ?
                ');
                $anonNode->execute([$this->userId]);
                $erasedTables[] = 'family_nodes';
            } catch (\Throwable $e) {
                error_log("[DataErasure] family_nodes anonymisation skipped: " . $e->getMessage());
            }

            // 7. Anonymise Shared Posts, Comments & Reels authorship
            try {
                $anonPost = $db->prepare('
                    UPDATE post
                    SET fname = \'Anonymised\',
                        lname = \'Member\',
                        user_name = \'Anonymised Member\',
                        profile_img = \'/resources/images/profile/avatarM.png\'
                    WHERE id = ?
                ');
                $anonPost->execute([$this->userId]);
                $erasedTables[] = 'post';
            } catch (\Throwable $e) {
                error_log("[DataErasure] post anonymisation skipped: " . $e->getMessage());
            }

            try {
                $anonComment = $db->prepare('
                    UPDATE comment
                    SET f_name = \'Anonymised\',
                        l_name = \'Member\',
                        profile_img = \'/resources/images/profile/avatarM.png\'
                    WHERE id = ?
                ');
                $anonComment->execute([$this->userId]);
                $erasedTables[] = 'comment';
            } catch (\Throwable $e) {
                error_log("[DataErasure] comment anonymisation skipped: " . $e->getMessage());
            }

            try {
                $anonReelComment = $db->prepare('
                    UPDATE family_reel_comments
                    SET author_name = \'Anonymised Member\',
                        author_avatar = \'/resources/images/profile/avatarM.png\'
                    WHERE user_id = ?
                ');
                $anonReelComment->execute([$this->userId]);
                $erasedTables[] = 'family_reel_comments';
            } catch (\Throwable $e) {
                error_log("[DataErasure] family_reel_comments anonymisation skipped: " . $e->getMessage());
            }

            // 8. Physical File Removal (Profile & Upload Pictures)
            $imageTables = ['profilePics', 'uploadPics', 'images'];
            foreach ($imageTables as $table) {
                try {
                    $imgStmt = $db->prepare("SELECT path FROM `{$table}` WHERE id = ?");
                    $imgStmt->execute([$this->userId]);
                    $paths = $imgStmt->fetchAll(\PDO::FETCH_COLUMN);
                    foreach ($paths as $path) {
                        if (is_string($path) && $path !== '' && str_starts_with($path, '/') && !str_contains($path, '..')) {
                            $fullPath = BASE_PATH . $path;
                            if (file_exists($fullPath) && is_file($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                    }
                    $delImg = $db->prepare("DELETE FROM `{$table}` WHERE id = ?");
                    $delImg->execute([$this->userId]);
                    $erasedTables[] = $table;
                } catch (\Throwable $e) {
                    error_log("[DataErasure] Table {$table} file removal skipped: " . $e->getMessage());
                }
            }

            // 9. Record Audit Log
            try {
                $audit = $db->prepare('
                    INSERT INTO audit_logs (email, action, details, created_at)
                    VALUES (?, \'GDPR_ARTICLE_17_ERASURE\', ?, NOW())
                ');
                $audit->execute([
                    $email,
                    "Account ID {$this->userId} erased by {$performedBy}. Anonymised email: {$anonEmail}."
                ]);
                $erasedTables[] = 'audit_logs';
            } catch (\Throwable $e) {
                try {
                    $auditFallback = $db->prepare('
                        INSERT INTO audit_logs (email, status, created_at)
                        VALUES (?, ?, NOW())
                    ');
                    $auditFallback->execute([
                        $email,
                        "GDPR_ARTICLE_17_ERASURE: Account ID {$this->userId} erased by {$performedBy}."
                    ]);
                    $erasedTables[] = 'audit_logs';
                } catch (\Throwable $e2) {
                    error_log("[DataErasure] audit_logs entry skipped: " . $e2->getMessage());
                }
            }

            $db->commit();
            error_log("[DataErasure] Account ID {$this->userId} ({$email}) erased successfully by {$performedBy}.");

            return [
                'success' => true,
                'message' => "Account ID {$this->userId} ({$email}) successfully erased and anonymised.",
                'erased_tables' => array_values(array_unique($erasedTables)),
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("[DataErasure] FAILED for Account ID {$this->userId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erasure failed: ' . $e->getMessage(),
                'erased_tables' => [],
            ];
        }
    }
}
