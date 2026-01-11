<?php

namespace Monosniper\LaravelPayment\Contracts;

use Monosniper\LaravelPayment\Models\Product;

interface Productable
{
    public function getFiscalizationInfo(): array;
}