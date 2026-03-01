<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    public function getTable()
    {
        return config('vormia.table_prefix') . 'user_meta';
    }

    protected $fillable = [
        'user_id',
        'key',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(config('vormia.user_model') ?? config('auth.providers.users.model'));
    }

    public function setValueAttribute($value)
    {
        if ($value === null) {
            $this->attributes['value'] = null;
        } else {
            $this->attributes['value'] = json_encode($value);
        }
    }

    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }
}
