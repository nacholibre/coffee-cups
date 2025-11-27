<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * IPP value tags as defined in RFC 2911.
 */
enum IppValueTag: int
{
    // Out-of-band values
    case UNSUPPORTED = 0x10;
    case UNKNOWN = 0x12;
    case NO_VALUE = 0x13;

    // Integer values
    case INTEGER = 0x21;
    case BOOLEAN = 0x22;
    case ENUM = 0x23;

    // Octet string values
    case OCTET_STRING = 0x30;
    case DATE_TIME = 0x31;
    case RESOLUTION = 0x32;
    case RANGE_OF_INTEGER = 0x33;
    case BEGIN_COLLECTION = 0x34;
    case TEXT_WITH_LANGUAGE = 0x35;
    case NAME_WITH_LANGUAGE = 0x36;
    case END_COLLECTION = 0x37;

    // Character string values
    case TEXT_WITHOUT_LANGUAGE = 0x41;
    case NAME_WITHOUT_LANGUAGE = 0x42;
    case KEYWORD = 0x44;
    case URI = 0x45;
    case URI_SCHEME = 0x46;
    case CHARSET = 0x47;
    case NATURAL_LANGUAGE = 0x48;
    case MIME_MEDIA_TYPE = 0x49;
    case MEMBER_ATTR_NAME = 0x4A;
}
