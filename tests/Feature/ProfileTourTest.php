<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\OnboardingController;
use App\services\ProfileTourService;
use PHPUnit\Framework\TestCase;

final class ProfileTourTest extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = dirname(__DIR__, 2);
        $_SESSION = [];
    }

    public function testAllowedTourKeysRejectArbitraryValues(): void
    {
        $service = new ProfileTourService();
        $this->assertTrue($service->isAllowedTourKey(ProfileTourService::PROFILE_TOUR_KEY));
        $this->assertFalse($service->isAllowedTourKey('arbitrary_key'));
        $this->assertFalse($service->isAllowedTourKey(''));
        $this->assertFalse($service->isAllowedTourKey('../etc/passwd'));
    }

    public function testSetDismissedRejectsEmptyUserOrInvalidTourKey(): void
    {
        $service = new ProfileTourService();

        $emptyUser = $service->setDismissed('', true);
        $this->assertSame('error', $emptyUser['status']);

        $badKey = $service->setDismissed('user_test_1', true, 'invalid_tour');
        $this->assertSame('error', $badKey['status']);
    }

    public function testSetAndGetDismissedRoundTrip(): void
    {
        $service = new ProfileTourService();
        $testUserId = 'tour_test_' . bin2hex(random_bytes(4));

        $initial = $service->isDismissed($testUserId);
        $this->assertFalse($initial);

        $saved = $service->setDismissed($testUserId, true);
        $this->assertSame('success', $saved['status']);
        $this->assertTrue($saved['dismissed']);

        // Clear session cache to verify DB persistence
        unset($_SESSION['profile_tour_dismissed_' . ProfileTourService::PROFILE_TOUR_KEY]);
        $this->assertTrue($service->isDismissed($testUserId));

        $reset = $service->setDismissed($testUserId, false);
        $this->assertSame('success', $reset['status']);
        $this->assertFalse($reset['dismissed']);

        unset($_SESSION['profile_tour_dismissed_' . ProfileTourService::PROFILE_TOUR_KEY]);
        $this->assertFalse($service->isDismissed($testUserId));
    }

    public function testUnauthenticatedGetTourPreferenceReturns401(): void
    {
        $_SESSION = [];
        $controller = new OnboardingController();

        ob_start();
        $controller->getProfileTourPreference();
        $output = (string)ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertSame('Not authenticated', $decoded['message'] ?? null);
    }

    public function testProfilePageAndTemplatesExposeTourTriggersAndScripts(): void
    {
        $leftCol = (string)file_get_contents($this->rootDir . '/resources/views/member/includes/leftColumn.blade.php');
        $profilePage = (string)file_get_contents($this->rootDir . '/resources/views/member/profilePage.blade.php');
        $rightCol = (string)file_get_contents($this->rootDir . '/resources/views/member/includes/rightColumn.blade.php');
        $howToUse = (string)file_get_contents($this->rootDir . '/resources/views/howToUse.blade.php');
        $routes = (string)file_get_contents($this->rootDir . '/app/router/engagementRoute.php');

        $this->assertStringContainsString('id="profileTourCardBtn"', $leftCol);
        $this->assertStringContainsString('id="profileTourQuickBtn"', $leftCol);
        $this->assertStringContainsString('data-profile-tour-trigger="true"', $leftCol);
        $this->assertStringContainsString('min-height: 44px', $leftCol);

        $this->assertStringContainsString('window.__FP_PROFILE_TOUR_CONFIG__', $profilePage);
        $this->assertStringContainsString('SovereignSpotlightTour.js', $profilePage);
        $this->assertStringContainsString('profileTour.js', $profilePage);

        $this->assertStringContainsString('id="familyNetworkCard"', $rightCol);
        $this->assertStringContainsString('Interactive Profile Tour', $howToUse);

        $this->assertStringContainsString('/api/profile/tour-preference', $routes);
    }
}
