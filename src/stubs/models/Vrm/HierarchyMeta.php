<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HierarchyMeta extends Model
{
    use HasFactory;

    protected $fillable = ['hierarchy', 'key', 'value'];

    /**
     * Todo: Table Name
     */
    protected $table = 'hierarchy_meta';

    /**
     * Todo: Add hierarchy
     *
     * ? 1 This will related with table hierarchy
     */
    public function hierarchy()
    {
        return $this->belongsTo(Hierarchy::class, 'id', 'hierarchy');
    }
}
