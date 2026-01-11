<?php

namespace Monosniper\LaravelPayment\Http\Requests\InfinityPay;

use Monosniper\LaravelPayment\Enums\InfinityPay\TransactionType;

class FiscalizationRequest extends BaseRequest
{
    public array $sign_fields = [
        'AGR_TRANS_ID',
        'TYPE',
    ];

    public function rules(): array
    {
        return [
            'AGR_TRANS_ID' => ['sometimes', 'nullable'],
            'TYPE' => ['sometimes', 'nullable', 'in:'.implode(',', TransactionType::values())],
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}
