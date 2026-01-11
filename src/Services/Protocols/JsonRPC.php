<?php

namespace Monosniper\LaravelPayment\Services\Protocols;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class JsonRPC
{
    static public function sendRequest(
        string $host,
        string $method,
        array $params,
        string $token = null
    ): array|null {
        $data = static::makeData($method, $params);

        $request = $token
            ? Http::withToken($token)->post($host, $data)
            : Http::post($host, $data);

        $response = $request->json();

        return $response['result'] ?? null;
    }

    static public function sendResponse(
        string $method,
        array $params
    ): Response {
        return response(static::makeData($method, $params));
    }

    static public function makeData(
        string $method,
        array $params,
    ): array {
        return [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => uniqid(),
        ];
    }
}