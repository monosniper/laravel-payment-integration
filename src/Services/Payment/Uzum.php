<?php

namespace Monosniper\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Monosniper\LaravelPayment\Base\OrderModel;
use Monosniper\LaravelPayment\Contracts\PaymentService;
use Monosniper\LaravelPayment\Http\Resources\UzumItemResource;

class Uzum implements PaymentService
{
    public Request $request;
    private string $terminal_id;
    private string $api_key;
    private string $host;
    private bool $is_test;

    const HOST = 'https://checkout-key.inplat-tech.com/api/v1/';
    const TEST_HOST = 'https://test-chk-api.uzumcheckout.uz/api/v1/';

    const SUCCESS_OPERATION_STATE = 'SUCCESS';
    const COMPLETE_OPERATION_TYPE = 'COMPLETE';
    const ORDER_STATUS_COMPLETED = 'COMPLETED';

    const ROUTE_REGISTER_PAYMENT = 'payment/register';
    const ROUTE_GET_ORDER_STATUS = 'payment/getOrderStatus';

    public function __construct()
    {
        $this->is_test = config('payment.uzum.is_test', true);

        $test_prefix = $this->is_test ? 'test.' : '';

        $this->terminal_id = config("payment.uzum.{$test_prefix}terminal_id");
        $this->api_key = config("payment.uzum.{$test_prefix}api_key");
        $this->host = $this->is_test ? self::TEST_HOST : self::HOST;
    }

    public function generateUrl(OrderModel $order): string
    {
        $data = [
            'successUrl' => $order->getSuccessUrl(),
            'failureUrl' => $order->getFailureUrl(),
            'viewType' => 'REDIRECT',
            'clientId' => (string) $order->getTransactionParam(),
            'currency' => 860,
            'orderNumber' => (string) $order->id,
            'sessionTimeoutSecs' => 1800,
            'amount' => $order->amount * 100,
            'merchantParams' => [
                'cart' => [
                    'cartId' => request()->ip(),
                    'receiptType' => 'PURCHASE',
                    'items' => UzumItemResource::collection($order->products),
                    'total' => $order->amount * 100,
                ],
            ],
            'paymentParams' => [
                'operationType' => 'PAYMENT',
                'payType' => 'ONE_STEP',
                'phoneNumber' => auth()->user()->phone,
            ]
        ];

        $response = $this->sendRequest(self::ROUTE_REGISTER_PAYMENT, $data);

        if (isset($response['result']['orderId'])) {
            $order->transaction()->create([
                'extra' => [
                    'orderId' => $response['result']['orderId'],
                ]
            ]);
        } else {
            info('UZUM Error: '. json_encode($response));
        }

        return $response['result']['paymentRedirectUrl'] ?? '';
    }

    private function sendRequest(string $route, array $data): ?array
    {
        return Http::withHeaders([
            'X-Terminal-Id' => $this->terminal_id,
            'X-API-Key' => $this->api_key,
            'Content-Language' => app()->getLocale() . '-' . strtoupper(app()->getLocale()),
        ])->post($this->host . $route, $data)->json();
    }

    private function getOrderStatus(string $orderId): array
    {
        $data = [
            'orderId' => $orderId,
        ];

        return $this->sendRequest(self::ROUTE_GET_ORDER_STATUS, $data);
    }

    private function performOrder(): void
    {
        if (
            $this->request->operationState === self::SUCCESS_OPERATION_STATE &&
            $this->request->operationType === self::COMPLETE_OPERATION_TYPE
        ) {
            $order = app(OrderModel::class)::find($this->request->orderNumber);

            if ($order) {
                $response = $this->getOrderStatus($this->request->orderId);

                if (isset($response['result']['status'])) {
                    if ($response['result']['status'] === self::ORDER_STATUS_COMPLETED) {
                        $order->update([
                            'is_payed' => true
                        ]);

                        try {
                            $order->onSuccessfulPay();
                        } catch (\Exception $exception) {
                            info("Failed onSuccessfulPay hook (Uzum, Order ID: $order->id): $exception");
                        }
                    }
                }
            }
        }
    }

    public function callback(Request $request): JsonResponse
    {
        $this->request = $request;
        $this->performOrder();

        return response()->json('ok');
    }
}
