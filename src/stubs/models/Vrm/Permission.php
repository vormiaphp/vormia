<?php

namespace App\Models\Vrm;

use App\Traits\Vrm\Model\HasSlugs;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasSlugs;
    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'permissions';
    }

    // Todo: Fillable
    protected $fillable = [
        'name',
        'description',
    ];

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
