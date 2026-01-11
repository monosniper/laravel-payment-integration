<?php

namespace Monosniper\LaravelPayment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UzumItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $product = $this->productable;
        $info = $product->getFiscalizationInfo();
        $title = $info['title'];

        if (strlen($title) > 63) {
            $title = substr($title, 0, 60) . '...';
        }

        return [
            'title' => $title,
            'productId' => (string) $info['id'],
            'quantity' => $this->pivot->quantity,
            'unitPrice' => $info['price'] * 100,
            'total' => $info['price'] * 100 * $this->pivot->quantity,
            'receiptParams' => [
                'spic' => $info['ikpu'],
                'packageCode' => $info['package_code'],
                'vatPercent' => config('payment.vat_percent'),
                'TIN' => config('payment.inn'),
            ]
        ];
    }
}
