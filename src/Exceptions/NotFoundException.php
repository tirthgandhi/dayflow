<?php

namespace HRMS\Exceptions;

class NotFoundException extends \Exception
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message);
    }
}
