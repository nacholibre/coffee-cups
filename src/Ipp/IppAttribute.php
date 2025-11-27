<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * Represents an IPP attribute with its tag, name, and value(s).
 */
class IppAttribute
{
    /**
     * @param IppValueTag $tag The value tag for this attribute
     * @param string $name The attribute name
     * @param mixed $value The attribute value (can be array for multi-valued)
     */
    public function __construct(
        public readonly IppValueTag $tag,
        public readonly string $name,
        public readonly mixed $value,
    ) {
    }

    public static function charset(string $value): self
    {
        return new self(IppValueTag::CHARSET, 'attributes-charset', $value);
    }

    public static function naturalLanguage(string $value): self
    {
        return new self(IppValueTag::NATURAL_LANGUAGE, 'attributes-natural-language', $value);
    }

    public static function printerUri(string $uri): self
    {
        return new self(IppValueTag::URI, 'printer-uri', $uri);
    }

    public static function jobUri(string $uri): self
    {
        return new self(IppValueTag::URI, 'job-uri', $uri);
    }

    public static function requestingUserName(string $name): self
    {
        return new self(IppValueTag::NAME_WITHOUT_LANGUAGE, 'requesting-user-name', $name);
    }

    public static function jobName(string $name): self
    {
        return new self(IppValueTag::NAME_WITHOUT_LANGUAGE, 'job-name', $name);
    }

    public static function documentName(string $name): self
    {
        return new self(IppValueTag::NAME_WITHOUT_LANGUAGE, 'document-name', $name);
    }

    public static function documentFormat(string $mimeType): self
    {
        return new self(IppValueTag::MIME_MEDIA_TYPE, 'document-format', $mimeType);
    }

    public static function copies(int $copies): self
    {
        return new self(IppValueTag::INTEGER, 'copies', $copies);
    }

    public static function sides(string $sides): self
    {
        return new self(IppValueTag::KEYWORD, 'sides', $sides);
    }

    public static function orientation(int $orientation): self
    {
        return new self(IppValueTag::ENUM, 'orientation-requested', $orientation);
    }

    public static function printQuality(int $quality): self
    {
        return new self(IppValueTag::ENUM, 'print-quality', $quality);
    }

    public static function keyword(string $name, string $value): self
    {
        return new self(IppValueTag::KEYWORD, $name, $value);
    }

    public static function integer(string $name, int $value): self
    {
        return new self(IppValueTag::INTEGER, $name, $value);
    }

    public static function boolean(string $name, bool $value): self
    {
        return new self(IppValueTag::BOOLEAN, $name, $value);
    }

    public static function text(string $name, string $value): self
    {
        return new self(IppValueTag::TEXT_WITHOUT_LANGUAGE, $name, $value);
    }

    public static function name(string $name, string $value): self
    {
        return new self(IppValueTag::NAME_WITHOUT_LANGUAGE, $name, $value);
    }

    public static function uri(string $name, string $value): self
    {
        return new self(IppValueTag::URI, $name, $value);
    }

    public static function mimeMediaType(string $name, string $value): self
    {
        return new self(IppValueTag::MIME_MEDIA_TYPE, $name, $value);
    }
}
