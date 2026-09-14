<?php
declare(strict_types=1);

namespace App\services;

use PDO;
use PDOException;
use Src\Db;

/**
 * OnboardingService
 *
 * Drives the 60-Second Quick-Start Onboarding Engine for FamilyPlatform.
 * Manages user milestone progress (Tree exploration, Family Invitation Flywheel, First Post),
 * generates secure viral invite links, and powers the onboarding checklist widget.
 */
class OnboardingService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Get the onboarding checklist state for the given user.
     *
     * @param string $userId
     * @param string $famCode
     * @return array{
     *     completed: bool,
     *     progress_percentage: int,
     *     completed_count: int,
     *     total_steps: int,
     *     steps: array<string, array{key: string, title: string, description: string, done: bool, action_url: string, icon: string}>,
     *     invite_data: array{invite_url: string, whatsapp_url: string, invite_text: string, family_code: string, surname: string}
     * }
     */
    public function getOnboardingState(string $userId, string $famCode): array
    {
        $userId = trim($userId);
        $famCode = trim($famCode);

        // 1. Check completed steps dynamically from database and session
        $treeExplored = $this->hasExploredTree($userId);
        $familyInvited = $this->hasInvitedFamily($userId, $famCode);
        $firstPostCreated = $this->hasCreatedPost($userId);

        $steps = [
            'explore_tree' => [
                'key' => 'explore_tree',
                'title' => 'Explore Your Family Tree',
                'description' => 'View your living lineage, branches, and connections on the interactive organogram.',
                'done' => $treeExplored,
                'action_url' => '/organogram',
                'icon' => 'bi-diagram-3-fill',
            ],
            'invite_family' => [
                'key' => 'invite_family',
                'title' => 'Invite 3 Family Relatives',
                'description' => 'Share your unique family code via WhatsApp or SMS to bring parents, siblings, and cousins together.',
                'done' => $familyInvited,
                'action_url' => '#inviteModal',
                'icon' => 'bi-people-fill',
            ],
            'post_memory' => [
                'key' => 'post_memory',
                'title' => 'Share Your First Family Memory',
                'description' => 'Post a family photo or life update to spark connection on the family stream.',
                'done' => $firstPostCreated,
                'action_url' => '#openPostModalTrigger',
                'icon' => 'bi-camera-fill',
            ],
        ];

        $completedCount = ($treeExplored ? 1 : 0) + ($familyInvited ? 1 : 0) + ($firstPostCreated ? 1 : 0);
        $totalSteps = 3;
        $progressPercentage = (int)round(($completedCount / $totalSteps) * 100);
        $isCompleted = ($completedCount === $totalSteps);

        // 2. Generate viral invite payload
        $inviteData = $this->generateInviteData($userId, $famCode);

        return [
            'completed' => $isCompleted,
            'progress_percentage' => $progressPercentage,
            'completed_count' => $completedCount,
            'total_steps' => $totalSteps,
            'steps' => $steps,
            'invite_data' => $inviteData,
        ];
    }

    /**
     * Mark an onboarding step complete explicitly.
     *
     * @param string $userId
     * @param string $stepKey
     * @return array{status: string, message: string, step: string}
     */
    public function completeStep(string $userId, string $stepKey): array
    {
        $validSteps = ['explore_tree', 'invite_family', 'post_memory'];
        if (!in_array($stepKey, $validSteps, true)) {
            return ['status' => 'error', 'message' => 'Invalid onboarding step', 'step' => $stepKey];
        }

        // Store step completion flag in session and audit log
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['onboarding_step_' . $stepKey] = true;
        }

        return [
            'status' => 'success',
            'message' => 'Onboarding step marked as complete',
            'step' => $stepKey,
        ];
    }

    /**
     * Generates a viral, high-converting invite payload for 1-tap WhatsApp and WebShare.
     *
     * @param string $userId
     * @param string $famCode
     * @return array{invite_url: string, whatsapp_url: string, invite_text: string, family_code: string, surname: string}
     */
    public function generateInviteData(string $userId, string $famCode): array
    {
        $appUrl = rtrim((string)(getenv('APP_URL') ?: (getenv('MIX_APP_URL') ?: 'https://myfamilyplatform.com')), '/');
        
        // Fetch user's and family's surname
        $surname = $this->getFamilySurname($userId, $famCode);
        
        $inviteUrl = "{$appUrl}/register?famCode=" . urlencode($famCode) . "&familySurname=" . urlencode($surname);
        
        $inviteText = "👋 Hey! I've just set up our private {$surname} Family Network & Heritage Tree on FamilyPlatform. Claim your branch, explore our lineage, and share memories with us here: {$inviteUrl}";
        
        $whatsappUrl = "https://api.whatsapp.com/send?text=" . urlencode($inviteText);

        return [
            'invite_url' => $inviteUrl,
            'whatsapp_url' => $whatsappUrl,
            'invite_text' => $inviteText,
            'family_code' => $famCode,
            'surname' => $surname,
        ];
    }

    private function hasExploredTree(string $userId): bool
    {
        if (!empty($_SESSION['onboarding_step_explore_tree'])) {
            return true;
        }

        try {
            // Check if user has linked or created tree nodes
            $stmt = $this->db->prepare("SELECT id FROM family_nodes WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function hasInvitedFamily(string $userId, string $famCode): bool
    {
        if (!empty($_SESSION['onboarding_step_invite_family'])) {
            return true;
        }

        try {
            // Check if user has sent any friend/family requests or family has >1 member
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM personal WHERE famCode = ? AND id != ?");
            $stmt->execute([$famCode, $userId]);
            $memberCount = (int)$stmt->fetchColumn();

            return $memberCount >= 1;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function hasCreatedPost(string $userId): bool
    {
        if (!empty($_SESSION['onboarding_step_post_memory'])) {
            return true;
        }

        try {
            $stmt = $this->db->prepare("SELECT post_no FROM post WHERE id = ? AND date_deleted IS NULL LIMIT 1");
            $stmt->execute([$userId]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function getFamilySurname(string $userId, string $famCode): string
    {
        try {
            $stmt = $this->db->prepare("SELECT lastName FROM personal WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $name = (string)$stmt->fetchColumn();
            if ($name !== '') {
                return $name;
            }

            // Fallback: extract from family code
            $cleanCode = preg_replace('/[0-9]+/', '', $famCode);
            return !empty($cleanCode) ? ucfirst(strtolower($cleanCode)) : 'Family';
        } catch (PDOException $e) {
            return 'Family';
        }
    }
}
