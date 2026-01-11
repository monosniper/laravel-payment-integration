<?php

namespace Monosniper\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Monosniper\LaravelPayment\Http\Requests\InfinityPay\CancelRequest;
use Monosniper\LaravelPayment\Http\Requests\InfinityPay\FiscalizationRequest;
use Monosniper\LaravelPayment\Http\Requests\InfinityPay\InfoRequest;
use Monosniper\LaravelPayment\Http\Requests\InfinityPay\NotifyRequest;
use Monosniper\LaravelPayment\Http\Requests\InfinityPay\PayRequest;
use Monosniper\LaravelPayment\Http\Requests\InfinityPay\StatementRequest;
use Monosniper\LaravelPayment\Services\Payment\InfinityPay;

class InfinityPayController
{
    private InfinityPay $service;

    public function __construct(InfinityPay $infinityPay)
    {
        $this->service = $infinityPay;
    }

    public function info(InfoRequest $request): JsonResponse
    {
        return $this->service->info($request);
    }

    public function pay(PayRequest $request): JsonResponse
    {
        return $this->service->pay($request);
    }

    public function notify(NotifyRequest $request): JsonResponse
    {
        return $this->service->notify($request);
    }

    public function cancel(CancelRequest $request): JsonResponse
    {
        return $this->service->cancel($request);
    }

    public function statement(StatementRequest $request): JsonResponse
    {
        return $this->service->statement($request);
    }

    public function fiscalization(FiscalizationRequest $request): JsonResponse
    {
        return $this->service->fiscalization($request);
    }
}