<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function modulePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    public function hasModule($moduleSlug)
    {
        return $this->modulePermissions()
            ->whereHas('module', function ($query) use ($moduleSlug) {
                $query->where('slug', $moduleSlug)->where('is_active', true);
            })
            ->exists();
    }

    public function getModules()
    {
        return $this->modulePermissions()
            ->with('module')
            ->whereHas('module', function ($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->pluck('module.slug')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getModulesWithInfo()
    {
        $userModulePermissions = $this->getUserModulePermissions();
        $parentModules = $this->getActiveParentModules();

        return $parentModules
            ->map(fn($parent) => $this->buildParentModuleData($parent, $userModulePermissions))
            ->filter() // Remover nulls
            ->sortBy('sort_order')
            ->values()
            ->toArray();
    }

    private function getUserModulePermissions()
    {
        return $this->modulePermissions()
            ->with(['module', 'permission'])
            ->whereHas(
                'module',
                fn($query) =>
                $query->where('is_active', true)->whereNotNull('parent_id')
            )
            ->get()
            ->groupBy('module.slug');
    }

    private function getActiveParentModules()
    {
        return Module::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    private function buildParentModuleData($parentModule, $userModulePermissions)
    {
        $visibleChildren = $this->getVisibleChildren($parentModule, $userModulePermissions);

        if ($visibleChildren->isEmpty()) {
            return null; // No mostrar padre si no tiene hijos visibles
        }

        return [
            'slug' => $parentModule->slug,
            'name' => $parentModule->name,
            'icon' => $parentModule->icon,
            'route' => $parentModule->route,
            'permissions' => [],
            'sort_order' => $parentModule->sort_order,
            'children' => $visibleChildren->sortBy('sort_order')->values()->toArray()
        ];
    }

    private function getVisibleChildren($parentModule, $userModulePermissions)
    {
        return $parentModule->children
            ->filter(
                fn($child) =>
                // $child->show_in_sidebar &&
                $userModulePermissions->has($child->slug)
            )
            ->map(fn($child) => [
                'slug' => $child->slug,
                'name' => $child->name,
                'icon' => $child->icon,
                'route' => $child->route,
                'permissions' => $userModulePermissions[$child->slug]->pluck('permission.slug')->toArray(),
                'sort_order' => $child->sort_order,
                'show_in_sidebar' => $child->show_in_sidebar,
            ]);
    }
}
