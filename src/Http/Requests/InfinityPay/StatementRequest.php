<?php

namespace Monosniper\LaravelPayment\Http\Requests\InfinityPay;

class StatementRequest extends BaseRequest
{
    public array $sign_fields = [
        'FROM',
        'TO',
    ];
}
