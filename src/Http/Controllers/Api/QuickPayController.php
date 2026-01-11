<?php

namespace Monosniper\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Monosniper\LaravelPayment\Http\Requests\QuickPay\CallbackRequest;
use Monosniper\LaravelPayment\Services\Payment\QuickPay;

class QuickPayController
{
    public function __invoke(CallbackRequest $request, QuickPay $quickPay): JsonResponse
    {
        return $quickPay->callback($request);
    }
}