<?php

namespace Monosniper\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Monosniper\LaravelPayment\Base\OrderModel;
use Monosniper\LaravelPayment\Contracts\PaymentService;
use Monosniper\LaravelPayment\Enums\InfinityPay\Error;
use Monosniper\LaravelPayment\Enums\InfinityPay\PaymentStatus;
use Monosniper\LaravelPayment\Enums\PaymentMethod;
use Monosniper\LaravelPayment\Enums\TransactionStatus;
use Monosniper\LaravelPayment\Helpers\Timestamp;
use Monosniper\LaravelPayment\Http\Resources\InfinityPayFiscalizationResource;
use Monosniper\LaravelPayment\Http\Resources\InfinityPayTransactionResource;
use Monosniper\LaravelPayment\Models\Transaction;

class InfinityPay implements PaymentService
{
    const HOST = 'https://gate.infinitypay.uz/pay/';
    const TEST_HOST = 'https://gate.infinitypay.uz/sandbox/';

    private string $vendor_id;
    private string $secret_key;
    private string $host;
    private int $time;
    public bool $is_test;
    private ?OrderModel $order;
    private ?Transaction $transaction;

    const TRANSACTION_STATE_CREATED = 0;
    const TRANSACTION_STATE_PAYED = 2;
    const TRANSACTION_STATE_CANCELLED = 3;

    public function __construct()
    {
        $this->vendor_id = config('payment.infinitypay.vendor_id');
        $this->secret_key = config('payment.infinitypay.secret_key');
        $this->is_test = config('payment.infinitypay.is_test');
        $this->host = $this->is_test
            ? self::TEST_HOST
            : self::HOST;
    }

    public function generateUrl(OrderModel $order): string
    {
        $this->order = $order;
        $this->time = (new Timestamp())();

        $params = [
            'VENDOR_ID' => $this->vendor_id,
            'MERCHANT_TRANS_ID' => $order->id,
            'MERCHANT_TRANS_AMOUNT' => $order->amount,
            'MERCHANT_CURRENCY' => 'sum',
            'MERCHANT_TRANS_NOTE' => 'test comment',
            'MERCHANT_TRANS_RETURN_URL' => $order->getSuccessUrl(),
            'SIGN_TIME' => $this->time,
            'SIGN_STRING' => $this->generateSignature(),
        ];

        return "$this->host?" . http_build_query($params);
    }

    private function generateSignature(): string
    {
        return md5(implode('', [
            $this->secret_key,
            $this->vendor_id,
            $this->order->id,
            $this->order->amount,
            'sum',
            $this->time,
        ]));
    }

    public function sendResponse(
        Error $error = Error::SUCCESS,
        array $extra = []
    ): JsonResponse
    {
        return response()->json([
            'ERROR' => $error->value,
            'ERROR_NOTE' => __('payment::payment.infinitypay.' . $error->value),
            ...$extra,
        ]);
    }

    private function checkOrder(): ?JsonResponse
    {
        if (!$this->order) {
            return $this->sendResponse(
                Error::USER_DOES_NOT_EXIST
            );
        }

        return null;
    }

    private function checkUser(): ?JsonResponse
    {
        if (!$this->order->user) {
            return $this->sendResponse(
                Error::USER_DOES_NOT_EXIST
            );
        }

        return null;
    }

    private function checkAmount(Request $request): ?JsonResponse
    {
        if ($this->order->amount !== $request->MERCHANT_TRANS_AMOUNT) {
            return $this->sendResponse(
                Error::INCORRECT_PARAMETER_AMOUNT
            );
        }

        return null;
    }

    private function getOrder(Request $request, string $key = 'MERCHANT_TRANS_ID'): void
    {
        $this->order = app(OrderModel::class)->find($request->$key);

        $this->transaction = $this->order?->transaction;
    }

    public function info(Request $request): JsonResponse
    {
        $this->getOrder($request);

        $checkOrder = $this->checkOrder();

        if ($checkOrder) return $checkOrder;

        return $this->sendResponse(extra: [
            'PARAMETERS' => []
        ]);
    }

    public function checkAlreadyPayed(): ?JsonResponse
    {
        if ($this->order->is_payed) {
            return $this->sendResponse(
                Error::ALREADY_PAID
            );
        }

        return null;
    }

    public function checkOrderCancelled(): ?JsonResponse
    {
        if ($this->transaction->status === TransactionStatus::CANCELLED) {
            return $this->sendResponse(
                Error::TRANSACTION_CANCELLED
            );
        }

        return null;
    }

    public function checkVendor(Request $request): ?JsonResponse
    {
        if ($this->vendor_id !== (string)$request->VENDOR_ID) {
            return $this->sendResponse(
                Error::VENDOR_NOT_FOUND
            );
        }

        return null;
    }

    public function createTransaction($request): ?JsonResponse
    {
        if ($this->order->transaction) {
            return $this->sendResponse(
                Error::ALREADY_PAID
            );
        }

        $this->transaction = $this->order->transaction()->create([
            'extra' => [
                'ENVIRONMENT' => $this->is_test ? 'sandbox' : 'live',
                'AGR_TRANS_ID' => $request->AGR_TRANS_ID,
                'DATE' => (new Timestamp())(),
                'STATE' => self::TRANSACTION_STATE_CREATED,
            ]
        ]);

        return null;
    }

    public function pay(Request $request): JsonResponse
    {
        $this->getOrder($request);

        $response = $this->checkOrder() ??
            $this->createTransaction($request) ??
            $this->checkAmount($request) ??
            $this->checkOrderCancelled() ??
            $this->checkAlreadyPayed() ??
            $this->checkUser() ??
            $this->checkVendor($request);

        if ($response === null) {
            $this->order->update([
                'is_payed' => true,
            ]);

            $this->transaction->update([
                'status' => TransactionStatus::ACTIVE,
            ]);
        } else return $response;

        return $this->sendResponse(extra: [
            'VENDOR_TRANS_ID' => $this->order->id,
        ]);
    }

    public function notify(Request $request): JsonResponse
    {
        $this->getOrder($request, 'VENDOR_TRANS_ID');

        if (!$this->order) {
            return $this->sendResponse(Error::TRANSACTION_DOES_NOT_EXIST);
        }

        $checkOrder = $this->checkOrder();

        if ($checkOrder) return $checkOrder;

        $this->order->transaction->update([
            'extra->STATE' => $request->STATUS,
        ]);

        switch ($request->STATUS) {
            case PaymentStatus::PAYED->value:
//                $this->order->update([
//                    'is_payed' => true,
//                ]);
//
//                $this->order->transaction->update([
//                    'status' => TransactionStatus::ACTIVE,
//                ]);

                break;
            case PaymentStatus::CANCELLED->value:
                $this->order->transaction->update([
                    'status' => TransactionStatus::CANCELLED,
                ]);

                break;
        }

        return $this->sendResponse();
    }

    public function cancel(Request $request): JsonResponse
    {
        $this->getOrder($request, 'VENDOR_TRANS_ID');

        $checks = $this->checkOrder() ??
            $this->checkOrderCancelled();

        if (!$checks) {
            $this->order->transaction->update([
                'extra->STATE' => self::TRANSACTION_STATE_CANCELLED,
            ]);
        }

        return $checks ?? $this->sendResponse();
    }

    public function statement(Request $request): JsonResponse
    {
        $transactions = Transaction::method(PaymentMethod::INFINITYPAY)
            ->between($request->FROM, $request->TO, PaymentMethod::INFINITYPAY)
            ->get();

        return $this->sendResponse(extra: [
            'TRANSACTIONS' => InfinityPayTransactionResource::collection($transactions)
        ]);
    }

    public function fiscalization(Request $request): JsonResponse
    {
        $column = 'AGR_TRANS_ID';
        $id = $request->$column;
        $this->order = app(OrderModel::class)->whereHas('transaction', function ($query) use($column, $id) {
            return $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(extra, '$.$column')) = $id");
        })->first();

        if (!$this->order) {
            return $this->sendResponse(Error::TRANSACTION_DOES_NOT_EXIST);
        }

        $checkOrder = $this->checkOrder();

        if ($checkOrder) return $checkOrder;

        return $this->sendResponse(extra: [
            'PARAMETERS' => new InfinityPayFiscalizationResource($this->order)
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        return response()->json();
    }
}