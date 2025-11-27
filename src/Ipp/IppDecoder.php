<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

use CoffeeCups\Exceptions\IppException;

/**
 * Decodes IPP binary response data.
 */
class IppDecoder
{
    private int $offset = 0;

    public function __construct(
        private readonly string $data,
    ) {
    }

    /**
     * @return array{version: array{major: int, minor: int}, statusCode: int, requestId: int, attributes: array<string, array<string, mixed>>}
     * @throws IppException
     */
    public function decode(): array
    {
        if (strlen($this->data) < 8) {
            throw new IppException('Invalid IPP response: too short');
        }

        $version = $this->readVersion();
        $statusCode = $this->readShort();
        $requestId = $this->readInt();

        $attributes = $this->readAttributes();

        return [
            'version' => $version,
            'statusCode' => $statusCode,
            'requestId' => $requestId,
            'attributes' => $attributes,
        ];
    }

    /**
     * @return array{major: int, minor: int}
     */
    private function readVersion(): array
    {
        $major = $this->readByte();
        $minor = $this->readByte();

        return ['major' => $major, 'minor' => $minor];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function readAttributes(): array
    {
        $groups = [];
        $currentGroup = null;
        $currentName = '';

        while ($this->offset < strlen($this->data)) {
            $tag = $this->readByte();

            // Check for attribute group tags
            if ($tag <= 0x0F) {
                if ($tag === IppAttributeGroup::END_OF_ATTRIBUTES->value) {
                    break;
                }

                $currentGroup = match ($tag) {
                    IppAttributeGroup::OPERATION_ATTRIBUTES->value => 'operation',
                    IppAttributeGroup::JOB_ATTRIBUTES->value => 'job',
                    IppAttributeGroup::PRINTER_ATTRIBUTES->value => 'printer',
                    IppAttributeGroup::UNSUPPORTED_ATTRIBUTES->value => 'unsupported',
                    default => 'unknown',
                };

                if (!isset($groups[$currentGroup])) {
                    $groups[$currentGroup] = [];
                }

                continue;
            }

            if ($currentGroup === null) {
                throw new IppException('Attribute found before attribute group tag');
            }

            // Read attribute name
            $nameLength = $this->readShort();

            if ($nameLength > 0) {
                $currentName = $this->readString($nameLength);
            }

            // Read attribute value
            $valueLength = $this->readShort();
            $value = $this->readValue($tag, $valueLength);

            // Store the attribute
            if (!isset($groups[$currentGroup][$currentName])) {
                $groups[$currentGroup][$currentName] = $value;
            } else {
                // Multi-valued attribute
                if (!is_array($groups[$currentGroup][$currentName]) ||
                    !array_is_list($groups[$currentGroup][$currentName])) {
                    $groups[$currentGroup][$currentName] = [$groups[$currentGroup][$currentName]];
                }
                $groups[$currentGroup][$currentName][] = $value;
            }
        }

        return $groups;
    }

    private function readValue(int $tag, int $length): mixed
    {
        return match ($tag) {
            IppValueTag::INTEGER->value,
            IppValueTag::ENUM->value => $this->readInt(),
            IppValueTag::BOOLEAN->value => $this->readByte() === 1,
            IppValueTag::RANGE_OF_INTEGER->value => $this->readRangeOfInteger(),
            IppValueTag::RESOLUTION->value => $this->readResolution(),
            IppValueTag::DATE_TIME->value => $this->readDateTime($length),
            default => $this->readString($length),
        };
    }

    private function readByte(): int
    {
        $byte = unpack('C', $this->data, $this->offset);
        $this->offset += 1;

        return $byte[1];
    }

    private function readShort(): int
    {
        $short = unpack('n', $this->data, $this->offset);
        $this->offset += 2;

        return $short[1];
    }

    private function readInt(): int
    {
        $int = unpack('N', $this->data, $this->offset);
        $this->offset += 4;

        // Handle signed integers
        $value = $int[1];
        if ($value >= 0x80000000) {
            $value -= 0x100000000;
        }

        return (int) $value;
    }

    private function readString(int $length): string
    {
        $string = substr($this->data, $this->offset, $length);
        $this->offset += $length;

        return $string;
    }

    /**
     * @return array{int, int}
     */
    private function readRangeOfInteger(): array
    {
        return [$this->readInt(), $this->readInt()];
    }

    /**
     * @return array{crossFeed: int, feed: int, units: int}
     */
    private function readResolution(): array
    {
        return [
            'crossFeed' => $this->readInt(),
            'feed' => $this->readInt(),
            'units' => $this->readByte(),
        ];
    }

    private function readDateTime(int $length): string
    {
        // IPP DateTime is 11 bytes
        $raw = $this->readString($length);
        if (strlen($raw) !== 11) {
            return $raw;
        }

        $unpacked = unpack('nyear/Cmonth/Cday/Chour/Cminute/Csecond/Cdeci/adir/Choffset/Cmoffset', $raw);
        if ($unpacked === false) {
            return $raw;
        }

        return sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d%s%02d:%02d',
            $unpacked['year'],
            $unpacked['month'],
            $unpacked['day'],
            $unpacked['hour'],
            $unpacked['minute'],
            $unpacked['second'],
            $unpacked['dir'],
            $unpacked['hoffset'],
            $unpacked['moffset'],
        );
    }
}
