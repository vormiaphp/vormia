<?php

namespace App\Models\Vrm;

use App\Traits\Vrm\Model\HasSlugs;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasSlugs;
    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'roles';
    }

    // Todo: Fillable
    protected $fillable = [
        'name',
        'slug',
        'module',
        'authority',
        'description',
    ];

    // Todo: The users that belong to the role.
    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            config("vormia.table_prefix") . "role_user",
        );
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
}
