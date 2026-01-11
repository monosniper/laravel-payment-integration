<?php

namespace Monosniper\LaravelPayment\Enums\QuickPay;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum PaymentType: int
{
    use BaseEnum;

    case PAYMENT = 1;
    case RETURN = 2;
}
