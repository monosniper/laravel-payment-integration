<?php

namespace Monosniper\LaravelPayment\Enums\Payme;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum Error: string
{
    use BaseEnum;

    case AUTH = '-32504';
    case INVALID_PARAMS = '-32700';
    case INTERNAL_ERROR = '-32400';

    case INVALID_AMOUNT = '-31001';
    case INVALID_TRANSACTION = '-31003';
    case CANT_CANCEL_TRANSACTION = '-31007';
    case CANT_PERFORM = '-31008';
    case INVALID_ORDER_ID = '-31055';
    case ALREADY_HAS_TRANSACTION = '-31056';
}
