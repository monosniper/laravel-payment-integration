<?php

namespace Monosniper\LaravelPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Monosniper\LaravelPayment\Http\Requests\Octobank\CallbackRequest;
use Monosniper\LaravelPayment\Services\Payment\Octobank;

class OctobankController
{
    public function __invoke(CallbackRequest $request, Octobank $octobank): JsonResponse
    {
        info(json_encode($request->all()));
        return $octobank->callback($request);
    }
}