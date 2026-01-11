<?php

namespace Monosniper\LaravelPayment\Http\Requests\QuickPay;

use Illuminate\Foundation\Http\FormRequest;
use Monosniper\LaravelPayment\Enums\QuickPay\PaymentCode;
use Monosniper\LaravelPayment\Enums\QuickPay\PaymentType;

class
CallbackRequest extends FormRequest
{
    public function authorize(): bool {
        $request_signature = $this->header('x-api-sha256-signature');

        $data = $this->all();
        ksort($data);
        $encodedData = json_encode($data);
        $signature = hash_hmac(
            'sha256',
            $encodedData,
            config('payment.quickpay.secret_key')
        );

        return hash_equals($request_signature, $signature);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required'],
            'status' => ['required'],
            'amount' => ['required'],
            'currency' => ['required'],
            'order_id' => ['required'],
            'type' => ['required', 'in:'.implode(',', PaymentType::values())],
            'code' => ['required', 'in:'.implode(',', PaymentCode::values())],
        ];
    }
}
