<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;
use Vormia\Vormia\Traits\Model\HasSlugs;

class Permission extends Model
{
    use HasSlugs;

    public function getTable()
    {
        return config('vormia.table_prefix') . 'permissions';
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
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
