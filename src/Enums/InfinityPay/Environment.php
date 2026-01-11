<?php

namespace Monosniper\LaravelPayment\Enums\InfinityPay;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum Environment: string
{
    use BaseEnum;

    case LIVE = 'live';
    case SANDBOX = 'sandbox';
}
