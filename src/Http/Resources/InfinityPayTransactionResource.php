<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfinityPayTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'ENVIRONMENT' => $this->extra['ENVIRONMENT'],
            'AGR_TRANS_ID' => $this->extra['AGR_TRANS_ID'],
            'VENDOR_TRANS_ID' => $this->id,
            'MERCHANT_TRANS_ID' => $this->order_id,
            'MERCHANT_TRANS_AMOUNT' => $this->order->amount,
            'STATE' => $this->extra['STATE'],
            'DATE' => $this->extra['DATE'],
        ];
    }
}
