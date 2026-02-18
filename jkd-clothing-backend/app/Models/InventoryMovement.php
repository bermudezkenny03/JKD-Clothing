<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_variant_id',
        'movement_type_id',
        'quantity',
        'reference_id',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function movementType()
    {
        return $this->belongsTo(Parameter::class, 'movement_type_id');
    }
}
