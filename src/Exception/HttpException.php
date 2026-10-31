<?php

namespace App\Exception;

use Override;
use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{

    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
