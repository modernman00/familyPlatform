<?php

declare(strict_types=1);

namespace App\router;

use App\Exceptions\NotFoundException;

use AltoRouter;
use PDOException;

class RouteDispatch
{
    protected $match;
    protected $controller;
    protected $method;

    public function dispatch(AltoRouter $router)
    {
        try {
            $this->match = $router->match();

            if ($this->match) {
                if (is_callable($this->match['target'])) {
                    call_user_func_array($this->match['target'], $this->match['params']);
                } else {
                    $controllerAndFunction = explode('@', $this->match['target']);
                    $this->controller = $controllerAndFunction[0];
                    $this->method = $controllerAndFunction[1];

                    if (method_exists($this->controller, $this->method)) {
                        call_user_func_array([new $this->controller, $this->method], $this->match['params']);
                    } else {
                        echo "Method {$this->method} not defined in {$this->controller}";
                        throw new NotFoundException("Method {$this->method}not found in {$this->controller}");
                    }
                }
            } else {

                view('error/genError', ["error" => "Invalid URL"]);
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }
}
