<?php
declare(strict_types=1);

namespace App\router;

use Src\Exceptions\NotFoundException;

use AltoRouter;
use Src\Exceptions\HttpException;
use Src\Exceptions\UnauthorisedException;
use Src\functionality\SignIn;


final class RouteDispatch
{
    protected $match;
    protected $controller;
    protected $method;

    /**
     * @return void
     */
    public function dispatch(AltoRouter $router)
{
    try {
        $this->match = $router->match();

        if (!$this->match) {
            view('errors.404');
            return;
        }

        /* ----------  callable route ---------- */
        if (is_callable($this->match['target'])) {
            call_user_func_array($this->match['target'], $this->match['params']);
            return;
        }

        /* ----------  string controller@method ---------- */
        [$controller, $method] = explode('@', $this->match['target']);

        if (!method_exists($controller, $method)) {
            throw new NotFoundException("Method {$method} not found in {$controller}");
        }

        // --- FAIL-CLOSED ROUTER-LEVEL AUTHENTICATION ---
        // Whitelist of controllers that do NOT require authentication.
        $publicControllers = [
            'App\controller\Index',
            'App\controller\LoginController',
            'App\controller\RegisterController',
            'App\controller\LoginCodeController',
            'App\controller\ForgotPasswordController',
            'App\controller\CronController'
        ];

        if (!in_array($controller, $publicControllers, true)) {
            if (empty($_SESSION['id'])) {
                $tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'auth_token';
                if (empty($_COOKIE[$tokenName])) {
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        throw new UnauthorisedException("Unauthorized");
                    }
                    redirect('login');
                    return;
                }

                $VerifyJWT = SignIn::verify();
                if (!\is_array($VerifyJWT) || empty($VerifyJWT['id'])) {
                    throw new NotFoundException('Error Request_2');
                }
                $id = checkInput($VerifyJWT['id']);
                $_SESSION['id'] = $id;
            }
        }
        // -----------------------------------------------

        call_user_func_array([new $controller, $method], $this->match['params']);

    } catch (HttpException $e) {          // your custom layer
        showError($e);
    } catch (NotFoundException $e) {      // 404 you throw above
        http_response_code(404);
        view('errors.404', ['message' => $e->getMessage()]);
    } catch (\Throwable $e) {             // *** catch everything else ***
        error_log((string) $e);
        http_response_code(500);
        view('errors.500', ['exception' => $e]);   // or showError($e) if it accepts Throwable
    }
}
}
