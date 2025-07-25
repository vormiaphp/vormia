<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;

class TaxonomyMeta extends Model
{
    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'taxonomy_meta';
    }

    protected $fillable = [
        'taxonomy_id',
        'key',
        'value',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }

    // Helper method for handling JSON values
    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        // Automatically decode JSON values if they look like JSON
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
                // If it fails to decode, return the original value
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
