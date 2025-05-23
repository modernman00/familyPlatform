<?php

namespace App\Exceptions;

class UnauthorisedException extends HttpExceptions
{
  public function __construct(string $message = "Unauthorized")
  {
    parent::__construct($message, 401);
  }
}
