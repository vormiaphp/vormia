<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory;

    // Todo: Table Name
    protected $table = 'user_tokens';

    protected $with = ['this_user'];

    protected $fillable = ['user', 'name', 'token'];

    // Todo: User
    public function this_user()
    {
        return $this->hasOne(User::class, 'id', 'user');
    }
}
