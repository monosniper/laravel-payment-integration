<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Monosniper\LaravelPayment\Base\OrderModel;
use Monosniper\LaravelPayment\Services\Payment\InfinityPay;

class InfinityPayFiscalizationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'type' => 0,
            'phone_number' => $this->phone,
            'items' => InfinityPayFiscalizationItemResource::collection($this->products)
        ];
    }
}
