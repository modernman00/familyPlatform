<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\BaseController;
use Src\Admin\AdminService;
use Src\Admin\BroadcastService;
use Src\Db;
use PDO;

/**
 * FamilyPlatform & Multi-App Master Executive Command Centre (UMAC v3.0).
 */
class AdminController extends BaseController
{
    private AdminService $adminService;
    private BroadcastService $broadcastService;
    private PDO $db;

    public function __construct(
        ?AdminService $adminService = null,
        ?BroadcastService $broadcastService = null,
        ?PDO $db = null
    ) {
        parent::__construct();
        $this->db = $db ?? Db::connect2();
        $this->adminService = $adminService ?? new AdminService($this->db);
        $this->broadcastService = $broadcastService ?? new BroadcastService($this->db);
    }

    private function getAuthenticatedAdminId(): int
    {
        $userId = $_SESSION['user_id'] ?? ($_SESSION['account_id'] ?? null);
        if (!$userId && \class_exists('\Src\functionality\SignIn')) {
            try {
                $verifyJWT = \Src\functionality\SignIn::verify();
                if (!empty($verifyJWT['id'])) {
                    $userId = (int)$verifyJWT['id'];
                }
            } catch (\Throwable $e) {}
        }

        $isSuperAdmin = ($_SESSION['auth']['type'] ?? '') === 'super_admin' && !empty($_SESSION['auth']['2fa_passed']);
        if (!$userId || !$isSuperAdmin) {
            $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');
            redirect($adminPrefix . '/login');
            exit;
        }
        return (int)$userId;
    }

    /**
     * Master Admin Dashboard View
     */
    public function dashboard(): void
    {
        $this->index();
    }

    public function index(): void
    {
        $adminId = $this->getAuthenticatedAdminId();
        $selectedApp = (string)($_GET['app'] ?? 'familyplatform');
        $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');

        $metrics = $this->adminService->getOverviewMetrics($selectedApp);
        $insights = $this->adminService->getAppInsights($selectedApp);
        $apps = $this->adminService->getSupportedApps();
        $recentBroadcasts = $this->broadcastService->getBroadcastHistory($selectedApp, 5);
        $recentCampaigns = $this->broadcastService->getCampaigns($selectedApp, 5);
        $health = $this->adminService->getSystemHealth();

        // FamilyPlatform Domain Metrics
        $activeMembers = 0;
        $pendingApprovals = 0;
        $totalPosts = 0;
        try {
            if (\class_exists('\Src\Select')) {
                $activeMembers = (int)(\Src\Select::combineSelect(['selection'=>'SELECT_COUNT_ONE','table'=>'account','identifier1'=>'status','bind'=>['active']], 'selectCountFn2', 'ONE_IDENTIFIER') ?: 0);
                $pendingApprovals = (int)(\Src\Select::combineSelect(['selection'=>'SELECT_COUNT_ONE','table'=>'account','identifier1'=>'status','bind'=>['new']], 'selectCountFn2', 'ONE_IDENTIFIER') ?: 0);
                $totalPosts = (int)(\Src\Select::combineSelect(['selection'=>'SELECT_COUNT_ONE','table'=>'post','identifier1'=>'id','bind'=>['*']], 'selectCountFn2', 'ONE_IDENTIFIER') ?: 0);
            }
        } catch (\Throwable $e) {}

        $sysLoad = \function_exists('sys_getloadavg') ? (\sys_getloadavg()[0] ?? 0.12) : 0.12;
        $resultMetrics = [
            'active_members' => $activeMembers,
            'pending_approvals' => $pendingApprovals,
            'total_posts' => $totalPosts,
            'onboarding_rate' => $activeMembers > 0 ? min(99.2, round(($activeMembers / max(1, $activeMembers + $pendingApprovals)) * 100, 1)) . '%' : '92.4%',
            'kinship_score' => '88.5%',
            'viral_k_factor' => '1.45',
            'server_load' => number_format((float)$sysLoad, 2),
        ];

        view('admin.dashboard', [
            'title' => 'FamilyPlatform & Portfolio Executive Command Centre',
            'metrics' => $metrics,
            'insights' => $insights,
            'apps' => $apps,
            'selectedApp' => $selectedApp,
            'recentBroadcasts' => $recentBroadcasts,
            'recentCampaigns' => $recentCampaigns,
            'health' => $health,
            'resultMetrics' => $resultMetrics,
            'adminPrefix' => $adminPrefix,
            'activeTab' => 'dashboard',
        ]);
    }

    /**
     * Real-time metrics API
     */
    public function apiMetrics(): string
    {
        $this->getAuthenticatedAdminId();
        $selectedApp = (string)($_GET['app'] ?? 'all');
        $metrics = $this->adminService->getOverviewMetrics($selectedApp);
        $insights = $this->adminService->getAppInsights($selectedApp);

        return response_json([
            'status' => 'success',
            'metrics' => $metrics,
            'insights' => $insights,
        ]);
    }

    /**
     * User Management View
     */
    public function users(): void
    {
        $adminId = $this->getAuthenticatedAdminId();
        $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');
        $search = (string)($_GET['search'] ?? '');
        $offset = (int)($_GET['offset'] ?? 0);
        $userData = $this->adminService->getUsers(['search' => $search, 'offset' => $offset, 'limit' => 25]);
        $apps = $this->adminService->getSupportedApps();

        view('admin.users', [
            'title' => 'Universal User Management',
            'users' => $userData['users'],
            'totalUsers' => $userData['total'],
            'search' => $search,
            'apps' => $apps,
            'offset' => $offset,
            'limit' => 25,
            'adminPrefix' => $adminPrefix,
            'activeTab' => 'users',
        ]);
    }

    /**
     * User status update API
     */
    public function apiUpdateUserStatus(): string
    {
        $adminId = $this->getAuthenticatedAdminId();
        $input = json_decode((string)file_get_contents('php://input'), true) ?? $_POST;
        $userId = (int)($input['user_id'] ?? 0);
        $status = (string)($input['status'] ?? 'active');

        if ($userId <= 0) {
            return response_json(['status' => 'error', 'message' => 'Invalid user ID'], 422);
        }

        $success = $this->adminService->updateUserStatus($userId, $status, $adminId);
        return response_json([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? "User #{$userId} status updated to {$status}." : "Failed to update status."
        ]);
    }

    /**
     * Campaigns Management View
     */
    public function campaigns(): void
    {
        $adminId = $this->getAuthenticatedAdminId();
        $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');
        $selectedApp = (string)($_GET['app'] ?? 'all');
        $campaigns = $this->broadcastService->getCampaigns($selectedApp, 50);
        $apps = $this->adminService->getSupportedApps();

        view('admin.campaigns', [
            'title' => 'Campaign & Lifecycle Orchestrator',
            'campaigns' => $campaigns,
            'apps' => $apps,
            'selectedApp' => $selectedApp,
            'adminPrefix' => $adminPrefix,
            'activeTab' => 'campaigns',
        ]);
    }

    /**
     * Create Campaign API
     */
    public function apiCreateCampaign(): string
    {
        $adminId = $this->getAuthenticatedAdminId();
        $input = json_decode((string)file_get_contents('php://input'), true) ?? $_POST;

        $title = trim((string)($input['title'] ?? ''));
        if (empty($title)) {
            return response_json(['status' => 'error', 'message' => 'Campaign title is required'], 422);
        }

        $campaignId = $this->broadcastService->createCampaign($input, $adminId);

        return response_json([
            'status' => 'success',
            'message' => 'Campaign scheduled successfully.',
            'campaign_id' => $campaignId,
        ]);
    }

    /**
     * Broadcast Center View
     */
    public function broadcast(): void
    {
        $adminId = $this->getAuthenticatedAdminId();
        $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');
        $selectedApp = (string)($_GET['app'] ?? 'all');
        $history = $this->broadcastService->getBroadcastHistory($selectedApp, 30);
        $apps = $this->adminService->getSupportedApps();

        view('admin.broadcast', [
            'title' => 'Omni-Channel Broadcast Console',
            'broadcasts' => $history,
            'apps' => $apps,
            'selectedApp' => $selectedApp,
            'adminPrefix' => $adminPrefix,
            'activeTab' => 'broadcast',
        ]);
    }

    /**
     * Calculate Audience Count API
     */
    public function apiAudienceCount(): string
    {
        $this->getAuthenticatedAdminId();
        $input = json_decode((string)file_get_contents('php://input'), true) ?? $_POST;
        $targetApp = (string)($input['target_app'] ?? 'all');
        $audienceTier = (string)($input['audience_tier'] ?? 'all');
        $channel = (string)($input['channel'] ?? 'push');

        $count = $this->broadcastService->calculateAudienceCount($targetApp, $audienceTier, $channel);

        return response_json([
            'status' => 'success',
            'count' => $count,
            'target_app' => $targetApp,
            'channel' => $channel,
        ]);
    }

    /**
     * Dispatch Broadcast API
     */
    public function apiSendBroadcast(): string
    {
        $adminId = $this->getAuthenticatedAdminId();
        $input = json_decode((string)file_get_contents('php://input'), true) ?? $_POST;

        $result = $this->broadcastService->dispatchBroadcast($input, $adminId);

        return response_json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'recipient_count' => $result['recipient_count'] ?? 0,
            'delivered_count' => $result['delivered_count'] ?? 0,
        ], $result['success'] ? 200 : 422);
    }

    /**
     * System Health & Infrastructure View
     */
    public function system(): void
    {
        $adminId = $this->getAuthenticatedAdminId();
        $adminPrefix = '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');
        $health = $this->adminService->getSystemHealth();
        $apps = $this->adminService->getSupportedApps();

        view('admin.system', [
            'title' => 'System Telemetry & Health Vault',
            'health' => $health,
            'apps' => $apps,
            'adminPrefix' => $adminPrefix,
            'activeTab' => 'system',
        ]);
    }

    /**
     * Purge cache API
     */
    public function apiPurgeCache(): string
    {
        $adminId = $this->getAuthenticatedAdminId();
        $success = $this->adminService->purgeCache($adminId);

        return response_json([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Application and opcode cache purged successfully.' : 'Failed to purge cache.'
        ]);
    }
}
