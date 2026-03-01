<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Vrm\Model\HasSlugs;
use App\Traits\Vrm\Model\HasUserMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens, HasSlugs, HasUserMeta, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /* -------------------------------------------------------------------------------- */
    // User Meta
    /* -------------------------------------------------------------------------------- */

    public function meta()
    {
        return $this->hasMany(\App\Models\Vrm\UserMeta::class, 'user_id');
    }

    public function getMeta($key, $default = null)
    {
        return optional($this->meta->firstWhere('key', $key))->value ?? $default;
    }

    public function setMeta($key, $value, $is_active = 1)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'is_active' => $is_active]
        );
    }

    public function deleteMeta($key)
    {
        return $this->meta()->where('key', $key)->delete();
    }

    /* -------------------------------------------------------------------------------- */
    // Slug Methods
    /* -------------------------------------------------------------------------------- */

    public function getSluggableField()
    {
        return 'name';
    }

    public function shouldAutoUpdateSlug()
    {
        if (app()->environment('local', 'development')) {
            return false;
        }

        return config('vormia.auto_update_slugs', false);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug' || $field === null) {
            return static::findBySlug($value);
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /* -------------------------------------------------------------------------------- */
    // Roles
    /* -------------------------------------------------------------------------------- */

    public function roles()
    {
        return $this->belongsToMany(
            \App\Models\Vrm\Role::class,
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
        $modules = array_map(function ($module) {
            return explode(',', $module);
        }, $roles);
        $modules = array_merge(...$modules);

        return in_array($module, $modules);
    }

    /* -------------------------------------------------------------------------------- */
    // Permissions
    /* -------------------------------------------------------------------------------- */

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
