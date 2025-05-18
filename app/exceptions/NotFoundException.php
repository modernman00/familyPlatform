<?php

namespace App\Exceptions;

class NotFoundException extends HttpExceptions
{
  public function __construct(string $message = "Not Found")
  {
    parent::__construct($message, 404);
  }
}
