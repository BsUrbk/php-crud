<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class UniqueUserException extends Exception
{
    public function __construct(string $message = 'Such user already exists', int $code = 200, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}