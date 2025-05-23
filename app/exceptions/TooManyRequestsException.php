<?php

namespace App\Exceptions;



class TooManyRequestsException extends HttpExceptions
{
  public function __construct(string $message = "Bad Request")
  {
    parent::__construct($message, 429);
  }
}
