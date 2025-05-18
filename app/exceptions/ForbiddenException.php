<?php

namespace App\exceptions;


class ForbiddenException extends HttpExceptions
{
  public function __construct(string $message = "Forbidden")
  {
    parent::__construct($message, 403);
  }
}
