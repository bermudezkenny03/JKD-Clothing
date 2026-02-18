<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'parameter_type_id',
    ];

    public function parameterType()
    {
        return $this->belongsTo(ParameterType::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }
}
