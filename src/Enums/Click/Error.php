<?php

namespace Monosniper\LaravelPayment\Enums\Click;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum Error: string
{
    use BaseEnum;

    case SUCCESS = '0';
    case INVALID_SIGN = '-1';
    case INVALID_AMOUNT = '-2';
    case INVALID_ACTION = '-3';
    case ALREADY_PAID = '-4';
    case INVALID_USER = '-5';
    case INVALID_TRANSACTION = '-6';
    case FAILED_UPDATE_USER = '-7';
    case INVALID_REQUEST = '-8';
    case TRANSACTION_CANCELLED = '-9';
}
