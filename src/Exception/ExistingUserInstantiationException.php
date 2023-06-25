<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class ExistingUserInstantiationException extends Exception
{
    public function __construct(string $message = 'User is already logged in', int $code = 200, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}