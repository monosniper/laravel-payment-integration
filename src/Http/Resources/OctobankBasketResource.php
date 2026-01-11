<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OctobankBasketResource extends JsonResource
{
    public function toArray($request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();

        return [
            'position_desc' => $info['title'],
            'count' => $this->pivot->quantity,
            'price' => $info['price'],
            'spic' => $info['ikpu'],
        ];
    }
}
