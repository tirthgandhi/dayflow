<?php

namespace HRMS\Exceptions;

class ForbiddenException extends \Exception
{
    public function __construct(string $message = 'Access denied')
    {
        parent::__construct($message);
    }
}
