<?php

namespace App\Exceptions;

use App\Exceptions\HttpExceptions;

class CaptchaVerificationException extends HttpExceptions
{
  public function __construct(string $message = 'reCAPTCHA verification failed.')
  {
    parent::__construct($message, 400); // 400 = Bad Request
  }
}
