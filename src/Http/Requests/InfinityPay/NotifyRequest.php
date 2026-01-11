<?php

namespace Monosniper\LaravelPayment\Http\Requests\InfinityPay;

class NotifyRequest extends BaseRequest
{
    public array $sign_fields = [
        'AGR_TRANS_ID',
        'VENDOR_TRANS_ID',
        'STATUS',
    ];
}
