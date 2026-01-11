<?php

namespace Monosniper\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Monosniper\LaravelPayment\Http\Requests\Click\CallbackRequest;
use Monosniper\LaravelPayment\Services\Payment\Click;

class ClickController
{
    public function __invoke(CallbackRequest $request, Click $click): JsonResponse
    {
        return $click->callback($request);
    }
}