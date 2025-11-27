<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * IPP status codes as defined in RFC 2911.
 */
enum IppStatus: int
{
    // Successful status codes
    case SUCCESSFUL_OK = 0x0000;
    case SUCCESSFUL_OK_IGNORED_OR_SUBSTITUTED_ATTRIBUTES = 0x0001;
    case SUCCESSFUL_OK_CONFLICTING_ATTRIBUTES = 0x0002;

    // Client error status codes
    case CLIENT_ERROR_BAD_REQUEST = 0x0400;
    case CLIENT_ERROR_FORBIDDEN = 0x0401;
    case CLIENT_ERROR_NOT_AUTHENTICATED = 0x0402;
    case CLIENT_ERROR_NOT_AUTHORIZED = 0x0403;
    case CLIENT_ERROR_NOT_POSSIBLE = 0x0404;
    case CLIENT_ERROR_TIMEOUT = 0x0405;
    case CLIENT_ERROR_NOT_FOUND = 0x0406;
    case CLIENT_ERROR_GONE = 0x0407;
    case CLIENT_ERROR_REQUEST_ENTITY_TOO_LARGE = 0x0408;
    case CLIENT_ERROR_REQUEST_VALUE_TOO_LONG = 0x0409;
    case CLIENT_ERROR_DOCUMENT_FORMAT_NOT_SUPPORTED = 0x040A;
    case CLIENT_ERROR_ATTRIBUTES_OR_VALUES_NOT_SUPPORTED = 0x040B;
    case CLIENT_ERROR_URI_SCHEME_NOT_SUPPORTED = 0x040C;
    case CLIENT_ERROR_CHARSET_NOT_SUPPORTED = 0x040D;
    case CLIENT_ERROR_CONFLICTING_ATTRIBUTES = 0x040E;
    case CLIENT_ERROR_COMPRESSION_NOT_SUPPORTED = 0x040F;
    case CLIENT_ERROR_COMPRESSION_ERROR = 0x0410;
    case CLIENT_ERROR_DOCUMENT_FORMAT_ERROR = 0x0411;
    case CLIENT_ERROR_DOCUMENT_ACCESS_ERROR = 0x0412;

    // Server error status codes
    case SERVER_ERROR_INTERNAL_ERROR = 0x0500;
    case SERVER_ERROR_OPERATION_NOT_SUPPORTED = 0x0501;
    case SERVER_ERROR_SERVICE_UNAVAILABLE = 0x0502;
    case SERVER_ERROR_VERSION_NOT_SUPPORTED = 0x0503;
    case SERVER_ERROR_DEVICE_ERROR = 0x0504;
    case SERVER_ERROR_TEMPORARY_ERROR = 0x0505;
    case SERVER_ERROR_NOT_ACCEPTING_JOBS = 0x0506;
    case SERVER_ERROR_BUSY = 0x0507;
    case SERVER_ERROR_JOB_CANCELED = 0x0508;
    case SERVER_ERROR_MULTIPLE_DOCUMENT_JOBS_NOT_SUPPORTED = 0x0509;

    public function isSuccessful(): bool
    {
        return $this->value >= 0x0000 && $this->value <= 0x00FF;
    }

    public function isClientError(): bool
    {
        return $this->value >= 0x0400 && $this->value <= 0x04FF;
    }

    public function isServerError(): bool
    {
        return $this->value >= 0x0500 && $this->value <= 0x05FF;
    }

    public function getMessage(): string
    {
        return match ($this) {
            self::SUCCESSFUL_OK => 'Successful',
            self::CLIENT_ERROR_BAD_REQUEST => 'Bad request',
            self::CLIENT_ERROR_FORBIDDEN => 'Forbidden',
            self::CLIENT_ERROR_NOT_AUTHENTICATED => 'Not authenticated',
            self::CLIENT_ERROR_NOT_AUTHORIZED => 'Not authorized',
            self::CLIENT_ERROR_NOT_FOUND => 'Not found',
            self::SERVER_ERROR_INTERNAL_ERROR => 'Internal server error',
            self::SERVER_ERROR_OPERATION_NOT_SUPPORTED => 'Operation not supported',
            self::SERVER_ERROR_SERVICE_UNAVAILABLE => 'Service unavailable',
            default => 'Unknown status',
        };
    }
}
