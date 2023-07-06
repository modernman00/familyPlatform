<?php

declare(strict_types=1);

namespace App\router;

use AltoRouter;
use PDOException;

class RouteDispatch
{
    /**
     * @var array|bool|null
     */
    protected $match;

    /**
     * @var null|string
     */
    protected $controller;

    /**
     * @var null|string
     */
    protected $method;
    public function __construct(AltoRouter $router)
    {
        try {

            $this->match = $router->match();
            
            if ($this->match) {
                $controllerAndFunction = explode('@', $this->match['target']);
                $this->controller = $controllerAndFunction[0];
                $this->method = $controllerAndFunction[1];
                if (method_exists($this->controller, $this->method)) {
                    call_user_func_array(array(new $this->controller, $this->method), array($this->match['params']));
                } else {
                    echo "This method {$this->method} is not defined in this {$this->controller}";
                }
            } else {

                view('error/genError');
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }
}
