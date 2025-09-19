<?php

declare(strict_types=1);

namespace App\router;

use Src\Exceptions\NotFoundException;

use AltoRouter;
use Src\Exceptions\HttpException;


class RouteDispatch
{
    protected $match;
    protected $controller;
    protected $method;

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
