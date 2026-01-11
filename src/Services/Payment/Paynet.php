<?php

namespace Monosniper\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Monosniper\LaravelPayment\Base\OrderModel;
use Monosniper\LaravelPayment\Contracts\PaymentService;
use Monosniper\LaravelPayment\Enums\Payme\Error;
use Monosniper\LaravelPayment\Enums\PaymentMethod;
use Monosniper\LaravelPayment\Enums\TransactionStatus;
use Monosniper\LaravelPayment\Helpers\Timestamp;
use Monosniper\LaravelPayment\Http\Resources\PaymeFiscalisationResource;
use Monosniper\LaravelPayment\Http\Resources\PaymeTransactionResource;
use Monosniper\LaravelPayment\Models\Transaction;

class Paynet implements PaymentService
{
    public Request $request;
    private ?OrderModel $order;
    private ?Transaction $transaction;
    private string $merchant_id;

    const TRANSACTION_STATE_CREATED = 1;
    const TRANSACTION_STATE_FINISHED = 2;
    const TRANSACTION_STATE_CANCELLED = -1;
    const TRANSACTION_STATE_CANCELLED_AFTER_PERFORM = -2;

    const MIN_AMOUNT = 100;
    const MAX_AMOUNT = 999999999;

    const HOST = 'https://app.paynet.uz';

    public function __construct()
    {
        $this->merchant_id = config('payment.paynet.merchant_id');
    }

    public function generateUrl(OrderModel $order): string
    {
        $params = [
            'm' => $this->merchant_id,
            'c' => $order->getClientId(),
            'a' => $order->amount,
        ];

        return self::HOST . '?' . http_build_query($params);
    }

    public function getError(?Error $error): ?array
    {
        return $error ? [
            'code' => (int) $error->value,
            'message' => [
                'ru' => __('payment::payment.payme.'.$error->value, locale: 'ru'),
                'en' => __('payment::payment.payme.'.$error->value, locale: 'en'),
                'uz' => __('payment::payment.payme.'.$error->value, locale: 'uz'),
            ]
        ] : null;
    }

    public function sendResponse(?array $result = null, Error $error = null): JsonResponse
    {
        return response()->json([
            'result' => $result,
            'error' => $this->getError($error),
            'id' => $this->request->id,
        ]);
    }

    public function CheckPerformTransaction(): JsonResponse
    {
        return $this->sendResponse([
            'allow' => true,
            'detail' => [
                'receipt_type' => 0,
                'items' => PaymeFiscalisationResource::collection($this->order->products),
            ]
        ]);
    }

    public function CheckTransaction(): JsonResponse
    {
        if(!$this->transaction) {
            return $this->sendResponse(error: Error::INVALID_TRANSACTION);
        }

        return $this->sendResponse([
            'create_time' => $this->transaction->extra['create_time'] ?? 0,
            'perform_time' => $this->transaction->extra['perform_time'] ?? 0,
            'cancel_time' => $this->transaction->extra['cancel_time'] ?? 0,
            'transaction' => (string) $this->transaction->id,
            'state' => $this->transaction->extra['state'] ?? -1,
            'reason' => $this->transaction->extra['reason'] ?? null,
        ]);
    }

    public function CreateTransaction(array $data): JsonResponse
    {
        if(!$this->transaction) {
            if($this->order->transaction) {
                return $this->sendResponse(error: Error::ALREADY_HAS_TRANSACTION);
            }

            $this->transaction = $this->order->transaction()->create([
                'extra' => [
                    'create_time' => (new Timestamp())(),
                    'perform_time' => 0,
                    'cancel_time' => 0,
                    'receivers' => null,
                    'reason' => null,
                    'state' => 1,
                    'account' => $data['account'],
                    'payment_transaction_id' => $data['id'],
                ]
            ]);
        }

        return $this->sendResponse([
            'create_time' => $this->transaction->extra['create_time'],
            'transaction' => (string) $this->transaction->id,
            'state' => self::TRANSACTION_STATE_CREATED,
        ]);
    }

    public function PerformTransaction(): JsonResponse
    {
        if($this->transaction->status !== TransactionStatus::ACTIVE) {
            $this->transaction->update([
                'status' => TransactionStatus::ACTIVE,
                'extra->perform_time' => (new Timestamp())(),
                'extra->state' => self::TRANSACTION_STATE_FINISHED,
            ]);
            $this->transaction->order->update([
                'is_payed' => true
            ]);
        }

        return $this->sendResponse([
            'transaction' => (string) $this->transaction->id,
            'perform_time' => $this->transaction->extra['perform_time'],
            'state' => self::getTransactionState($this->transaction),
        ]);
    }

    public function CancelTransaction(array $data): JsonResponse
    {
        if($this->transaction->status !== TransactionStatus::CANCELLED) {
            $this->transaction->update([
                'status' => TransactionStatus::CANCELLED,
                'extra->cancel_time' => (new Timestamp())(),
                'extra->reason' => $data['reason'],
                'extra->state' => ($this->transaction->extra['state'] ?? -1) === self::TRANSACTION_STATE_FINISHED
                    ? self::TRANSACTION_STATE_CANCELLED_AFTER_PERFORM
                    : self::TRANSACTION_STATE_CANCELLED,
            ]);
        }

        return $this->sendResponse([
            'transaction' => (string) $this->transaction->id,
            'cancel_time' => $this->transaction->extra['cancel_time'],
            'state' => $this->transaction->extra['state'] ?? -1,
        ]);
    }

    public function ChangePassword(): JsonResponse
    {
        return $this->sendResponse(['success' => true]);
    }

    public function GetStatement(array $data): JsonResponse
    {
        $transactions = Transaction::method(PaymentMethod::PAYME)
            ->between($data['from'], $data['to'], PaymentMethod::PAYME)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse([
            'transactions' => PaymeTransactionResource::collection($transactions),
        ]);
    }

    public function getTransactionState(Transaction $transaction): int
    {
        return match ($transaction->status) {
            TransactionStatus::INACTIVE => self::TRANSACTION_STATE_CREATED,
            TransactionStatus::ACTIVE => self::TRANSACTION_STATE_FINISHED,
            TransactionStatus::CANCELLED => self::TRANSACTION_STATE_CANCELLED,
        };
    }

    public function getTransaction(string $payme_transaction_id): ?Transaction
    {
        return Transaction::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(extra, '$.payment_transaction_id')) = '$payme_transaction_id'")->first();
    }

    public function validateAuth(Request $request): bool
    {
        $authorizationHeader = $request->header('Authorization');
        if(!$authorizationHeader) return false;

        $key_ = explode(' ', $authorizationHeader);

        if(isset($key_[1])) {
            $key = $key_[1];

            if($key === base64_encode($this->login . ":" . $this->key)) {
                return true;
            }
        }

        return false;
    }

    public function validateParams(array $data): ?Error
    {
        if(isset($data['id'])) {
            $this->transaction = self::getTransaction($data['id']);
        }

        if(isset($data['account'][config('payment.payme.parameter')])) {
            $this->order = app(OrderModel::class)::find($data['account'][config('payment.payme.parameter')]);

            if(!$this->order) {
                return Error::INVALID_ORDER_ID;
            }
            if($this->order->amount !== $data['amount'] / 100) {
                return Error::INVALID_AMOUNT;
            }
        }

        if(isset($data['amount']) && ($data['amount'] / 100 < self::MIN_AMOUNT || $data['amount'] / 100 > self::MAX_AMOUNT)) {
            return Error::INVALID_AMOUNT;
        }

        return null;
    }

    public function callback(Request $request): JsonResponse
    {
        $this->request = $request;

        $auth_valid = self::validateAuth($request);
        $params_valid = self::validateParams($request->params);

        if($params_valid !== null) return $this->sendResponse(
            error: $params_valid
        );

        if (!$auth_valid) {
            return $this->sendResponse(
                error: Error::AUTH
            );
        }

        return self::{$request->get('method')}($request->get('params'));
    }
}
