<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Model
use App\Models\Vrm\Term;
use App\Models\Vrm\HierarchyMeta;

class Hierarchy extends Model
{
    use HasFactory;


    protected $with = ['term'];

    protected $fillable = ['type', 'group', 'name', 'parent', 'flag'];

    /**
     * Todo: Table Name
     */
    protected $table = 'hierarchy';

    /**
     * Todo: Parennt
     *
     */
    public function parentInfo()
    {
        return $this->hasOne(Self::class, 'id', 'parent');
    }
    /**
     * Todo: Relate this model to the Term Model
     *
     * ? 1 Participant can have only 1 slug from Terms
     * ? To match the two look for terms.table = 'participants' and terms.related = participants.id
     * ? Return the terms.slug
     */
    public function term()
    {
        return $this->hasOne(Term::class, 'related')->where('table', 'hierarchy');
    }

    /**
     * Todo: Add hierarchy_attributes
     *
     * ? 1 This will related table hierarchy with table hierarchy_attributes where the hierarchy_attributes.hierarchy is the same
     */
    public function attributes()
    {
        return $this->hasMany(HierarchyMeta::class, 'hierarchy');
    }

    /**
     * Todo: Add hierarchy_attributes
     *
     * ? 1 This will related table hierarchy with table hierarchy_attributes where the hierarchy_attributes.hierarchy is the same
     */
    public function attributeinfo()
    {
        return $this->hasMany(HierarchyMeta::class, 'hierarchy');
    }

    /**
     * Todo: Get Icon
     */
    public function getIcon()
    {
        return $this->HierarchyMeta->where('name', 'icon')->first()->value ?? null;
    }
}
