<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * Encodes data into IPP binary format.
 */
class IppEncoder
{
    private string $buffer = '';

    public function writeVersion(int $major = 2, int $minor = 0): self
    {
        $this->buffer .= pack('CC', $major, $minor);

        return $this;
    }

    public function writeOperation(IppOperation $operation): self
    {
        $this->buffer .= pack('n', $operation->value);

        return $this;
    }

    public function writeStatusCode(int $statusCode): self
    {
        $this->buffer .= pack('n', $statusCode);

        return $this;
    }

    public function writeRequestId(int $requestId): self
    {
        $this->buffer .= pack('N', $requestId);

        return $this;
    }

    public function writeAttributeGroup(IppAttributeGroup $group): self
    {
        $this->buffer .= pack('C', $group->value);

        return $this;
    }

    public function writeAttribute(IppAttribute $attribute): self
    {
        $values = is_array($attribute->value) ? $attribute->value : [$attribute->value];
        $isFirst = true;

        foreach ($values as $value) {
            $this->buffer .= pack('C', $attribute->tag->value);

            if ($isFirst) {
                $this->buffer .= pack('n', strlen($attribute->name));
                $this->buffer .= $attribute->name;
                $isFirst = false;
            } else {
                // Additional values have empty name
                $this->buffer .= pack('n', 0);
            }

            $this->writeValue($attribute->tag, $value);
        }

        return $this;
    }

    private function writeValue(IppValueTag $tag, mixed $value): void
    {
        match ($tag) {
            IppValueTag::INTEGER,
            IppValueTag::ENUM => $this->writeInteger($value),
            IppValueTag::BOOLEAN => $this->writeBoolean($value),
            IppValueTag::RANGE_OF_INTEGER => $this->writeRangeOfInteger($value),
            IppValueTag::RESOLUTION => $this->writeResolution($value),
            default => $this->writeString($value),
        };
    }

    private function writeInteger(int $value): void
    {
        $this->buffer .= pack('n', 4);
        $this->buffer .= pack('N', $value);
    }

    private function writeBoolean(bool $value): void
    {
        $this->buffer .= pack('n', 1);
        $this->buffer .= pack('C', $value ? 1 : 0);
    }

    private function writeString(string $value): void
    {
        $this->buffer .= pack('n', strlen($value));
        $this->buffer .= $value;
    }

    private function writeRangeOfInteger(array $range): void
    {
        $this->buffer .= pack('n', 8);
        $this->buffer .= pack('N', $range[0]);
        $this->buffer .= pack('N', $range[1]);
    }

    private function writeResolution(array $resolution): void
    {
        $this->buffer .= pack('n', 9);
        $this->buffer .= pack('N', $resolution[0]); // Cross-feed resolution
        $this->buffer .= pack('N', $resolution[1]); // Feed resolution
        $this->buffer .= pack('C', $resolution[2]); // Units
    }

    public function writeEndOfAttributes(): self
    {
        $this->buffer .= pack('C', IppAttributeGroup::END_OF_ATTRIBUTES->value);

        return $this;
    }

    public function writeData(string $data): self
    {
        $this->buffer .= $data;

        return $this;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function reset(): self
    {
        $this->buffer = '';

        return $this;
    }
}
