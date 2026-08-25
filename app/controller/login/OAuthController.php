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

    public function googleCallback(): void
    {
        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            exit('Invalid state');
        }

        $provider = $this->getGoogleProvider();

        try {
            /** @var \League\OAuth2\Client\Token\AccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
            $user = $provider->getResourceOwner($token);
            $this->handleSocialLogin($user->getEmail(), $user->getFirstName(), $user->getLastName(), $user->getId(), 'google');

        } catch (\Exception $e) {
            exit('Something went wrong: ' . $e->getMessage());
        }
    }

    public function facebookCallback(): void
    {
        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            exit('Invalid state');
        }

        $provider = $this->getFacebookProvider();

        try {
            /** @var \League\OAuth2\Client\Token\AccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            /** @var \League\OAuth2\Client\Provider\FacebookUser $user */
            $user = $provider->getResourceOwner($token);
            $this->handleSocialLogin($user->getEmail(), $user->getFirstName(), $user->getLastName(), $user->getId(), 'facebook');

        } catch (\Exception $e) {
            exit('Something went wrong: ' . $e->getMessage());
        }
    }

    private function handleSocialLogin(?string $email, ?string $firstName, ?string $lastName, ?string $providerId, string $provider): void
    {
        if (!$email || !$providerId) {
            exit('Social login failed to retrieve essential information.');
        }
        $db = Db::connect2();
        
        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM account WHERE email = ?");
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();

        if ($userId) {
            // Update provider ID
            $column = $provider === 'google' ? 'google_id' : 'facebook_id';
            $update = $db->prepare("UPDATE account SET {$column} = ? WHERE id = ?");
            $update->execute([$providerId, $userId]);

            // Log them in
            $this->loginUser((int)$userId);
        } else {
            // New user, store in session and redirect to register
            $_SESSION['oauth_pending'] = [
                'email' => $email,
                'firstName' => $firstName ?? '',
                'lastName' => $lastName ?? '',
                'provider' => $provider,
                'providerId' => $providerId
            ];
            
            redirect('register?oauth=1');
        }
    }

    private function loginUser(int $userId): void
    {
        $db = Db::connect2();
        // Fetch family code
        $stmt = $db->prepare("SELECT famCode FROM personal WHERE id = ?");
        $stmt->execute([$userId]);
        $famCode = $stmt->fetchColumn();

        $_SESSION['id'] = $userId;
        $_SESSION['manager_id'] = $userId;
        $_SESSION['famCode'] = $famCode;
        $_SESSION['loggedIn'] = true;

        // Create JWT cookie
        \Src\JwtHandler::issueLoginCookie(['id' => $userId, 'role' => 'users']);

        redirect('/profilePage');
    }
}
