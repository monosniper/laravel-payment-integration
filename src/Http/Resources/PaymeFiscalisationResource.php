<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymeFiscalisationResource extends JsonResource
{
    public function toArray($request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();

        return [
            'title' => $info['title'],
            'price' => $info['price'] * 100,
            'count' => $this->pivot->quantity,
            'code' => $info['ikpu'],
            'vat_percent' => config('payment.vat_percent'),
            'package_code' => $info['package_code'],
        ];
    }
}
