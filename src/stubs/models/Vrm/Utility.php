<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;

class Utility extends Model
{
    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'utilities';
    }

    // Todo: Fillable
    protected $fillable = [
        'type',
        'key',
        'value',
    ];
}
