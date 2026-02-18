<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterType extends Model
{
    protected $fillable = [
        'name',
        'table_reference',
    ];

    public function parameters()
    {
        return $this->hasMany(Parameter::class);
    }
}
