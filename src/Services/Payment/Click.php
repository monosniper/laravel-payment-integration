<?php

namespace Monosniper\LaravelPayment\Services\Payment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Monosniper\LaravelPayment\Base\OrderModel;
use Monosniper\LaravelPayment\Contracts\PaymentService;
use Monosniper\LaravelPayment\Enums\Click\Error;
use Monosniper\LaravelPayment\Enums\TransactionStatus;
use Monosniper\LaravelPayment\Models\Transaction;

class Click implements PaymentService
{
    public Request $request;
    private bool $with_split;
    private string $secret_key;
    private string $service_id;
    private string $merchant_id;
    private int $prepare_action;
    private int $complete_action;
    private OrderModel $order;

    const MIN_AMOUNT = 100;
    const MAX_AMOUNT = 100000000;

    const HOST = 'https://my.click.uz/services/pay';

    public function __construct()
    {
        $this->with_split = config('payment.click.with_split', false);
        $this->secret_key = config('payment.click.secret_key');
        $this->service_id = config('payment.click.service_id');
        $this->merchant_id = config('payment.click.merchant_id');
        $this->prepare_action = $this->with_split ? 1 : 0;
        $this->complete_action = $this->with_split ? 2 : 1;
    }

    private function setConfig(): void
    {
        $config = $this->order?->getPaymentConfig() ?? config('payment.click');
        
        $this->with_split = $config['with_split'] ?? false;
        $this->secret_key = $config['secret_key'];
        $this->service_id = $config['service_id'];
        $this->merchant_id = $config['merchant_id'];
    }

    public function generateUrl(OrderModel $order): string
    {
        $this->order = $order;
        $this->setConfig();

        $params = [
            'service_id' => $this->service_id,
            'merchant_id' => $this->merchant_id,
            'merchant_user_id' => $order->user_id,
            'amount' => $order->amount,
            'transaction_param' => $order->getTransactionParam(),
        ];

        return self::HOST . '?' . http_build_query($params);
    }

    private function validateSignature($params): bool
    {
        $data = $this->with_split ? [
            $params['click_paydoc_id'],
            $params['attempt_trans_id'],
            $params['service_id'],
            $this->secret_key,
            implode('', array_values($params['params'])),
        ] : [
            $params['click_trans_id'],
            $params['service_id'],
            $this->secret_key,
            $params['merchant_trans_id'],
        ];

        if(!$this->with_split) {
            if((int) $params['action'] === $this->complete_action) {
                $data[] = $params['merchant_prepare_id'];
            }

            $data[] = $params['amount'];
        }

        $data[] = $params['action'];
        $data[] = $params['sign_time'];

        $sign = md5(implode('', $data));

        return $sign === $params['sign_string'];
    }

    private function validateParams($action, $data): ?JsonResponse
    {
        if($action === null) {
            return $this->makeErrorResponse(Error::INVALID_ACTION);
        }

        if(!$this->validateSignature($data)) {
            return $this->makeErrorResponse(Error::INVALID_SIGN);
        }

        if($this->with_split) {
            if($data['params']['amount'] < self::MIN_AMOUNT || $data['params']['amount'] > self::MAX_AMOUNT) {
                return $this->makeErrorResponse(Error::INVALID_AMOUNT);
            }

            if(!app(OrderModel::class)::find($data['params']['transaction_param'])) {
                return $this->makeErrorResponse(Error::INVALID_USER);
            }
        } else {
            if((int) $data['amount'] < self::MIN_AMOUNT || (int) $data['amount'] > self::MAX_AMOUNT) {
                return $this->makeErrorResponse(Error::INVALID_AMOUNT);
            }

            if(!app(OrderModel::class)::find($data['merchant_trans_id'])) {
                return $this->makeErrorResponse(Error::INVALID_USER);
            }
        }


        if(isset($data['merchant_prepare_id'])) {
            $transaction = Transaction::find($data['merchant_prepare_id']);
        } else $transaction = null;

        if($action === 'prepare') {
            if($transaction) {
                if($transaction->status === TransactionStatus::CANCELLED) {
                    return $this->makeErrorResponse(Error::TRANSACTION_CANCELLED);
                }

                return $this->makeErrorResponse(Error::ALREADY_PAID);
            }
        } else if (!$transaction) {
            return $this->makeErrorResponse(Error::INVALID_TRANSACTION);
        } else if ($transaction->order->amount !== (int) ($this->with_split ? $data['params']['amount'] : $data['amount'])) {
            return $this->makeErrorResponse(Error::INVALID_AMOUNT);
        }

        if($this->with_split) {
            if($data['error'] !== 0) {
                return $this->makeErrorResponse(Error::INVALID_REQUEST);
            }
        }

        return null;
    }

    public function prepare(array $data): JsonResponse
    {
        $transaction = Transaction::create($this->with_split ? [
            'order_id' => $data['params']['transaction_param'],
            'extra' => [
                'click_paydoc_id' => $data['click_paydoc_id'],
                'attempt_trans_id' => $data['attempt_trans_id'],
                'service_id' => $data['service_id'],
                'sign_time' => $data['sign_time'],
            ],
        ] : [
            'order_id' => $data['merchant_trans_id'],
            'extra' => [
                'click_trans_id' => $data['click_trans_id'],
                'click_paydoc_id' => $data['click_paydoc_id'],
                'service_id' => $data['service_id'],
                'sign_time' => $data['sign_time'],
            ],
        ]);

        return $this->makeResponse($transaction, $data['action']);
    }

    public function complete(array $data): JsonResponse
    {
        $transaction = Transaction::find($data['merchant_prepare_id']);
        $transaction->update([
            'status' => TransactionStatus::ACTIVE
        ]);
        $transaction->order->update([
            'is_payed' => true
        ]);

        return $this->makeResponse($transaction, $data['action']);
    }

    public function makeResponse(
        Transaction $transaction,
        int         $action,
    ): JsonResponse
    {
        $type = $this->with_split
            ? ($action === 1 ? 'prepare' : 'complete')
            : ($action === 1 ? 'complete' : 'prepare');

        $transaction->update([
            'extra' => $transaction->extra + [
                'attempt_trans_id' => time(),
            ]
        ]);

        $response = $this->with_split ? [
            'click_paydoc_id' => $transaction->extra['click_paydoc_id'],
            'attempt_trans_id ' => $transaction->extra['attempt_trans_id'],
            'params ' => [],
        ] : [
            'click_trans_id' => $transaction->extra['click_trans_id'],
            'merchant_trans_id' => $transaction->order_id,
        ];

        $response["merchant_{$type}_id"] = $transaction->id;

        try {
            $transaction->order->onSuccessfulPay();
        } catch (\Exception $exception) {
            info("Failed onSuccessfulPay hook (Click, Order ID: $transaction->order_id): $exception");
        }

        return response()->json($response + $this->makeError(Error::SUCCESS));
    }

    public function makeErrorResponse(
        Error $error
    ): JsonResponse
    {
        return response()->json($this->makeError($error));
    }

    static public function makeError(
        Error $error
    ): array
    {
        return [
            'error' => $error->value,
            'error_note' => __('payment::payment.click.' . $error->value),
        ];
    }

    public function getAction($action): ?string
    {
        return match((int) $action) {
            $this->prepare_action => 'prepare',
            $this->complete_action => 'complete',
            default => null,
        };
    }

    public function callback(Request $request): JsonResponse
    {
        $transactionId = data_get($request->all(), 'params.transaction_param');

        if ($transactionId) {
            $this->order = app(OrderModel::class)::find($transactionId);
        }

        $this->request = $request;

        $action = $this->getAction($request->action);
        $errors = $this->validateParams($action, $request->all());

        if($errors !== null) return $errors;

        return $this->$action($request->all());
    }
}
