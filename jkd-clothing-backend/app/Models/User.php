<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'status',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::deleting(function ($user) {
            $user->userDetail?->delete();
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public static function createUser($validated)
    {
        $user = self::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'role_id' => $validated['role_id'],
        ]);

        return $user;
    }

    public function updateUser($validated)
    {
        $this->update(array_filter($validated, fn($key) => in_array($key, $this->getFillable()), ARRAY_FILTER_USE_KEY));
    }

    public function scopeWhereNotAssigned($query, $assignedUserIds)
    {
        return $query->whereNotIn('id', $assignedUserIds);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function hasModule($moduleSlug)
    {
        return $this->role->hasModule($moduleSlug) ?? false;
    }

    public function isStaff(): bool
    {
        if (! $this->status || ! $this->role) {
            return false;
        }

        return in_array($this->role->name, [
            'Super Admin',
            'Admin',
        ]);
    }

    public function getModules()
    {
        $modules = collect($this->role?->getModules() ?? []);

        if ($this->isStaff() && ! $modules->contains('dashboard')) {
            $modules->prepend('dashboard');
        }

        return $modules->values()->toArray();
    }

    public function getModulesWithInfo()
    {
        $modules = collect($this->role?->getModulesWithInfo() ?? []);
        if ($this->isStaff()) {

        $dashboard = Module::where('slug', 'dashboard')
            ->where('is_active', true)
            ->first();

        if ($dashboard && ! $modules->contains(fn($m) => $m['slug'] === 'dashboard')) {
            $modules->prepend([
                'slug' => $dashboard->slug,
                'name' => $dashboard->name,
                'icon' => $dashboard->icon,
                'route' => $dashboard->route,
                'sort_order' => 1,
                'children' => [],
            ]);
        }
    }

    return $modules->sortBy('sort_order')->values()->toArray();
}
}
