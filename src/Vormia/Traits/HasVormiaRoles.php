<?php

namespace Vormia\Vormia\Traits;

use Vormia\Vormia\Models\Role;

/**
 * Add this trait to your User model to enable Vormia roles, permissions, and meta.
 *
 * Also add to your User model:
 * - is_active in $fillable
 * - 'is_active' => 'boolean' in $casts
 */
trait HasVormiaRoles
{
    use Traits\Model\HasUserMeta;

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            config('vormia.table_prefix') . 'role_user',
        );
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasRoleId(int $roleId): bool
    {
        return $this->roles()->where('id', $roleId)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRoleId(1);
    }

    public function isAdminOrSuperAdmin(): bool
    {
        return $this->roles()->whereIn('id', [1, 2])->exists();
    }

    public function hasModule(string $module): bool
    {
        $roles = $this->roles()->pluck('module')->toArray();
        $modules = array_map(function ($mod) {
            return explode(',', $mod);
        }, $roles);
        $modules = array_merge(...$modules);

        return in_array($module, $modules);
    }

    public function permissions()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('name');
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
