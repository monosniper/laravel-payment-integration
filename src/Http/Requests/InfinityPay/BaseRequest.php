<?php

namespace Monosniper\LaravelPayment\Http\Requests\InfinityPay;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Monosniper\LaravelPayment\Enums\InfinityPay\Error;
use Monosniper\LaravelPayment\Enums\InfinityPay\TransactionType;
use Monosniper\LaravelPayment\Services\Payment\InfinityPay;

class BaseRequest extends FormRequest
{
    private array $_sign_fields = ['SIGN_TIME', 'SIGN_STRING'];

    protected function missParams(): bool {
        return !empty(
            array_diff(
                array_merge($this->sign_fields, $this->_sign_fields),
                array_keys($this->input())
            )
        );
    }

    public function authorize(): bool {
        $request_signature = $this->SIGN_STRING;

        if(!$request_signature || $this->missParams()) return false;

        $signature = md5(implode('', [
            config('payment.infinitypay.secret_key'),
            ...$this->only($this->sign_fields),
            $this->SIGN_TIME,
        ]));

        return hash_equals($request_signature, $signature);
    }

    protected function failedAuthorization()
    {
        if($this->missParams()) {
            throw new HttpResponseException(
                app(InfinityPay::class)->sendResponse(Error::INFINITYPAY_ERROR)
            );
        }

        throw new HttpResponseException(
            app(InfinityPay::class)->sendResponse(Error::SIGN_CHECK_FAILED)
        );
    }

    protected function failedValidation(Validator $validator)
    {
        if($this->filled('TYPE') && !in_array($this->input('TYPE'), TransactionType::values())) {
            throw new HttpResponseException(
                app(InfinityPay::class)->sendResponse(Error::TRANSACTION_TYPE_INCORRECT)
            );
        }

        throw new HttpResponseException(
            app(InfinityPay::class)->sendResponse(Error::INFINITYPAY_ERROR)
        );
    }

    public function rules(): array
    {
        return [
            ...array_combine(
                $this->sign_fields,
                array_map(fn() => ['sometimes', 'nullable'], $this->sign_fields)
            ),
            'SIGN_TIME' => ['required'],
            'SIGN_STRING' => ['required'],
        ];
    }
}