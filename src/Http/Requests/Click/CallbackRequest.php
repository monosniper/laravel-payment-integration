<?php

namespace Monosniper\LaravelPayment\Http\Requests\Click;

use Illuminate\Foundation\Http\FormRequest;
use Monosniper\LaravelPayment\Enums\Payme\Method;

class CallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
        ];
    }
}
