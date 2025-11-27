<?php

declare(strict_types=1);

namespace CoffeeCups;

/**
 * Represents a printer with its attributes.
 */
class Printer
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly string $uri,
        public readonly string $name,
        public readonly string $state,
        public readonly string $stateMessage,
        public readonly bool $isAcceptingJobs,
        public readonly array $attributes = [],
    ) {
    }

    /**
     * Create a Printer from IPP attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public static function fromAttributes(string $uri, array $attributes): self
    {
        $name = $attributes['printer-name'] ?? basename($uri);

        $stateValue = $attributes['printer-state'] ?? 0;
        $state = match ($stateValue) {
            3 => 'idle',
            4 => 'processing',
            5 => 'stopped',
            default => 'unknown',
        };

        $stateMessage = '';
        if (isset($attributes['printer-state-reasons'])) {
            $reasons = is_array($attributes['printer-state-reasons'])
                ? $attributes['printer-state-reasons']
                : [$attributes['printer-state-reasons']];
            $stateMessage = implode(', ', $reasons);
        }

        $isAcceptingJobs = $attributes['printer-is-accepting-jobs'] ?? true;

        return new self(
            uri: $uri,
            name: $name,
            state: $state,
            stateMessage: $stateMessage,
            isAcceptingJobs: $isAcceptingJobs,
            attributes: $attributes,
        );
    }

    public function isIdle(): bool
    {
        return $this->state === 'idle';
    }

    public function isProcessing(): bool
    {
        return $this->state === 'processing';
    }

    public function isStopped(): bool
    {
        return $this->state === 'stopped';
    }

    public function getLocation(): ?string
    {
        return $this->attributes['printer-location'] ?? null;
    }

    public function getInfo(): ?string
    {
        return $this->attributes['printer-info'] ?? null;
    }

    public function getMakeAndModel(): ?string
    {
        return $this->attributes['printer-make-and-model'] ?? null;
    }

    /**
     * @return string[]
     */
    public function getSupportedFormats(): array
    {
        $formats = $this->attributes['document-format-supported'] ?? [];

        return is_array($formats) ? $formats : [$formats];
    }

    /**
     * @return string[]
     */
    public function getSupportedMediaSizes(): array
    {
        $sizes = $this->attributes['media-supported'] ?? [];

        return is_array($sizes) ? $sizes : [$sizes];
    }

    public function supportsColor(): bool
    {
        $colorSupported = $this->attributes['color-supported'] ?? false;

        return $colorSupported === true;
    }

    public function supportsDuplex(): bool
    {
        $sides = $this->attributes['sides-supported'] ?? [];
        if (!is_array($sides)) {
            $sides = [$sides];
        }

        return in_array('two-sided-long-edge', $sides, true)
            || in_array('two-sided-short-edge', $sides, true);
    }
}
