<?php

declare(strict_types=1);

namespace App\interface;

interface ResponseRedirectorInterface
{
    public function redirect(string $uri, int $statusCode = 302): void;
}
