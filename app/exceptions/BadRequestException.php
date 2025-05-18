<?php

namespace App\exceptions;



class BadRequestException extends HttpExceptions
{
  public function __construct(string $message = "Bad Request")
  {
    parent::__construct($message, 400);
  }
}
