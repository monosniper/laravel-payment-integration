<?php

namespace Monosniper\LaravelPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    protected $table = 'productables';
    
    protected $fillable = [
        'productable_id',
        'productable_type',
    ];

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }
}