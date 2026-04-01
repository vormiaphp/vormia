<?php

/**
 * Copy this file to app/Models/User.php (replace your existing User model).
 *
 * This model is wired for Vormia only — no duplicate meta/role methods here:
 *
 * - HasVormiaRoles — roles, permissions, and helpers (hasRole, hasPermission, …).
 *   It also composes HasUserMeta — user meta (meta, getMeta, setMeta, deleteMeta, …).
 * - HasSlugs — slugs live in vormia slug_registry (getSlug, findBySlug, …).
 *
 * After publishing config/migrations, run migrations and ensure config/vormia.php
 * points at this class if you use vormia.user_model.
 *
 * Optional: if you do not use Laravel Fortify or Sanctum, remove those traits
 * and the related use statements.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Vormia\Vormia\Traits\HasVormiaRoles;
use Vormia\Vormia\Traits\Model\HasSlugs;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens, HasVormiaRoles, HasSlugs, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'is_active',
        'provider',
        'provider_id',
        'avatar',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
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
     * Two-letter initials from the user's name (UI helpers).
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Expose the registry-backed slug as a virtual attribute so route() and
     * array/json use of "slug" match resolveRouteBinding() / findBySlug().
     */
    protected function slug(): Attribute
    {
        return Attribute::get(fn () => $this->getSlug());
    }

    public function getSluggableField(): string
    {
        return 'name';
    }

    public function shouldAutoUpdateSlug(): bool
    {
        if (app()->environment('local', 'development')) {
            return false;
        }

        return (bool) config('vormia.auto_update_slugs', false);
    }

    public function getRouteKeyName(): string
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

    /**
     * Convenience: true if the user has the "member" role (by slug on vormia roles).
     */
    public function isMember(): bool
    {
        return $this->roles()->where('slug', 'member')->exists();
    }
}
