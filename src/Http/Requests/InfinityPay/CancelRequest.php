<?php

namespace Monosniper\LaravelPayment\Http\Requests\InfinityPay;

class CancelRequest extends BaseRequest
{
    public array $sign_fields = [
        'AGR_TRANS_ID',
        'VENDOR_TRANS_ID',
    ];
}
