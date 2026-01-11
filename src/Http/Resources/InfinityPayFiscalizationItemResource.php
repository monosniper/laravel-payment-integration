<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InfinityPayFiscalizationItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();

        return [
            'title' => $info['title'],
            'price' => $info['price'],
            'count' => $this->pivot->quantity,
            'code' => $info['ikpu'],
            'package_code' => $info['package_code'],
            'vat_percent' => config('payment.vat_percent'),
        ];
    }
}
