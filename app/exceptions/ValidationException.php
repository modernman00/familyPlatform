<?php

namespace App\Exceptions;



class ValidationException extends HttpExceptions
{
  public function __construct(string $message = "Bad Request")
  {
    parent::__construct($message, 422);
  }
}
