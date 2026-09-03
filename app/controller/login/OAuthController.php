<?php
declare(strict_types=1);

namespace App\controller\login;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;
use Src\Db;

class OAuthController
{
    private function getGoogleProvider(): Google
    {
        return new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'] ?? 'dummy_id',
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'dummy_secret',
            'redirectUri'  => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/') . '/auth/google/callback',
        ]);
    }

    private function getFacebookProvider(): Facebook
    {
        return new Facebook([
            'clientId'          => $_ENV['FACEBOOK_CLIENT_ID'] ?? 'dummy_id',
            'clientSecret'      => $_ENV['FACEBOOK_CLIENT_SECRET'] ?? 'dummy_secret',
            'redirectUri'       => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/') . '/auth/facebook/callback',
            'graphApiVersion'   => 'v19.0',
        ]);
    }

    public function googleRedirect(): void
    {
        $provider = $this->getGoogleProvider();
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function facebookRedirect(): void
    {
        $provider = $this->getFacebookProvider();
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * CSRF/state validation for an OAuth callback.
     *
     * The old check only rejected on a *mismatch* when a session state existed —
     * so a callback with no prior `googleRedirect` (no `$_SESSION['oauth2state']`)
     * sailed through, letting an attacker replay their own `code` to log a victim
     * into the attacker's account (login-CSRF / session fixation). Now the
     * session state MUST be present and match, constant-time, single-use.
     */
    private function assertOauthState(): void
    {
        $sessionState = $_SESSION['oauth2state'] ?? '';
        $requestState = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
        unset($_SESSION['oauth2state']); // single use, whatever the outcome

        if ($sessionState === '' || $requestState === '' || !hash_equals($sessionState, $requestState)) {
            http_response_code(400);
            exit('Invalid or missing OAuth state. Please start sign-in again.');
        }
    }

    public function googleCallback(): void
    {
        $this->assertOauthState();

        $provider = $this->getGoogleProvider();

        try {
            /** @var \League\OAuth2\Client\Token\AccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => is_string($_GET['code'] ?? null) ? $_GET['code'] : ''
            ]);

            /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
            $user = $provider->getResourceOwner($token);
            // Google only returns an email once the provider has verified it.
            $this->handleSocialLogin($user->getEmail(), $user->getFirstName(), $user->getLastName(), $user->getId(), 'google', true);

        } catch (\Exception $e) {
            error_log('[OAuth google] ' . $e->getMessage());
            http_response_code(502);
            exit('Sign-in with Google failed. Please try again.');
        }
    }

    public function facebookCallback(): void
    {
        $this->assertOauthState();

        $provider = $this->getFacebookProvider();

        try {
            /** @var \League\OAuth2\Client\Token\AccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => is_string($_GET['code'] ?? null) ? $_GET['code'] : ''
            ]);

            /** @var \League\OAuth2\Client\Provider\FacebookUser $user */
            $user = $provider->getResourceOwner($token);
            $fbData = $user->toArray();
            // Facebook can hand back an unverified email; only trust it if the
            // Graph API says so, else force the manual-registration path.
            $emailVerified = !empty($fbData['verified']) || !empty($fbData['is_verified']);
            $this->handleSocialLogin($user->getEmail(), $user->getFirstName(), $user->getLastName(), $user->getId(), 'facebook', $emailVerified);

        } catch (\Exception $e) {
            error_log('[OAuth facebook] ' . $e->getMessage());
            http_response_code(502);
            exit('Sign-in with Facebook failed. Please try again.');
        }
    }

    private function handleSocialLogin(?string $email, ?string $firstName, ?string $lastName, ?string $providerId, string $provider, bool $emailVerified = false): void
    {
        if (!$email || !$providerId) {
            http_response_code(400);
            exit('Sign-in did not return the information we need. Please register manually.');
        }
        $db = Db::connect2();

        // Check if user exists
        $stmt = $db->prepare("SELECT id, email, token_version FROM account WHERE email = ?");
        $stmt->execute([$email]);
        $userRow = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Account-takeover guard: never bind this OAuth identity to an existing
        // account (or log into it) unless the provider has *verified* the email.
        // An unverified provider email would otherwise be an account-takeover
        // vector — the person must prove control by logging in with a password.
        if ($userRow && !empty($userRow['id']) && !$emailVerified) {
            http_response_code(409);
            exit('An account already exists for this email. Please sign in with your password.');
        }

        if ($userRow && !empty($userRow['id'])) {
            $userId = (string) $userRow['id'];
            // Update provider ID
            $column = $provider === 'google' ? 'google_id' : 'facebook_id';
            $update = $db->prepare("UPDATE account SET {$column} = ? WHERE id = ?");
            $update->execute([$providerId, $userId]);

            // Log them in
            $this->loginUser($userId, $userRow);
        } else {
            // New user, store in session and redirect to register
            $_SESSION['oauth_pending'] = [
                'email' => $email,
                'firstName' => $firstName ?? '',
                'lastName' => $lastName ?? '',
                'provider' => $provider,
                'providerId' => $providerId
            ];
            
            redirect('/register?oauth=1');
        }
    }

    /** @param array<string, mixed> $userRow */
    private function loginUser(string $userId, array $userRow = []): void
    {
        // M-2 — defeat session fixation: the password path regenerates the id on
        // auth; the OAuth path must too, or an attacker who planted a session id
        // rides in on the victim's OAuth login.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $db = Db::connect2();
        // Fetch family code
        $stmt = $db->prepare("SELECT famCode FROM personal WHERE id = ?");
        $stmt->execute([$userId]);
        $famCode = (string) $stmt->fetchColumn();

        $_SESSION['id'] = $userId;
        $_SESSION['manager_id'] = $userId;
        $_SESSION['famCode'] = $famCode;
        $_SESSION['loggedIn'] = true;

        $tokenVersion = (int) ($userRow['token_version'] ?? 1);
        $email = $userRow['email'] ?? '';

        // Create JWT cookie with exact user payload expected by RoleMiddleware
        \Src\JwtHandler::issueLoginCookie([
            'id' => $userId,
            'email' => $email,
            'role' => 'users',
            'token_version' => $tokenVersion
        ]);

        if (!empty($famCode)) {
            try {
                $pStmt = $db->prepare("SELECT firstName, lastName, email, mobile, gender, day, month, year FROM personal p LEFT JOIN contact c ON c.id = p.id WHERE p.id = ?");
                $pStmt->execute([$userId]);
                $pData = $pStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
                \App\services\FamilyClaimService::claimOrInitializeNode($famCode, $userId, $pData);
            } catch (\Throwable $e) {
                error_log("FamilyClaimService OAuth hook error: " . $e->getMessage());
            }
        }

        redirect('/profilePage');
    }
}
