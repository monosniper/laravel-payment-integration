<?php

namespace Monosniper\LaravelPayment\Http\Requests\InfinityPay;

class PayRequest extends BaseRequest
{
    public array $sign_fields = [
        'AGR_TRANS_ID',
        'VENDOR_ID',
        'PAYMENT_ID',
        'PAYMENT_NAME',
        'MERCHANT_TRANS_ID',
        'MERCHANT_TRANS_AMOUNT',
        'ENVIRONMENT',
    ];
}
