<?php

namespace Monosniper\LaravelPayment\Enums\QuickPay;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum PaymentStatus: string
{
    use BaseEnum;

    case SUCCESS = 'success';
    case FAIL = 'fail';
    case EXPIRED = 'expired';
    case REFUND = 'refund';
}
