<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    // Todo: Table name
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

    // Todo: User relation
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Todo: Mutator: Store any type of value as JSON
    public function setValueAttribute($value)
    {
        if ($value === null) {
            $this->attributes['value'] = null;
        } else {
            $this->attributes['value'] = json_encode($value);
        }
    }

    // Todo: Accessor: Decode JSON, return original value if not JSON
    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }
}
