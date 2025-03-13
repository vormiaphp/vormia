<?php

namespace App\Models\Vrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Term extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'table',
        'type',
        'related',
        'slug',
    ];

    /**
     * Todo: When selecting a term, also select the related model
     */
    public function hierarchy()
    {
        return $this->belongsTo(Hierarchy::class, 'related');
    }

    /**
     * Todo: Check Slug
     * ? Check if slug exists
     * ? Return false or term id if exits
     * @param string $slug
     *
     */
    public static function checkSlug(string $slug)
    {
        $term = Term::where('slug', $slug)->first();
        if ($term) {
            return $term->id;
        }
        return false;
    }

    /**
     * Todo: Check Slug
     * ? Check if slug exists
     * ? Return false or term id if exits
     * @param string $slug
     *
     */
    public static function checkUsername(string $slug)
    {
        $username = User::where('username', $slug)->first();
        if ($username) {
            return $username->id;
        }
        return false;
    }

    /**
     * Todo: Generate Slug
     * ? Pass string to slugify limit to 200 characters
     * ? If turn is true then append number to slug
     * ? Check if slug exists
     * ? If exists, append number to slug
     * ? Return slug
     *
     * @param string $string
     * @param int $id default null - article ID
     * @param bool $turn
     *
     * @return string
     */
    public static function slug(string $string, int $id = null, $turn = false): string
    {
        $string = Str::limit($string, 200); // Length

        // Turn
        if ($turn) {
            // Generate random string from character - abcdefghijklmnpqrstuvwxyz123456789
            $string = (!is_null($id) || $id != 0) ? $string . '-' . $id : $string . '-' . Str::random(5);
        }

        // Generate Slug
        $slug = Str::slug($string);
        $slug_status = self::checkSlug($slug);
        if ($slug_status) {
            $slug = self::slug($slug, $id, true);
        }

        // Return
        return $slug;
    }

    /**
     * Todo: Generate Slug & Username
     * ? Pass string to slugify limit to 200 characters
     * ? If turn is true then append number to slug
     * ? Check if slug exists
     * ? If exists, append number to slug
     * ? Return slug
     *
     * @param string $string
     * @param int $id default null - article ID
     * @param bool $turn
     *
     * @return string
     */
    public static function username(string $string, int $id = null, $turn = false): string
    {
        $string = Str::limit($string, 200); // Length

        // Turn
        if ($turn) {
            // Generate random string from character - abcdefghijklmnpqrstuvwxyz123456789
            $string = (!is_null($id) || $id != 0) ? $string . '-' . $id : $string . '-' . Str::random(5);
        }

        // Generate Slug
        $slug = Str::slug($string);
        $slug_username_status = self::checkUsername($slug);
        if ($slug_username_status) {
            $slug = self::username($slug, $id, true);
        }

        $slug_status = self::checkSlug($slug);
        if ($slug_status) {
            $slug = self::slug($slug, $id, true);
        }

        // Return
        return $slug;
    }

    /**
     * Todo: Get Slug
     * ? 1: Pass array of search terms
     *
     * @param array $search
     */
    public static function getSlug(array $search)
    {
        $query = Term::query();

        foreach ($search as $column => $value) {
            if ($value) {
                $query->where($column, $value);
            }
        }

        // Return column slug
        return ($query->first()) ? $query->first()->slug : '';
    }
}
