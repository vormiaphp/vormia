<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;

class Utility extends Model
{
    public function getTable()
    {
        return config('vormia.table_prefix') . 'utilities';
    }

    protected $fillable = [
        'type',
        'key',
        'value',
        'is_public',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];
}
