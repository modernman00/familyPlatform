<?php
declare(strict_types=1);

namespace App\controller\login;

use Src\{
  Utility
};

use Src\functionality\PasswordRecoveryService;

final class Forgot
{
  public string $table = 'login';
  public string $email;

  public function show(): void
  {
    try {
      $verify = $_GET['verify'] ?? null;
      if (!$verify) {
          redirect('/login');
      }

      unset($_SESSION['auth']['2FA_token_ts']);
      unset($_SESSION['auth']['identifyCust']);
      unset($_SESSION['auth']['pendingUser']);
      $_SESSION['auth']['flow'] = 'forgot_password';
      
      $_SESSION['auth']['showLogin'] = true;
      PasswordRecoveryService::show('login/forgot');
    } catch (\Throwable $th) {
      Utility::showError($th);
    }
  }

  public function verify(): void
  {
    try {
        \Src\CorsHandler::setHeaders();
        $input = \Src\functionality\middleware\GetRequestData::getRequestData();
        if (!$input) {
            throw new \Src\Exceptions\NotFoundException('There was no post data');
        }

        \App\controller\BaseController::validateCsrf($input);

        $_SESSION['auth']['flow'] = 'forgot_password';
        unset($_SESSION['auth']['pendingUser']);

        $nonProdEnvs = ['local', 'development', 'testing'];
        $appEnv = strtolower((string) ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production'));
        $isCaptchaV3 = !in_array($appEnv, $nonProdEnvs, true);

        if ($isCaptchaV3) {
            \Src\Recaptcha::verifyCaptchaEnterprise($input, 'FORGOT');
            unset($input['action'], $input['token']);
        }

        if (!isset($input['email']) || !is_string($input['email']) || trim($input['email']) === '') {
            throw new \Src\Exceptions\BadRequestException('Please enter your email address');
        }

        \Src\Limiter::limit($input['email']);

        $sanitised = \Src\LoginUtility::getSanitisedInputData($input, [
            'data' => ['email'],
            'min'  => [5],
            'max'  => [50],
        ]);

        $user = \Src\LoginUtility::useEmailToFindData($sanitised);
        if (empty($user)) {
            throw new \Src\Exceptions\NotFoundException('User not found');
        }

        unset($user['password']);

        $recoveryPayload = [
            'id' => (string) ($user['id'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => 'password_recovery',
            'token_version' => (int) ($user['token_version'] ?? 1),
        ];

        \Src\JwtHandler::jwtEncodeDataAndSetCookies($recoveryPayload, 'auth_forgot', 900);

        $pathToSentCodeNotification = (string) ($_ENV['PATH_TO_SENT_CODE_NOTIFICATION'] ?? 'msg/code');
        try {
            \Src\Token::generateSendTokenEmail($user, $pathToSentCodeNotification);
        } catch (\Throwable $mailEx) {
            $isTestOrDev = in_array($appEnv, $nonProdEnvs, true);
            $isSmtpError = str_contains(strtolower($mailEx->getMessage()), 'smtp')
                || str_contains(strtolower($mailEx->getMessage()), 'recipient')
                || str_contains(strtolower($mailEx->getMessage()), 'mail');
            if (!($isTestOrDev && $isSmtpError && !empty($_SESSION['auth']['2FA_token_ts']))) {
                throw $mailEx;
            }
        }

        unset($_SESSION['token']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        Utility::msgSuccess(200, 'Recovery token sent to your email successfully');
    } catch (\Throwable $th) {
      Utility::showError($th);
    }
  }
}
