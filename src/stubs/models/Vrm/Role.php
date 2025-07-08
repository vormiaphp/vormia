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
        'is_active',
    ];

    // Todo: The users that belong to the role.
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'role_user');
    }

    // Todo: The permissions that belong to the role.
    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Vrm\Permission::class, config('vormia.table_prefix') . 'permission_role', 'role_id', 'permission_id');
    }
}
