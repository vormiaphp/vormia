<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;
use Vormia\Vormia\Traits\Model\HasSlugs;

class Role extends Model
{
    use HasSlugs;

    public function getTable()
    {
        return config('vormia.table_prefix') . 'roles';
    }

    protected $fillable = [
        'name',
        'slug',
        'module',
        'authority',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(
            config('vormia.user_model') ?? config('auth.providers.users.model'),
            config('vormia.table_prefix') . 'role_user',
        );
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('vormia.table_prefix') . 'permission_role',
        );
    }

    public function getSluggableField(): string
    {
        return 'name';
    }

    public function shouldAutoUpdateSlug(): bool
    {
        if (app()->environment('local', 'development')) {
            return true;
        }

        return config('vormia.auto_update_slugs', false);
    }
}
