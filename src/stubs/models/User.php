<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Vrm\Model\HasUserMeta;
use App\Traits\Vrm\Model\HasSlugs;
// use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasSlugs, HasUserMeta; //, HasApiTokens;

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
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected $with = ['term'];

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /* -------------------------------------------------------------------------------- */

    // Todo: User meta
    public function meta()
    {
        return $this->hasMany(\App\Models\Vrm\UserMeta::class, 'user_id');
    }

    // Todo: Get meta value by key
    public function getMeta($key, $default = null)
    {
        return optional($this->meta->firstWhere('key', $key))->value ?? $default;
    }

    // Todo: Set or update meta
    public function setMeta($key, $value, $flag = 1)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'flag' => $flag]
        );
    }

    // Todo: Delete meta
    public function deleteMeta($key)
    {
        return $this->meta()->where('key', $key)->delete();
    }

    /* -------------------------------------------------------------------------------- */

    // Todo: Slug
    public function term()
    {
        return $this->hasOne(\App\Models\Vrm\SlugRegistry::class, 'entity_id', 'id');
    }

    /**
     * Define which field should be used for generating slugs.
     *
     * @return string
     */
    public function getSluggableField()
    {
        return 'name';
    }

    /**
     * Enable automatic slug updates for this model.
     *
     * @return bool
     */
    public function shouldAutoUpdateSlug()
    {
        // Development: Allow automatic updates
        if (app()->environment('local', 'development')) {
            return true;
        }

        // Production: Require manual approval
        return config('vormia.auto_update_slugs', false);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug' || $field === null) {
            return static::findBySlug($value);
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /* -------------------------------------------------------------------------------- */

    // Todo: User roles
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Vrm\Role::class);
    }

    // Todo: Check if the user has the required role
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    // Todo: Check if the user has the required role for the requested module
    public function hasModule(string $module): bool
    {
        $roles = $this->roles()->pluck('module')->toArray();
        $modules = array_map(function ($module) {
            return explode(',', $module);
        }, $roles);
        $modules = array_merge(...$modules);
        return in_array($module, $modules);
    }

    // Todo: User permissions
    public function permissions()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('name');
    }

    // Todo: Check if the user has the required permission
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
