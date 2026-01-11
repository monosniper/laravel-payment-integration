<?php

namespace Monosniper\LaravelPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Monosniper\LaravelPayment\Base\OrderModel;
use Monosniper\LaravelPayment\Enums\PaymentMethod;
use Monosniper\LaravelPayment\Enums\TransactionStatus;

class Transaction extends Model
{
    protected $fillable = [
        'status',
        'order_id',
        'extra',

        // Payme
        'extra->create_time',
        'extra->cancel_time',
        'extra->perform_time',
        'extra->payment_transaction_id',
        'extra->reason',
        'extra->state',
        'extra->receivers',
        'extra->reason',
        'extra->account',

        // InfinityPay
        'extra->ENVIRONMENT',
        'extra->AGR_TRANS_ID',
        'extra->STATE',
        'extra->DATE',
    ];

    protected $casts = [
        'extra' => 'array',
        'status' => TransactionStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(get_class(app(OrderModel::class)));
    }

    public function scopeBetween($query, int $from, int $to, PaymentMethod $method) {
        $column = match($method) {
            PaymentMethod::PAYME => 'create_time',
            PaymentMethod::INFINITYPAY => 'DATE',
        };

        return $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(extra, '$.$column')) <= $to AND JSON_UNQUOTE(JSON_EXTRACT(extra, '$.$column')) >= $from");
    }

    public function scopeMethod($query, PaymentMethod $method)
    {
        return $query->whereHas('order', fn($query) => $query->wherePaymentMethod($method));
    }
}
