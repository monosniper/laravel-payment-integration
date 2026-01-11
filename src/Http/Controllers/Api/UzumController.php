<?php

namespace Monosniper\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Monosniper\LaravelPayment\Http\Requests\Uzum\CallbackRequest;
use Monosniper\LaravelPayment\Services\Payment\Uzum;

class UzumController
{
    public function __invoke(CallbackRequest $request, Uzum $uzum): JsonResponse
    {
        return $uzum->callback($request);
    }
}