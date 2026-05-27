<?php

namespace HRMS\Exceptions;

class AuthException extends \Exception
{
    public function __construct(string $message = 'Authentication required')
    {
        parent::__construct($message);
    }
}
