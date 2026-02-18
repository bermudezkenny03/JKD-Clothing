<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'icon', 'route', 'is_active', 'sort_order', 'show_in_sidebar'];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_sidebar' => 'boolean'
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
