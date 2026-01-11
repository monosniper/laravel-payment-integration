<?php

namespace Monosniper\LaravelPayment\Enums\InfinityPay;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum TransactionType: string
{
    use BaseEnum;

    case PAYMENT = 'PAYMENT';
    case CANCEL = 'CANCEL';
}
