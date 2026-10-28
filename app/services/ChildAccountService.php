<?php

declare(strict_types=1);

namespace App\services;

use Exception;
use PDO;
use Src\Db;
use Src\Exceptions\BadRequestException;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;

/**
 * 🛡️ ChildAccountService
 * 
 * Manages parent-provisioned child accounts, credential generation,
 * kinship authorization, EXIF metadata sanitisation, and supervision guardrails.
 * 
 * Compliance: UK GDPR (DPA 2018 s.9), ICO Children's Code, Online Safety Act 2023.
 */
final class ChildAccountService
{
    public const MIN_USERNAME_LENGTH = 3;
    public const MAX_USERNAME_LENGTH = 30;
    public const MIN_PIN_LENGTH = 4;
    public const MAX_PIN_LENGTH = 8;

    /**
     * Provisions a new supervised child account for a child node in the family tree.
     *
     * @param string $parentUserId Current authenticated parent user ID
     * @param int $childNodeId Target family_nodes.id for the child
     * @param string $username Unique username within the family
     * @param string $pin 4-8 digit numeric PIN
     * @param string|null $dobYear Date of birth year (e.g. 2015)
     * @param string|null $dobMonth Date of birth month (e.g. '06' or 'Jun')
     * @param string|null $dobDay Date of birth day (e.g. '15')
     * @param string|null $clientIp Client IP address for consent audit log
     * @return array<string, mixed>
     */
    public static function provisionChildAccount(
        string $parentUserId,
        int $childNodeId,
        string $username,
        string $pin,
        ?string $dobYear = null,
        ?string $dobMonth = null,
        ?string $dobDay = null,
        ?string $clientIp = null
    ): array {
        $parentUserId = trim($parentUserId);
        if ($parentUserId === '') {
            throw new ForbiddenException('You must be signed in as a parent or guardian to provision a child account.');
        }

        if ($childNodeId <= 0) {
            throw new BadRequestException('A valid family node ID must be specified.');
        }

        // Clean & validate username
        $cleanUsername = strtolower(trim($username));
        if (!preg_match('/^[a-z0-9_]{3,30}$/', $cleanUsername)) {
            throw new ValidationException('Child username must be between 3 and 30 characters and contain only lowercase letters, numbers, and underscores.');
        }

        // Clean & validate PIN
        $cleanPin = trim($pin);
        if (!preg_match('/^[0-9]{4,8}$/', $cleanPin)) {
            throw new ValidationException('Child PIN must be between 4 and 8 digits.');
        }

        $db = Db::connect2();

        // 1. Verify parent's approved family codes
        $stmtP = $db->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
        $stmtP->execute([$parentUserId]);
        $parentFamCode = (string)($stmtP->fetchColumn() ?: '');
        if ($parentFamCode === '') {
            throw new ForbiddenException('Parent account has no active family code.');
        }

        // 2. Fetch the target child node
        $stmtNode = $db->prepare('SELECT id, family_code, user_id, first_name, last_name, gender, birth_date, birth_year FROM family_nodes WHERE id = ? LIMIT 1');
        $stmtNode->execute([$childNodeId]);
        $childNode = $stmtNode->fetch(PDO::FETCH_ASSOC);

        if (!$childNode) {
            throw new NotFoundException('The specified family node could not be found.');
        }

        $nodeFamCode = (string)($childNode['family_code'] ?? '');
        if ($nodeFamCode !== $parentFamCode) {
            // Check if parent shares this family code in user_families
            $stmtShares = $db->prepare('SELECT COUNT(*) FROM user_families WHERE user_id = ? AND family_code = ? AND status = "approved"');
            $stmtShares->execute([$parentUserId, $nodeFamCode]);
            if ((int)$stmtShares->fetchColumn() === 0) {
                throw new ForbiddenException('You do not have permission to manage nodes in this family tree.');
            }
        }

        // Check if node is already linked to an active account
        $existingLinkedUserId = trim((string)($childNode['user_id'] ?? ''));
        if ($existingLinkedUserId !== '') {
            $stmtAccCheck = $db->prepare('SELECT id FROM account WHERE id = ? LIMIT 1');
            $stmtAccCheck->execute([$existingLinkedUserId]);
            if ($stmtAccCheck->fetchColumn()) {
                throw new BadRequestException('This family member already has an active account linked to their profile.');
            }
        }

        // 3. Ensure username is unique within this family
        $stmtUserCheck = $db->prepare('SELECT id FROM child_pin_credentials WHERE family_code = ? AND child_username = ? LIMIT 1');
        $stmtUserCheck->execute([$nodeFamCode, $cleanUsername]);
        if ($stmtUserCheck->fetchColumn()) {
            throw new ValidationException('The username "' . $cleanUsername . '" is already in use in your family. Please choose another.');
        }

        // 4. Resolve Date of Birth and calculate Age Tier
        $dobData = self::resolveBirthDate($childNode, $dobYear, $dobMonth, $dobDay);
        $age = $dobData['age'];
        $birthDateSql = $dobData['sql_date'];
        $transitionDueDate = $dobData['transition_due_date'];

        $adulthoodStatus = 'minor_supervised';
        if ($age >= 18) {
            $adulthoodStatus = 'standard_adult';
        } elseif ($age >= 13) {
            $adulthoodStatus = 'minor_teen';
        }

        // Generate IDs and hash credentials
        $childUserId = 'child_' . bin2hex(random_bytes(10));
        $pinHash = password_hash($cleanPin, PASSWORD_BCRYPT);
        $cleanFam = strtolower((string)preg_replace('/[^a-z0-9]/', '', (string)$nodeFamCode));
        $placeholderEmail = $cleanUsername . '@' . $cleanFam . '.family.local';

        $firstName = trim((string)($childNode['first_name'] ?? 'Child'));
        $lastName = trim((string)($childNode['last_name'] ?? ''));
        $gender = (string)($childNode['gender'] ?? 'Other');

        $db->beginTransaction();
        try {
            // A. Insert account
            $stmtInsAcc = $db->prepare('
                INSERT INTO account (
                    id, email, password, type, status,
                    is_child_account, adulthood_status, parent_user_id,
                    transition_due_date, firstName, lastName
                ) VALUES (?, ?, ?, "member", "approved", 1, ?, ?, ?, ?, ?)
            ');
            $stmtInsAcc->execute([
                $childUserId,
                $placeholderEmail,
                $pinHash,
                $adulthoodStatus,
                $parentUserId,
                $transitionDueDate,
                $firstName,
                $lastName
            ]);

            // B. Insert personal table record
            $stmtInsPers = $db->prepare('
                INSERT INTO personal (
                    id, firstName, lastName, famCode,
                    day, month, year, gender
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmtInsPers->execute([
                $childUserId,
                $firstName,
                $lastName,
                $nodeFamCode,
                $dobData['day'],
                $dobData['month_str'],
                $dobData['year'],
                $gender
            ]);

            // Fetch parent country for inherited location setting
            $stmtParentCountry = $db->prepare('SELECT country FROM contact WHERE id = ? LIMIT 1');
            $stmtParentCountry->execute([$parentUserId]);
            $parentCountry = (string)($stmtParentCountry->fetchColumn() ?: 'GB');

            // C. Insert contact record
            $stmtInsContact = $db->prepare('
                INSERT INTO contact (id, email, country, mobile, show_birth_year)
                VALUES (?, ?, ?, "", "off")
            ');
            $stmtInsContact->execute([$childUserId, $placeholderEmail, $parentCountry]);

            // D. Insert child PIN credentials
            $stmtInsCred = $db->prepare('
                INSERT INTO child_pin_credentials (
                    user_id, child_username, family_code, pin_hash
                ) VALUES (?, ?, ?, ?)
            ');
            $stmtInsCred->execute([
                $childUserId,
                $cleanUsername,
                $nodeFamCode,
                $pinHash
            ]);

            // E. Link family_nodes to the newly created child account
            $stmtUpdNode = $db->prepare('
                UPDATE family_nodes 
                SET user_id = ?, birth_date = ?, birth_year = ?
                WHERE id = ?
            ');
            $stmtUpdNode->execute([
                $childUserId,
                $birthDateSql,
                $dobData['year'],
                $childNodeId
            ]);

            // F. Log verifiable parental consent (UK GDPR s.9 / DPA 2018)
            $stmtConsent = $db->prepare('
                INSERT INTO guardian_consents (
                    parent_user_id, child_user_id, family_code,
                    consent_type, parent_ip
                ) VALUES (?, ?, ?, "parental_account_sponsorship", ?)
            ');
            $stmtConsent->execute([
                $parentUserId,
                $childUserId,
                $nodeFamCode,
                $clientIp
            ]);

            // G. Link to parent children list if not already present
            $stmtCheckChild = $db->prepare('SELECT COUNT(*) FROM children WHERE id = ? AND children_linked = ?');
            $stmtCheckChild->execute([$parentUserId, $childUserId]);
            if ((int)$stmtCheckChild->fetchColumn() === 0) {
                $stmtAddChild = $db->prepare('
                    INSERT INTO children (id, children_name, children_email, children_linked, children_code)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmtAddChild->execute([
                    $parentUserId,
                    trim($firstName . ' ' . $lastName),
                    $placeholderEmail,
                    $childUserId,
                    $nodeFamCode
                ]);
            }

            $db->commit();

            return [
                'status' => 'success',
                'message' => 'Child account provisioned successfully.',
                'child' => [
                    'user_id' => $childUserId,
                    'username' => $cleanUsername,
                    'family_code' => $nodeFamCode,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'adulthood_status' => $adulthoodStatus,
                    'transition_due_date' => $transitionDueDate,
                    'age' => $age,
                ],
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Authenticates a child using Family Code + Child Username + PIN.
     *
     * @param string $familyCode Family network code
     * @param string $username Child username
     * @param string $pin Numeric PIN
     * @return array<string, mixed>|null Returns session payload or null on invalid credentials
     */
    public static function authenticateChild(string $familyCode, string $username, string $pin): ?array
    {
        $cleanFamCode = trim($familyCode);
        $cleanUsername = strtolower(trim($username));
        $cleanPin = trim($pin);

        if ($cleanFamCode === '' || $cleanUsername === '' || $cleanPin === '') {
            return null;
        }

        $db = Db::connect2();
        $stmt = $db->prepare('
            SELECT c.user_id, c.pin_hash, c.family_code,
                   a.status, a.is_child_account, a.adulthood_status,
                   p.firstName, p.lastName
            FROM child_pin_credentials c
            JOIN account a ON a.id = c.user_id
            JOIN personal p ON p.id = c.user_id
            WHERE c.family_code = ? AND c.child_username = ?
            LIMIT 1
        ');
        $stmt->execute([$cleanFamCode, $cleanUsername]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        if (($row['status'] ?? '') !== 'approved') {
            return null;
        }

        if (!password_verify($cleanPin, (string)$row['pin_hash'])) {
            return null;
        }

        return [
            'id' => (string)$row['user_id'],
            'firstName' => (string)$row['firstName'],
            'lastName' => (string)$row['lastName'],
            'famCode' => (string)$row['family_code'],
            'is_child_account' => (int)($row['is_child_account'] ?? 1),
            'adulthood_status' => (string)($row['adulthood_status'] ?? 'minor_supervised'),
        ];
    }

    /**
     * Removes EXIF metadata (including GPS coordinates and camera fingerprints)
     * from uploaded child media to guarantee physical safety.
     *
     * @param string $filePath Full filesystem path to the uploaded image
     * @return bool True if processed or stripped, false otherwise
     */
    public static function stripExifMetadata(string $filePath): bool
    {
        if (!file_exists($filePath) || !is_file($filePath)) {
            return false;
        }

        if (!extension_loaded('gd')) {
            return false;
        }

        $info = @getimagesize($filePath);
        if (!$info) {
            return false;
        }

        $mime = (string)$info['mime'];
        $success = false;

        switch ($mime) {
            case 'image/jpeg':
                $img = @imagecreatefromjpeg($filePath);
                if ($img) {
                    $success = @imagejpeg($img, $filePath, 90);
                    @imagedestroy($img);
                }
                break;
            case 'image/png':
                $img = @imagecreatefrompng($filePath);
                if ($img) {
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                    $success = @imagepng($img, $filePath);
                    @imagedestroy($img);
                }
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
                    $img = @imagecreatefromwebp($filePath);
                    if ($img) {
                        $success = @imagewebp($img, $filePath, 90);
                        @imagedestroy($img);
                    }
                }
                break;
            default:
                $success = false;
                break;
        }

        return $success;
    }

    /**
     * Verifies that a parent is authorized to supervise an account.
     * Hard-blocks access if the child has turned 18 or graduated (BOLA/IDOR protection).
     *
     * @throws ForbiddenException If access is not permitted or supervision has expired
     */
    public static function assertParentSupervisionAllowed(string $parentUserId, string $childUserId): void
    {
        $db = Db::connect2();

        $stmt = $db->prepare('SELECT id, adulthood_status, is_child_account, parent_user_id FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$childUserId]);
        $child = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$child) {
            throw new NotFoundException('Account not found.');
        }

        // Hard Gate: Once status is graduated_adult, parental surveillance throws 403 Forbidden
        $status = (string)($child['adulthood_status'] ?? '');
        if ($status === 'graduated_adult' || (int)($child['is_child_account'] ?? 0) === 0) {
            throw new ForbiddenException('Parental supervision has expired. User has reached legal adulthood.');
        }

        $assignedParent = (string)($child['parent_user_id'] ?? '');
        if ($assignedParent !== $parentUserId) {
            // Check guardian consents
            $stmtC = $db->prepare('SELECT COUNT(*) FROM guardian_consents WHERE parent_user_id = ? AND child_user_id = ? AND revoked_at IS NULL');
            $stmtC->execute([$parentUserId, $childUserId]);
            if ((int)$stmtC->fetchColumn() === 0) {
                throw new ForbiddenException('Unauthorized guardian relationship.');
            }
        }
    }

    /**
     * Resolves birth date parameters and computes age and transition due date.
     *
     * @param array<string, mixed> $childNode Existing node data
     * @param string|null $dobYear Input year
     * @param string|null $dobMonth Input month
     * @param string|null $dobDay Input day
     * @return array{age: int, sql_date: string, transition_due_date: string, year: int, month: int, month_str: string, day: int}
     */
    private static function resolveBirthDate(
        array $childNode,
        ?string $dobYear,
        ?string $dobMonth,
        ?string $dobDay
    ): array {
        $monthMap = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $y = null;
        $m = null;
        $d = null;

        if (!empty($dobYear)) {
            $y = (int)trim($dobYear);
            if (!empty($dobMonth)) {
                $cleanM = strtolower(substr(trim($dobMonth), 0, 3));
                $m = $monthMap[$cleanM] ?? (int)trim($dobMonth);
            }
            if (!empty($dobDay)) {
                $d = (int)trim($dobDay);
            }
        }

        // Fall back to node birth_date or birth_year
        if ($y === null && !empty($childNode['birth_date'])) {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d', (string)$childNode['birth_date']);
            if ($dt) {
                $y = (int)$dt->format('Y');
                $m = (int)$dt->format('n');
                $d = (int)$dt->format('j');
            }
        }

        if ($y === null && !empty($childNode['birth_year'])) {
            $y = (int)$childNode['birth_year'];
            $m = 1;
            $d = 1;
        }

        // Default to a 10-year-old child if completely missing
        $today = new \DateTimeImmutable('today');
        if ($y === null || $y < 1900 || $y > (int)$today->format('Y')) {
            $y = (int)$today->format('Y') - 10;
            $m = 1;
            $d = 1;
        }

        if ($m === null || $m < 1 || $m > 12) {
            $m = 1;
        }
        if ($d === null || $d < 1 || $d > 31 || !checkdate($m, $d, $y)) {
            $d = 1;
        }

        $dob = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $y, $m, $d));
        $age = $dob->diff($today)->y;
        $transitionDate = $dob->modify('+18 years')->format('Y-m-d');

        return [
            'age' => $age,
            'sql_date' => $dob->format('Y-m-d'),
            'transition_due_date' => $transitionDate,
            'year' => $y,
            'month' => $m,
            'month_str' => $monthNames[$m],
            'day' => $d,
        ];
    }
}
