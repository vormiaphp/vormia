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
        'is_active',
    ];

    // Todo: The roles that belong to the permission.
    public function roles()
    {
        return $this->belongsToMany(Role::class, config('vormia.table_prefix') . 'permission_role', 'permission_id', 'role_id');
    }
}
