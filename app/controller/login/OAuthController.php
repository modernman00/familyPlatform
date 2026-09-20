<?php
declare(strict_types=1);

namespace App\controller\login;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
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

    private function getStateSecret(): string
    {
        $candidates = [
            $_ENV['JWT_SECRET'] ?? null,
            $_ENV['APP_SECRET'] ?? null,
            $_ENV['APP_KEY'] ?? null,
            getenv('JWT_SECRET') ?: null,
            getenv('APP_SECRET') ?: null,
            getenv('APP_KEY') ?: null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return 'family-oauth-state-secret';
    }

    private function buildSignedState(): string
    {
        $invite = is_string($_GET['invite'] ?? null) ? trim((string)$_GET['invite']) : (is_string($_SESSION['invite_token'] ?? null) ? (string)$_SESSION['invite_token'] : null);
        $ref = is_string($_GET['ref'] ?? null) ? trim((string)$_GET['ref']) : (is_array($_SESSION['pending_referral'] ?? null) && !empty($_SESSION['pending_referral']['token']) ? (string)$_SESSION['pending_referral']['token'] : null);
        $claimNode = isset($_GET['claim_node']) && is_numeric($_GET['claim_node']) ? (int)$_GET['claim_node'] : null;

        $payload = [
            'rnd'          => bin2hex(random_bytes(16)),
            'invite_token' => $invite,
            'ref_token'    => $ref,
            'claim_node'   => $claimNode,
            'iat'          => time(),
        ];

        $json = (string)json_encode($payload, JSON_UNESCAPED_SLASHES);
        $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $secret = $this->getStateSecret();
        $sig = hash_hmac('sha256', $encoded, $secret);

        return $encoded . '.' . $sig;
    }

    /**
     * @return array{invite_token: string|null, ref_token: string|null, claim_node: int|null}
     */
    private function assertAndUnpackState(): array
    {
        $requestState = is_string($_GET['state'] ?? null) ? (string)$_GET['state'] : (is_string($_POST['state'] ?? null) ? (string)$_POST['state'] : '');
        if ($requestState === '') {
            http_response_code(400);
            exit('Missing OAuth state parameter. Please start sign-in again.');
        }

        // 1. Signed HMAC State (Cryptographically authenticated & stateless)
        if (str_contains($requestState, '.')) {
            [$encoded, $sig] = explode('.', $requestState, 2);
            $secret = $this->getStateSecret();
            $expectedSig = hash_hmac('sha256', $encoded, $secret);
            if (!hash_equals($expectedSig, $sig)) {
                error_log('[OAuth SecOps] Tampered OAuth state signature detected.');
                http_response_code(400);
                exit('Invalid or tampered OAuth state. Please start sign-in again.');
            }

            $decodedJson = base64_decode(strtr($encoded, '-_', '+/'));
            $data = json_decode((string)$decodedJson, true);
            if (!is_array($data)) {
                return ['invite_token' => null, 'ref_token' => null, 'claim_node' => null];
            }

            // Verify TTL (15 minutes expiration to prevent replay attacks)
            $iat = isset($data['iat']) && is_numeric($data['iat']) ? (int)$data['iat'] : 0;
            if ($iat > 0 && (time() - $iat > 900)) {
                error_log('[OAuth SecOps] Expired OAuth state (age > 900s).');
                http_response_code(400);
                exit('Sign-in session expired. Please start sign-in again.');
            }

            // Restore pending referral in session if valid
            if (!empty($data['ref_token'])) {
                $refToken = (string)$data['ref_token'];
                $resolved = \App\services\FamilyRecommendationService::verifyAndResolveReferralToken($refToken);
                if ($resolved !== null) {
                    $_SESSION['pending_referral'] = [
                        'inviter_id'   => $resolved['inviter_id'],
                        'inviter_name' => $resolved['inviter_name'],
                        'token'        => $refToken,
                    ];
                }
            }

            // Restore invite token in session
            if (!empty($data['invite_token'])) {
                $_SESSION['invite_token'] = (string)$data['invite_token'];
            }

            unset($_SESSION['oauth2state']);

            return [
                'invite_token' => !empty($data['invite_token']) ? (string)$data['invite_token'] : null,
                'ref_token'    => !empty($data['ref_token']) ? (string)$data['ref_token'] : null,
                'claim_node'   => !empty($data['claim_node']) ? (int)$data['claim_node'] : null,
            ];
        }

        // 2. Legacy Stateful Fallback (Session check)
        $sessionState = $_SESSION['oauth2state'] ?? '';
        unset($_SESSION['oauth2state']);

        if ($sessionState === '' || !hash_equals($sessionState, $requestState)) {
            http_response_code(400);
            exit('Invalid or missing OAuth state. Please start sign-in again.');
        }

        return ['invite_token' => null, 'ref_token' => null, 'claim_node' => null];
    }

    public function googleRedirect(): void
    {
        $provider = $this->getGoogleProvider();
        $state = $this->buildSignedState();
        $_SESSION['oauth2state'] = $state;
        $authUrl = $provider->getAuthorizationUrl(['state' => $state]);
        header('Location: ' . $authUrl);
        exit;
    }

    public function facebookRedirect(): void
    {
        $provider = $this->getFacebookProvider();
        $state = $this->buildSignedState();
        $_SESSION['oauth2state'] = $state;
        $authUrl = $provider->getAuthorizationUrl(['state' => $state]);
        header('Location: ' . $authUrl);
        exit;
    }

    public function appleRedirect(): void
    {
        $clientId = (string)($_ENV['APPLE_CLIENT_ID'] ?? getenv('APPLE_CLIENT_ID') ?: 'com.myfamilyplatform.web');
        $redirectUri = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost'), '/') . '/auth/apple/callback';
        $state = $this->buildSignedState();
        $_SESSION['oauth2state'] = $state;

        $authUrl = 'https://appleid.apple.com/auth/authorize?' . http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code id_token',
            'response_mode' => 'form_post',
            'scope'         => 'name email',
            'state'         => $state,
        ]);

        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback(): void
    {
        $context = $this->assertAndUnpackState();

        $provider = $this->getGoogleProvider();

        try {
            /** @var \League\OAuth2\Client\Token\AccessToken $token */
            $token = $provider->getAccessToken('authorization_code', [
                'code' => is_string($_GET['code'] ?? null) ? $_GET['code'] : ''
            ]);

            /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
            $user = $provider->getResourceOwner($token);
            // Google only returns an email once the provider has verified it.
            $this->handleSocialLogin($user->getEmail(), $user->getFirstName(), $user->getLastName(), $user->getId(), 'google', true, $context);

        } catch (\Exception $e) {
            error_log('[OAuth google] ' . $e->getMessage());
            http_response_code(502);
            exit('Sign-in with Google failed. Please try again.');
        }
    }

    public function facebookCallback(): void
    {
        $context = $this->assertAndUnpackState();

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
            $this->handleSocialLogin($user->getEmail(), $user->getFirstName(), $user->getLastName(), $user->getId(), 'facebook', $emailVerified, $context);

        } catch (\Exception $e) {
            error_log('[OAuth facebook] ' . $e->getMessage());
            http_response_code(502);
            exit('Sign-in with Facebook failed. Please try again.');
        }
    }

    public function appleCallback(): void
    {
        $context = $this->assertAndUnpackState();

        $idToken = is_string($_POST['id_token'] ?? null) ? (string)$_POST['id_token'] : '';
        if ($idToken === '') {
            http_response_code(400);
            exit('Missing Apple ID token.');
        }

        // Cryptographically verify ID token against Apple's live JWKS public keys
        $verifiedClaims = $this->verifyAndParseAppleIdToken($idToken);

        $appleUserId = $verifiedClaims['sub'];
        $email = $verifiedClaims['email'];
        $emailVerified = $verifiedClaims['email_verified'];

        // User name object is only sent by Apple on first authorization
        $firstName = null;
        $lastName = null;
        if (!empty($_POST['user']) && is_string($_POST['user'])) {
            $userObj = json_decode($_POST['user'], true);
            if (is_array($userObj) && !empty($userObj['name']) && is_array($userObj['name'])) {
                $firstName = isset($userObj['name']['firstName']) && is_string($userObj['name']['firstName']) ? $userObj['name']['firstName'] : null;
                $lastName = isset($userObj['name']['lastName']) && is_string($userObj['name']['lastName']) ? $userObj['name']['lastName'] : null;
            }
        }

        $this->handleSocialLogin($email, $firstName, $lastName, $appleUserId, 'apple', $emailVerified, $context);
    }

    /**
     * Cryptographically verifies Apple ID Token using Apple's JWKS public keys.
     *
     * @return array{sub: string, email: ?string, email_verified: bool}
     */
    private function verifyAndParseAppleIdToken(string $idToken): array
    {
        $keys = $this->getApplePublicKeys();
        if (empty($keys)) {
            error_log('[OAuth apple sec-ops] Failed to fetch or parse Apple JWKS public keys.');
            http_response_code(502);
            exit('Unable to verify Apple credentials at this time. Please try again.');
        }

        try {
            // Decodes JWT, validates signature against matching kid in JWKS, checks exp/nbf/iat
            $decoded = JWT::decode($idToken, $keys);
            $claims = (array)$decoded;
        } catch (\Throwable $e) {
            error_log('[OAuth apple sec-ops] Apple ID token verification failed: ' . $e->getMessage());
            http_response_code(401);
            exit('Apple authentication failed: invalid token signature or claims.');
        }

        // Validate standard Apple OpenID claims
        $iss = (string)($claims['iss'] ?? '');
        if ($iss !== 'https://appleid.apple.com') {
            error_log('[OAuth apple sec-ops] Invalid Apple issuer: ' . $iss);
            http_response_code(401);
            exit('Apple authentication failed: invalid token issuer.');
        }

        $expectedAud = (string)($_ENV['APPLE_CLIENT_ID'] ?? getenv('APPLE_CLIENT_ID') ?: '');
        if ($expectedAud !== '' && isset($claims['aud'])) {
            $aud = is_array($claims['aud']) ? $claims['aud'] : [(string)$claims['aud']];
            if (!in_array($expectedAud, $aud, true)) {
                error_log('[OAuth apple sec-ops] Apple audience mismatch. Expected: ' . $expectedAud . ', Got: ' . json_encode($claims['aud']));
                http_response_code(401);
                exit('Apple authentication failed: audience mismatch.');
            }
        }

        if (empty($claims['sub']) || !is_string($claims['sub'])) {
            error_log('[OAuth apple sec-ops] Apple ID token missing sub claim.');
            http_response_code(401);
            exit('Apple authentication failed: missing subject identifier.');
        }

        $email = isset($claims['email']) && is_string($claims['email']) ? $claims['email'] : null;
        $emailVerified = !empty($claims['email_verified']) && ($claims['email_verified'] === true || $claims['email_verified'] === 'true');

        return [
            'sub'            => (string)$claims['sub'],
            'email'          => $email,
            'email_verified' => $emailVerified,
        ];
    }

    /**
     * @return array<string, Key>
     */
    private function getApplePublicKeys(): array
    {
        $cacheFile = __DIR__ . '/../../../bootstrap/cache/apple_jwks.json';
        $jwks = null;

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
            $cachedContent = @file_get_contents($cacheFile);
            if (is_string($cachedContent)) {
                $jwks = json_decode($cachedContent, true);
            }
        }

        if (!is_array($jwks) || empty($jwks['keys'])) {
            $ch = curl_init('https://appleid.apple.com/auth/keys');
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 5,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_USERAGENT      => 'FamilyPlatform-OAuth/2.0',
                ]);
                /** @var string|false $res */
                $res = curl_exec($ch);
                if (is_string($res)) {
                    $parsed = json_decode($res, true);
                    if (is_array($parsed) && !empty($parsed['keys'])) {
                        $jwks = $parsed;
                        @file_put_contents($cacheFile, $res);
                    }
                }
            }
        }

        if (!is_array($jwks) || empty($jwks['keys'])) {
            return [];
        }

        try {
            return JWK::parseKeySet($jwks, 'RS256');
        } catch (\Throwable $e) {
            error_log('[OAuth apple sec-ops] JWK parseKeySet failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param array{invite_token?: string|null, ref_token?: string|null, claim_node?: int|null} $context
     */
    private function handleSocialLogin(?string $email, ?string $firstName, ?string $lastName, ?string $providerId, string $provider, bool $emailVerified = false, array $context = []): void
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
        if ($userRow && !empty($userRow['id']) && !$emailVerified) {
            http_response_code(409);
            exit('An account already exists for this email. Please sign in with your password.');
        }

        if ($userRow && !empty($userRow['id'])) {
            $userId = (string) $userRow['id'];
            $column = match($provider) {
                'google'   => 'google_id',
                'apple'    => 'apple_id',
                'facebook' => 'facebook_id',
                default    => throw new \InvalidArgumentException("Unsupported OAuth provider {$provider}")
            };
            $update = $db->prepare("UPDATE account SET {$column} = ? WHERE id = ?");
            $update->execute([$providerId, $userId]);

            // Log them in
            $this->loginUser($userId, $userRow);
        } else {
            // New user, store in session and redirect to register with context
            $_SESSION['oauth_pending'] = [
                'email' => $email,
                'firstName' => $firstName ?? '',
                'lastName' => $lastName ?? '',
                'provider' => $provider,
                'providerId' => $providerId
            ];
            
            $query = ['oauth' => '1'];
            if (!empty($context['invite_token'])) {
                $query['invite'] = $context['invite_token'];
            }
            if (!empty($context['claim_node'])) {
                $query['claim_node'] = (string)$context['claim_node'];
            }
            if (!empty($context['ref_token'])) {
                $query['ref'] = $context['ref_token'];
            }

            redirect('/register?' . http_build_query($query));
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
