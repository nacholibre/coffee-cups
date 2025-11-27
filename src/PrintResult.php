<?php

declare(strict_types=1);

namespace CoffeeCups;

use CoffeeCups\Ipp\IppResponse;

/**
 * Represents the result of a print operation.
 */
class PrintResult
{
    public function __construct(
        private readonly bool $success,
        private readonly ?int $jobId,
        private readonly ?string $jobUri,
        private readonly ?string $message,
        private readonly int $statusCode,
    ) {
    }

    /**
     * Check if the print operation was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Get the error/status message.
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get the job ID.
     */
    public function getJobId(): ?int
    {
        return $this->jobId;
    }

    /**
     * Get the job URI.
     */
    public function getJobUri(): ?string
    {
        return $this->jobUri;
    }

    /**
     * Get the IPP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Create a PrintResult from an IPP response.
     */
    public static function fromResponse(IppResponse $response): self
    {
        return new self(
            success: $response->isSuccessful(),
            jobId: $response->getJobId(),
            jobUri: $response->getJobUri(),
            message: $response->getStatusMessage(),
            statusCode: $response->getStatusCode(),
        );
    }

    /**
     * Create a failed result.
     */
    public static function failed(string $message, int $statusCode = 0): self
    {
        return new self(
            success: false,
            jobId: null,
            jobUri: null,
            message: $message,
            statusCode: $statusCode,
        );
    }
}
