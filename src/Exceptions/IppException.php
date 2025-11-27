<?php

declare(strict_types=1);

namespace CoffeeCups\Exceptions;

/**
 * Exception thrown when IPP protocol errors occur.
 */
class IppException extends CupsException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
