<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymeTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->extra['payment_transaction_id'],
            'time' => $this->created_at->timestamp,
            'amount' => $this->amount * 100,
            'account' => $this->extra['account'],
            'create_time' => $this->extra['create_time'],
            'perform_time' => $this->extra['perform_time'],
            'cancel_time' => $this->extra['cancel_time'],
            'transaction' => $this->id,
            'state' => $this->extra['state'],
            'reason' => $this->extra['reason'],
            'receivers' => $this->extra['receivers'],
        ];
    }
}
