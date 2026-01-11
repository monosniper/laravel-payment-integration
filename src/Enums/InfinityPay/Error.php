<?php

namespace Monosniper\LaravelPayment\Enums\InfinityPay;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum Error: int
{
    use BaseEnum;

    case SUCCESS = 0;
    case INVOICE_ISSUED = 1;
    case TRANSACTION_CONFIRMATION = 2;
    case SIGN_CHECK_FAILED = -1;
    case INCORRECT_PARAMETER_AMOUNT = -2;
    case ACTION_NOT_FOUND = -3;
    case ALREADY_PAID = -4;
    case USER_DOES_NOT_EXIST = -5;
    case TRANSACTION_DOES_NOT_EXIST = -6;
    case FAILED_TO_UPDATE_USER = -7;
    case INFINITYPAY_ERROR = -8;
    case TRANSACTION_CANCELLED = -9;
    case VENDOR_NOT_FOUND = -10;
    case TRANSACTION_TYPE_INCORRECT = -11;
}
