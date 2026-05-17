<?php

namespace App\Exceptions;

use RuntimeException;

class ServiceException extends RuntimeException
{
    public function __construct(string $message, public readonly int $statusCode = 500)
    {
        parent::__construct($message);
    }
}
