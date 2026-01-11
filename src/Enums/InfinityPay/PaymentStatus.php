<?php

namespace Monosniper\LaravelPayment\Enums\InfinityPay;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum PaymentStatus: int
{
    use BaseEnum;

    case PAYED = 2;
    case CANCELLED = 3;
}
