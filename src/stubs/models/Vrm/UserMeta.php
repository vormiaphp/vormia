<?php

namespace App\Models\Vrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserMeta extends Model
{
    use HasFactory;

    // Todo: Table Name
    protected $table = 'user_meta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user',
        'key',
        'value',
        'flag',
    ];

    /**
     * Todo: Users
     * ? One or more meta values can be related to a single user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user');
    }
}
