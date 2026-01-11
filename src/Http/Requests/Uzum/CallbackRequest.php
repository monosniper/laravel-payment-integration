<?php

namespace Monosniper\LaravelPayment\Http\Requests\Uzum;

use Illuminate\Foundation\Http\FormRequest;

class CallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'orderId' => ['required'],
            'orderNumber' => ['required'],
            'operationType' => ['required'],
            'operationState' => ['required'],
        ];
    }
}
