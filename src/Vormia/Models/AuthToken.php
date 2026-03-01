<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;

class AuthToken extends Model
{
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

    public function user()
    {
        return $this->belongsTo(config('vormia.user_model') ?? config('auth.providers.users.model'));
    }
}
