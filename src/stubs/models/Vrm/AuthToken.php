<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{
    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'auth_tokens';
    }

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];


    // Todo: User relationship
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
