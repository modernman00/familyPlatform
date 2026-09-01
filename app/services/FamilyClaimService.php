<?php
declare(strict_types=1);

namespace App\services;

use Src\Db;

class FamilyClaimService
{
    /**
     * Claims an existing unclaimed tree node or creates a fresh root node for the member.
     *
     * @param string $familyCode
     * @param string $userId
     * @param array<string, mixed> $memberData
     * @param int|null $explicitClaimNodeId
     * @return int Node ID
     */
    public static function claimOrInitializeNode(
        string $familyCode,
        string $userId,
        array $memberData,
        ?int $explicitClaimNodeId = null
    ): int {
        if (empty($familyCode) || empty($userId)) {
            return 0;
        }

        $db = Db::connect2();

        // 1. Check if user already has an active claimed root node in this family
        $checkStmt = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$familyCode, $userId]);
        $existingNodeId = $checkStmt->fetchColumn();
        if ($existingNodeId) {
            return (int)$existingNodeId;
        }

        $firstName = trim((string)($memberData['firstName'] ?? ''));
        $lastName  = trim((string)($memberData['lastName'] ?? ''));
        $email     = strtolower(trim((string)($memberData['email'] ?? '')));
        $mobile    = trim((string)($memberData['mobile'] ?? ''));
        $cleanMobile = preg_replace('/\D+/', '', $mobile);

        // 2. Explicit Claim Node (from secure token/deep-link)
        if ($explicitClaimNodeId && $explicitClaimNodeId > 0) {
            $valStmt = $db->prepare("SELECT id FROM family_nodes WHERE id = ? AND family_code = ? AND user_id IS NULL LIMIT 1");
            $valStmt->execute([$explicitClaimNodeId, $familyCode]);
            if ($valStmt->fetchColumn()) {
                $upd = $db->prepare("
                    UPDATE family_nodes 
                    SET user_id = ?, 
                        first_name = COALESCE(NULLIF(?, ''), first_name),
                        last_name = COALESCE(NULLIF(?, ''), last_name),
                        email = COALESCE(NULLIF(?, ''), email),
                        mobile = COALESCE(NULLIF(?, ''), mobile)
                    WHERE id = ?
                ");
                $upd->execute([$userId, $firstName, $lastName, $email, $mobile, $explicitClaimNodeId]);
                return $explicitClaimNodeId;
            }
        }

        // 3. Multi-Faceted Auto-Claim (Normalized Email, E.164 Mobile & Name Match)
        $candStmt = $db->prepare("SELECT id, first_name, last_name, email, mobile FROM family_nodes WHERE family_code = ? AND user_id IS NULL");
        $candStmt->execute([$familyCode]);
        $unclaimedCandidates = $candStmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($unclaimedCandidates as $cand) {
            $candId = (int)$cand['id'];
            $candEmail = strtolower(trim((string)($cand['email'] ?? '')));
            $candMobile = trim((string)($cand['mobile'] ?? ''));
            $candCleanMobile = preg_replace('/\D+/', '', $candMobile);
            $candFirst = trim((string)($cand['first_name'] ?? ''));
            $candLast = trim((string)($cand['last_name'] ?? ''));

            $isMatch = false;

            // A. Email Match
            if (!empty($email) && !empty($candEmail) && $email === $candEmail) {
                $isMatch = true;
            }

            // B. Phone Match (exact digits or matching trailing 9 digits for country-code variations)
            if (!$isMatch && !empty($cleanMobile) && !empty($candCleanMobile)) {
                if ($cleanMobile === $candCleanMobile) {
                    $isMatch = true;
                } elseif (strlen($cleanMobile) >= 9 && strlen($candCleanMobile) >= 9) {
                    if (substr($cleanMobile, -9) === substr($candCleanMobile, -9)) {
                        $isMatch = true;
                    }
                }
            }

            // C. First & Last Name Match (Case-Insensitive)
            if (!$isMatch && !empty($firstName) && !empty($lastName) && !empty($candFirst) && !empty($candLast)) {
                if (strcasecmp($firstName, $candFirst) === 0 && strcasecmp($lastName, $candLast) === 0) {
                    $isMatch = true;
                }
            }

            if ($isMatch) {
                $upd = $db->prepare("
                    UPDATE family_nodes 
                    SET user_id = ?, 
                        email = COALESCE(NULLIF(?, ''), email),
                        mobile = COALESCE(NULLIF(?, ''), mobile)
                    WHERE id = ?
                ");
                $upd->execute([$userId, $email, $mobile, $candId]);
                return $candId;
            }
        }

        // 4. Create Root User Node if no unclaimed node existed
        $sex = (($memberData['gender'] ?? 'Male') === 'Male') ? 'avatarM.png' : 'avatarF.png';
        $rootAvatar = (string)($memberData['img'] ?? "/resources/images/profile/{$sex}");

        $insRoot = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, birth_year, occupation, location, email, mobile, avatar_url, generation_level, bio)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
        ");
        $insRoot->execute([
            $familyCode,
            $userId,
            !empty($firstName) ? $firstName : 'Self',
            $lastName,
            (string)($memberData['gender'] ?? 'Male'),
            !empty($memberData['year']) ? (int)$memberData['year'] : null,
            $memberData['occupation'] ?? null,
            $memberData['country'] ?? null,
            !empty($email) ? $email : null,
            !empty($mobile) ? $mobile : null,
            $rootAvatar,
            "Primary Member"
        ]);
        $rootNodeId = (int)$db->lastInsertId();

        // 5. Create Parent Placeholders if parents provided
        $fatherName = trim((string)($memberData['father_name'] ?? ''));
        $motherName = trim((string)($memberData['mother_name'] ?? ''));

        if (!empty($fatherName) || !empty($motherName)) {
            $fParts = !empty($fatherName) ? explode(' ', $fatherName, 2) : ['Father', ''];
            $mParts = !empty($motherName) ? explode(' ', $motherName, 2) : ['Mother', ''];

            $insParent = $db->prepare("
                INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                VALUES (?, ?, ?, ?, -1, ?, ?, ?, ?)
            ");

            $insParent->execute([
                $familyCode,
                $fParts[0],
                $fParts[1] ?? '',
                'Male',
                '/resources/images/profile/avatarM.png',
                'Father',
                $memberData['father_email'] ?? null,
                $memberData['father_mobile'] ?? null
            ]);
            $fatherId = (int)$db->lastInsertId();

            $insParent->execute([
                $familyCode,
                $mParts[0],
                $mParts[1] ?? '',
                'Female',
                '/resources/images/profile/avatarF.png',
                'Mother',
                $memberData['mother_email'] ?? null,
                $memberData['mother_mobile'] ?? null
            ]);
            $motherId = (int)$db->lastInsertId();

            $insUnion = $db->prepare("
                INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                VALUES (?, ?, ?, 'married', 1)
            ");
            $insUnion->execute([$familyCode, $fatherId, $motherId]);
            $parentUnionId = (int)$db->lastInsertId();

            $insLink = $db->prepare("
                INSERT INTO family_node_children (union_id, child_id, relationship_type)
                VALUES (?, ?, 'biological')
            ");
            $insLink->execute([$parentUnionId, $rootNodeId]);
        }

        return $rootNodeId;
    }

    /**
     * Generates a tamper-proof HMAC-SHA256 signed invite token.
     */
    public static function generateSignedInviteToken(
        int $nodeId,
        string $familyCode,
        string $email,
        string $firstName,
        string $lastName,
        int $ttlSeconds = 604800 // 7 days default
    ): string {
        $payload = [
            'node_id' => $nodeId,
            'family_code' => $familyCode,
            'email' => strtolower(trim($email)),
            'first_name' => trim($firstName),
            'last_name' => trim($lastName),
            'exp' => time() + $ttlSeconds
        ];

        $json = (string)json_encode($payload);
        $b64 = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $secret = (string)($_ENV['APP_KEY'] ?? 'familyPlatformSecurityTokenSecretKey2026');
        $sig = hash_hmac('sha256', $b64, $secret);

        return $b64 . '.' . $sig;
    }

    /**
     * Verifies and extracts data from an HMAC-SHA256 signed invite token.
     *
     * @return array{node_id: int, family_code: string, email: string, first_name: string, last_name: string}|null
     */
    public static function verifySignedInviteToken(string $token): ?array
    {
        if (empty($token) || !str_contains($token, '.')) {
            return null;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$b64, $signature] = $parts;
        $secret = (string)($_ENV['APP_KEY'] ?? 'familyPlatformSecurityTokenSecretKey2026');
        $expectedSig = hash_hmac('sha256', $b64, $secret);

        if (!hash_equals($expectedSig, $signature)) {
            return null; // Tampered or forged token
        }

        $json = base64_decode(strtr($b64, '-_', '+/'), true);
        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['node_id']) || empty($data['family_code'])) {
            return null;
        }

        // Expiry check
        if (!empty($data['exp']) && time() > (int)$data['exp']) {
            return null; // Expired token
        }

        $nodeId = (int)$data['node_id'];
        $familyCode = (string)$data['family_code'];

        // Ensure the node still exists, belongs to family, and is unclaimed
        $db = Db::connect2();
        $stmt = $db->prepare("SELECT id FROM family_nodes WHERE id = ? AND family_code = ? AND user_id IS NULL LIMIT 1");
        $stmt->execute([$nodeId, $familyCode]);
        if (!$stmt->fetchColumn()) {
            return null; // Already claimed or deleted
        }

        return [
            'node_id' => $nodeId,
            'family_code' => $familyCode,
            'email' => (string)($data['email'] ?? ''),
            'first_name' => (string)($data['first_name'] ?? ''),
            'last_name' => (string)($data['last_name'] ?? '')
        ];
    }

    /**
     * Atomically claims a specific family node by ID.
     */
    public static function claimNodeById(int $nodeId, string $userId, string $familyCode): bool
    {
        if ($nodeId <= 0 || empty($userId) || empty($familyCode)) {
            return false;
        }

        $db = Db::connect2();
        $db->beginTransaction();

        try {
            // Check if user already has an active root node
            $checkExisting = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
            $checkExisting->execute([$familyCode, $userId]);
            $existingId = $checkExisting->fetchColumn();

            if ($existingId && (int)$existingId !== $nodeId) {
                // If user had a standalone placeholder root node with no connections, delete it to merge
                $checkConns = $db->prepare("SELECT COUNT(*) FROM family_node_children WHERE child_id = ?");
                $checkConns->execute([$existingId]);
                $conns = (int)$checkConns->fetchColumn();

                if ($conns === 0) {
                    $del = $db->prepare("DELETE FROM family_nodes WHERE id = ?");
                    $del->execute([$existingId]);
                }
            }

            $stmt = $db->prepare("UPDATE family_nodes SET user_id = ? WHERE id = ? AND family_code = ? AND user_id IS NULL");
            $stmt->execute([$userId, $nodeId, $familyCode]);
            $success = ($stmt->rowCount() === 1);

            $db->commit();
            return $success;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("claimNodeById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Detects fuzzy unclaimed relative matches in the tree for the current user.
     *
     * @param array<string, mixed> $memberData
     * @return array<string, mixed>|null
     */
    public static function findFuzzyUnclaimedMatches(string $familyCode, string $userId, array $memberData): ?array
    {
        if (empty($familyCode) || empty($userId)) {
            return null;
        }

        $db = Db::connect2();

        // 1. Check if user already claimed a node
        $checkStmt = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$familyCode, $userId]);
        if ($checkStmt->fetchColumn()) {
            return null; // Already linked to a node
        }

        $lastName = trim((string)($memberData['lastName'] ?? ''));
        $firstName = trim((string)($memberData['firstName'] ?? ''));

        // 2. Look for unclaimed candidate nodes in the family tree
        $stmt = $db->prepare("
            SELECT id, first_name, last_name, bio, gender, email, mobile, generation_level 
            FROM family_nodes 
            WHERE family_code = ? AND user_id IS NULL
            ORDER BY id ASC
        ");
        $stmt->execute([$familyCode]);
        $candidates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($candidates as $cand) {
            $candId = (int)$cand['id'];
            if (!empty($_SESSION['dismissed_claim_node_' . $candId])) {
                continue;
            }

            $candFirst = trim((string)($cand['first_name'] ?? ''));
            $candLast = trim((string)($cand['last_name'] ?? ''));

            // Check surname match or similar first name
            $isMatch = false;
            if (!empty($lastName) && !empty($candLast) && strcasecmp($lastName, $candLast) === 0) {
                $isMatch = true;
            } elseif (!empty($firstName) && !empty($candFirst) && (
                strcasecmp($firstName, $candFirst) === 0 ||
                soundex($firstName) === soundex($candFirst) ||
                stripos($candFirst, $firstName) !== false ||
                stripos($firstName, $candFirst) !== false
            )) {
                $isMatch = true;
            }

            if ($isMatch) {
                $role = (string)($cand['bio'] ?? 'Family Member');
                return [
                    'node_id' => $candId,
                    'first_name' => $candFirst,
                    'last_name' => $candLast,
                    'full_name' => trim("$candFirst $candLast"),
                    'role' => $role,
                    'gender' => $cand['gender'] ?? 'Unknown'
                ];
            }
        }

        return null;
    }
}
