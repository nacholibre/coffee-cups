<?php

declare(strict_types=1);

namespace CoffeeCups\Ipp;

/**
 * IPP attribute group tags as defined in RFC 2911.
 */
enum IppAttributeGroup: int
{
    case OPERATION_ATTRIBUTES = 0x01;
    case JOB_ATTRIBUTES = 0x02;
    case END_OF_ATTRIBUTES = 0x03;
    case PRINTER_ATTRIBUTES = 0x04;
    case UNSUPPORTED_ATTRIBUTES = 0x05;
}
