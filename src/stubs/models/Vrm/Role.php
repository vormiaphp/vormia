<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
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
        return $this->belongsToMany(\App\Models\User::class, 'role_user');
    }
}
