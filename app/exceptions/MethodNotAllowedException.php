<?php

namespace App\Exceptions;

class MethodNotAllowedException extends HttpExceptions
{
  public function __construct(string $message = "Not Found")
  {
    parent::__construct($message, 405);
  }
}
