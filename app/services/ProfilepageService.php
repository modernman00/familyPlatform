<?php
declare(strict_types=1);

namespace App\services;

use App\model\SingleCustomerData;
use App\model\Post;
use App\model\AllMembersData as DataAll;
use App\repository\FeedRepository;
use Src\Exceptions\NotFoundException;

final class ProfilepageService
{
    private FeedRepository $feedRepo;

    public function __construct(?FeedRepository $feedRepo = null)
    {
        $this->feedRepo = $feedRepo ?? new FeedRepository();
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfileData(string $userId, bool $includeFeedPayload = true): array
    {
        $setData = new SingleCustomerData();

        $tables = ['personal', 'contact', 'otherFamily', 'post', 'profilePics'];
        $memberData = $setData->getCustomerData($userId, $tables);

        if (!$memberData || !is_array($memberData)) {
            throw new NotFoundException("User not found");
        }

        $rawFamCode = $memberData['famCode'] ?? null;
        $famCodeClean = ($rawFamCode !== null && $rawFamCode !== '') ? checkInput($rawFamCode) : '';
        $famCode = is_string($famCodeClean) ? strtoupper(trim(ltrim($famCodeClean, '#'))) : '';
        $primaryFamCode = $famCode;

        $birthFamily = ['state' => 'none', 'code' => '', 'origin' => null, 'pending' => null];
        try {
            $birthFamily = (new BirthFamilyCodeService(\Src\Db::connect2()))->status($userId);
        } catch (\Throwable $e) {
            error_log('[ProfilepageService] Failed to load birth family status: ' . $e->getMessage());
        }

        $maternalFamCode = strtoupper(trim(ltrim((string)($birthFamily['code'] ?? ''), '#')));
        $hasMaternalCode = ($birthFamily['state'] ?? '') === 'active'
            && $maternalFamCode !== ''
            && $maternalFamCode !== $primaryFamCode;

        $maritalSurname = trim((string)($memberData['lastName'] ?? ''));
        $maidenSurname = trim((string)($memberData['maiden_name'] ?? ''));
        if ($maidenSurname === '' && $maternalFamCode !== '') {
            $cleanBirth = preg_replace('/[0-9]+/', '', $maternalFamCode) ?? '';
            $maidenSurname = $cleanBirth !== '' ? ucfirst(strtolower($cleanBirth)) : $maritalSurname;
        }

        $memberData['primaryFamCode'] = $primaryFamCode;
        $memberData['maternalFamCode'] = $hasMaternalCode ? $maternalFamCode : '';
        $memberData['hasMaternalCode'] = $hasMaternalCode;
        $memberData['maritalSurname'] = $maritalSurname;
        $memberData['maidenSurname'] = $maidenSurname;
        $memberData['birthFamily'] = $birthFamily;
        if ($hasMaternalCode && empty($memberData['otherFamCode'])) {
            $memberData['otherFamCode'] = $maternalFamCode;
        }

        // Fetch marriage / anniversary date from family_unions or events
        $marriageDate = null;
        try {
            $db = \Src\Db::connect2();
            $uStmt = $db->prepare("
                SELECT fu.marriage_date, fu.marriage_year
                FROM family_unions fu
                JOIN family_nodes fn ON (fn.id = fu.partner_1_id OR fn.id = fu.partner_2_id)
                WHERE fn.user_id = ? AND fu.is_current = 1 AND fu.union_type = 'married'
                ORDER BY fu.id DESC LIMIT 1
            ");
            $uStmt->execute([$userId]);
            $uRow = $uStmt->fetch(\PDO::FETCH_ASSOC);
            if ($uRow && !empty($uRow['marriage_date'])) {
                $marriageDate = (string)$uRow['marriage_date'];
            }
            if (empty($marriageDate)) {
                $evtStmt = $db->prepare("SELECT eventDate FROM events WHERE id = ? AND eventType = 'Anniversary' LIMIT 1");
                $evtStmt->execute([$userId]);
                $evtDate = $evtStmt->fetchColumn();
                if ($evtDate) {
                    $marriageDate = (string)$evtDate;
                }
            }
        } catch (\Throwable $e) {
            error_log('[ProfilepageService] Failed to load wedding date: ' . $e->getMessage());
        }

        $memberData['marriage_date'] = $marriageDate;
        $memberData['wedding_date'] = $marriageDate;
        $isMarried = strcasecmp(trim((string)($memberData['marital_status'] ?? '')), 'Married') === 0
            || !empty($memberData['spouse_name']);
        $memberData['needs_wedding_date_prompt'] = ($isMarried && empty($marriageDate));

        $approvalService = new \App\service\FamilyCodeApprovalService(\Src\Db::connect2());

        // Dynamic 20-minute escalation: broadcast any overdue pending requests to all family members
        try {
            $approvalService->escalateOverdueRequests(null, 5);
        } catch (\Throwable $escErr) {
            error_log('[ProfilepageService] Escalation check warning: ' . $escErr->getMessage());
        }

        // Check if the current user has an active pending approval request (unapproved status)
        $userPendingRequest = null;
        try {
            $userPendingRequest = $approvalService->getUserPendingRequest($userId);
        } catch (\Throwable $penErr) {
            error_log('[ProfilepageService] User pending request check error: ' . $penErr->getMessage());
        }

        $isPendingApproval = ($userPendingRequest !== null);

        $famCodes = $_SESSION['famCodes'] ?? ($famCode !== '' ? [$famCode] : []);
        if (!is_array($famCodes)) {
            $famCodes = [$famCodes];
        }
        if ($famCode !== '' && !in_array($famCode, $famCodes, true)) {
            $famCodes[] = $famCode;
        }
        if ($hasMaternalCode && !in_array($maternalFamCode, $famCodes, true)) {
            $famCodes[] = $maternalFamCode;
        }

        if ($isPendingApproval) {
            // When pending, strip all family codes from active session so private feeds fail closed
            $famCodes = [];
            $_SESSION['famCodes'] = [];
            $_SESSION['famCode'] = '';
            $_SESSION['is_pending_approval'] = true;
        } else {
            $_SESSION['famCodes'] = array_values(array_unique(array_filter(array_map('strval', $famCodes))));
            unset($_SESSION['is_pending_approval']);
        }

        $pendingFamilyRequests = [];
        if (!$isPendingApproval) {
            try {
                $pendingFamilyRequests = $approvalService->getPendingApprovalsWithDetails($userId, $famCode);
            } catch (\Throwable $e) {
                error_log('[ProfilepageService] Failed to load pending family requests: ' . $e->getMessage());
            }
        }

        $pendingMigrations = [
            'incomingNudges' => [],
            'incomingRequests' => [],
            'totalPendingCount' => 0
        ];
        if (!$isPendingApproval) {
            try {
                $pendingMigrations = (new \App\services\FamilyMigrationService(\Src\Db::connect2()))
                    ->getPendingMigrationsForUser($userId, $famCode);
            } catch (\Throwable $e) {
                error_log('[ProfilepageService] Failed to load pending migrations: ' . $e->getMessage());
            }
        }

        $allFamilyMembers = !$isPendingApproval ? (new DataAll())->getAllMembers($userId) : [];
        $familyRoster = array_values(array_map(static fn(array $m): array => [
            'id' => (string)($m['id'] ?? ''),
            'firstName' => (string)($m['firstName'] ?? ''),
            'lastName' => (string)($m['lastName'] ?? ''),
            'img' => (string)($m['img'] ?? ($m['profilePics'] ?? '')),
        ], $allFamilyMembers));

        return [
            'memberData' => $memberData,
            'famCode' => $famCode,
            'primaryFamCode' => $primaryFamCode,
            'maternalFamCode' => $hasMaternalCode ? $maternalFamCode : '',
            'hasMaternalCode' => $hasMaternalCode,
            'maritalSurname' => $maritalSurname,
            'maidenSurname' => $maidenSurname,
            'birthFamily' => $birthFamily,
            'friendRequests' => !$isPendingApproval ? DataAll::getFriendRequestData($userId, "Request sent") : [],
            'posts' => ($includeFeedPayload && !$isPendingApproval) ? $this->feedRepo->getFeedPostsAsArray($userId, (array)$famCodes, 50, 0) : [],
            'comments' => ($includeFeedPayload && !$isPendingApproval) ? Post::getAllCommentProfilePics() : [],
            'events' => !$isPendingApproval ? DataAll::getEventDataByFamCode((array)$famCodes) : [],
            'post2Id' => $includeFeedPayload ? Post::postLink2Id($userId) : [],
            'pics' => $includeFeedPayload ? Post::getAllPostPics($userId) : [],
            'totalFamilyMembers' => count($allFamilyMembers),
            'familyRoster' => $familyRoster,
            'unclaimedMatch' => !$isPendingApproval ? FamilyClaimService::findFuzzyUnclaimedMatches($famCode, $userId, $memberData) : null,
            'pendingFamilyRequests' => $pendingFamilyRequests,
            'pendingMigrations' => $pendingMigrations,
            'isPendingApproval' => $isPendingApproval,
            'pendingApprovalData' => $userPendingRequest,
        ];
    }
}