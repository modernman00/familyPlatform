<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\AdulthoodTransitionService;
use App\services\ChildAccountService;
use Exception;
use Src\CheckToken;
use Src\Exceptions\BadRequestException;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;
use Src\Limiter;

/**
 * 🧒 ChildAccountController
 * 
 * Handles child account provisioning by verified parents,
 * child authentication (Family Code + Username + PIN),
 * and the "Turning 18" Adulthood Graduation milestone.
 */
final class ChildAccountController extends BaseController
{
    /**
     * Provisions a child account from a family tree node.
     * Route: POST /api/family/children/provision
     */
    public function provision(): void
    {
        try {
            header('Content-Type: application/json');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
                return;
            }

            $parentUserId = (string)($_SESSION['id'] ?? '');
            if ($parentUserId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please sign in as a parent.']);
                return;
            }

            // CSRF validation
            CheckToken::tokenCheck();

            // Rate limiting per parent
            Limiter::limit('child_prov_' . $parentUserId, 'post');

            $childNodeId = (int)($_POST['child_node_id'] ?? 0);
            $username = trim((string)($_POST['username'] ?? ''));
            $pin = trim((string)($_POST['pin'] ?? ''));
            $dobYear = !empty($_POST['dob_year']) ? trim((string)$_POST['dob_year']) : null;
            $dobMonth = !empty($_POST['dob_month']) ? trim((string)$_POST['dob_month']) : null;
            $dobDay = !empty($_POST['dob_day']) ? trim((string)$_POST['dob_day']) : null;

            $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $result = ChildAccountService::provisionChildAccount(
                parentUserId: $parentUserId,
                childNodeId: $childNodeId,
                username: $username,
                pin: $pin,
                dobYear: $dobYear,
                dobMonth: $dobMonth,
                dobDay: $dobDay,
                clientIp: $clientIp
            );

            http_response_code(201);
            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (ValidationException|BadRequestException $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('[ChildAccountController::provision] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred while provisioning the child account.']);
        }
    }

    /**
     * Authenticates a child using their Family Code, Username, and PIN.
     * Route: POST /login/child
     */
    public function login(): void
    {
        try {
            header('Content-Type: application/json');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
                return;
            }

            $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            Limiter::limit('child_login_' . $clientIp, 'login');

            $familyCode = trim((string)($_POST['family_code'] ?? ''));
            $username = trim((string)($_POST['username'] ?? ''));
            $pin = trim((string)($_POST['pin'] ?? ''));

            if ($familyCode === '' || $username === '' || $pin === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Family code, username, and PIN are required.']);
                return;
            }

            $child = ChildAccountService::authenticateChild($familyCode, $username, $pin);

            if ($child === null) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid family code, username, or PIN.']);
                return;
            }

            // Initialize authenticated session
            $_SESSION['id'] = $child['id'];
            $_SESSION['fName'] = $child['firstName'];
            $_SESSION['lName'] = $child['lastName'];
            $_SESSION['famCode'] = $child['famCode'];
            $_SESSION['famCodes'] = $child['famCode'];
            $_SESSION['is_child_account'] = $child['is_child_account'];
            $_SESSION['adulthood_status'] = $child['adulthood_status'];
            $_SESSION['loggedIn'] = true;
            $_SESSION['auth'] = [
                'identifyCust' => $child['id'],
                'flow' => 'authenticated',
                'type' => 'member',
            ];

            if (\session_status() === PHP_SESSION_ACTIVE) {
                \session_regenerate_id(true);
            }

            // Check if user has turned 18 on this login
            $graduation = AdulthoodTransitionService::checkAndProcessUserGraduation($child['id']);

            $safeChildName = htmlspecialchars($child['firstName'] ?? '', ENT_QUOTES, 'UTF-8');
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Sign-in successful. Welcome back, ' . $safeChildName . '!',
                'redirect' => '/member/profilePage',
                'graduation' => $graduation,
            ]); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            error_log('[ChildAccountController::login] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred during child sign-in.']);
        }
    }

    /**
     * Checks if current user has an unviewed 18th birthday graduation milestone.
     * Route: GET /api/adulthood/celebration
     */
    public function celebration(): void
    {
        try {
            header('Content-Type: application/json');

            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }

            $graduation = AdulthoodTransitionService::checkAndProcessUserGraduation($userId);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'has_celebration' => $graduation !== null,
                'celebration_data' => $graduation,
            ]);
        } catch (\Throwable $e) {
            error_log('[ChildAccountController::celebration] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to check graduation status.']);
        }
    }
}
