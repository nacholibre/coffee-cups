<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * IPP operation codes as defined in RFC 2911.
 */
enum IppOperation: int
{
    // Print operations
    case PRINT_JOB = 0x0002;
    case PRINT_URI = 0x0003;
    case VALIDATE_JOB = 0x0004;
    case CREATE_JOB = 0x0005;
    case SEND_DOCUMENT = 0x0006;
    case SEND_URI = 0x0007;
    case CANCEL_JOB = 0x0008;
    case GET_JOB_ATTRIBUTES = 0x0009;
    case GET_JOBS = 0x000A;
    case GET_PRINTER_ATTRIBUTES = 0x000B;
    case HOLD_JOB = 0x000C;
    case RELEASE_JOB = 0x000D;
    case RESTART_JOB = 0x000E;
    case PAUSE_PRINTER = 0x0010;
    case RESUME_PRINTER = 0x0011;
    case PURGE_JOBS = 0x0012;

    // CUPS-specific operations
    case CUPS_GET_DEFAULT = 0x4001;
    case CUPS_GET_PRINTERS = 0x4002;
    case CUPS_ADD_MODIFY_PRINTER = 0x4003;
    case CUPS_DELETE_PRINTER = 0x4004;
    case CUPS_GET_CLASSES = 0x4005;
    case CUPS_ADD_MODIFY_CLASS = 0x4006;
    case CUPS_DELETE_CLASS = 0x4007;
    case CUPS_ACCEPT_JOBS = 0x4008;
    case CUPS_REJECT_JOBS = 0x4009;
    case CUPS_SET_DEFAULT = 0x400A;
    case CUPS_GET_DEVICES = 0x400B;
    case CUPS_GET_PPDS = 0x400C;
    case CUPS_MOVE_JOB = 0x400D;
    case CUPS_AUTHENTICATE_JOB = 0x400E;
    case CUPS_GET_PPD = 0x400F;
    case CUPS_GET_DOCUMENT = 0x4027;
}
