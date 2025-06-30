<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
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
}
