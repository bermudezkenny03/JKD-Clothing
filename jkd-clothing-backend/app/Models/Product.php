<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'category_id',
        'brand_id',
        'status_id',
        'is_featured',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function status()
    {
        return $this->belongsTo(Parameter::class, 'status_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', '%' . $term . '%');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }
}
