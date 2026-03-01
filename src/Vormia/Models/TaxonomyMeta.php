<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;

class TaxonomyMeta extends Model
{
    public function getTable()
    {
        return config('vormia.table_prefix') . 'taxonomy_meta';
    }

    protected $fillable = [
        'taxonomy_id',
        'key',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }

    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        if (
            is_string($value) &&
            (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))
        ) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                //
            }
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($value === null) {
            $this->attributes['value'] = null;
        } elseif (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
