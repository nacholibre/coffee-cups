<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

use CoffeeCups\Exceptions\IppException;

/**
 * Represents an IPP response.
 */
class IppResponse
{
    /** @var array{major: int, minor: int} */
    private array $version;

    private int $statusCode;
    private int $requestId;

    /** @var array<string, array<string, mixed>> */
    private array $attributes;

    /**
     * @throws IppException
     */
    public function __construct(string $data)
    {
        $decoder = new IppDecoder($data);
        $decoded = $decoder->decode();

        $this->version = $decoded['version'];
        $this->statusCode = $decoded['statusCode'];
        $this->requestId = $decoded['requestId'];
        $this->attributes = $decoded['attributes'];
    }

    /**
     * @return array{major: int, minor: int}
     */
    public function getVersion(): array
    {
        return $this->version;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatus(): ?IppStatus
    {
        return IppStatus::tryFrom($this->statusCode);
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 0x0000 && $this->statusCode <= 0x00FF;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOperationAttributes(): array
    {
        return $this->attributes['operation'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getJobAttributes(): array
    {
        return $this->attributes['job'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getPrinterAttributes(): array
    {
        return $this->attributes['printer'] ?? [];
    }

    public function getAttribute(string $group, string $name): mixed
    {
        return $this->attributes[$group][$name] ?? null;
    }

    public function getJobId(): ?int
    {
        return $this->getJobAttributes()['job-id'] ?? null;
    }

    public function getJobUri(): ?string
    {
        return $this->getJobAttributes()['job-uri'] ?? null;
    }

    public function getJobState(): ?int
    {
        return $this->getJobAttributes()['job-state'] ?? null;
    }

    public function getStatusMessage(): ?string
    {
        return $this->getOperationAttributes()['status-message'] ?? null;
    }
}
