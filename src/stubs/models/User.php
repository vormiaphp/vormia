<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Todo:: method to get usermetas
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usermetas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Vrm\UserMeta::class, 'user', 'id');
    }

    /**
     * Todo:: method to save user Metadata
     * @param integer $userId
     * @param array $data
     * @return bool
     */
    public static function save_usermeta(int $userId, array $data): bool
    {
        /**
         * Loop through the data
         * key should be the meta_key
         * value should be the meta_value
         */
        foreach ($data as $key => $value) {
            if (!is_null($key)) {
                // Save
                \App\Models\Vrm\UserMeta::create([
                    'user' => $userId,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }

        return true;
    }

    /**
     * Todo:: method to update user Metadata
     * @param integer $userId
     * @param array $data
     * @return bool
     */
    public static function update_usermeta(int $userId, array $data): bool
    {
        /**
         * Loop through the data
         * key should be the meta_key
         * value should be the meta_value
         */
        foreach ($data as $key => $value) {

            \App\Models\Vrm\UserMeta::where('user', $userId)
                ->where('key', $key)
                ->update([
                    'value' => $value,
                ]);
        }

        return true;
    }

    /**
     * Todo:: method retrive usermeta
     * ? Pass the usermeta as a Collection
     * ? Loop and get the metakey, set is as parent array key
     * ? If specific key is being asked return that only
     *
     * @param any $usermeta
     * @param string|null $key
     *
     */
    public static function get_usermeta($usermeta, string $key = null)
    {
        // Check if is laravel Collection
        if (!is_a($usermeta, 'Illuminate\Database\Eloquent\Collection')) {
            return $usermeta;
        }

        // Loop
        $meta = [];
        foreach ($usermeta as $index => $this_meta) {
            $meta[$this_meta->meta_key] = $this_meta->meta_value;
        }

        // If key is not null
        if (!is_null($key) && !array_key_exists($key, $meta)) return null;

        // Return
        return is_null($key) ? (object) $meta : $meta[$key];
    }
}
