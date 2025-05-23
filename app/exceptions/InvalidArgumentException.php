<?php

namespace App\Exceptions;

class InvalidArgumentException extends HttpExceptions
{
  public function __construct(string $message = "Not Found")
  {
    parent::__construct($message, 400);
  }
}
