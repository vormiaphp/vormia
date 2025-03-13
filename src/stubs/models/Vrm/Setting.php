<?php

namespace App\Models\Vrm;

use App\Models\Auto;
use Illuminate\Support\Facades\Auth;

//Models
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    // Donot show this column
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Todo: Load all Global Active Settings
     *
     * * This method is used to load all data requred to be present for the system/website to operate well
     * ? E.g Site Title, Active Themes, Meta Data e.t.c
     * ? All values are return as one array (data)
     */
    public static function globalSetting($allow_autoload = true)
    {
        // Get all settings where flag is 1 use eloquent
        $global_setting = array_reduce(Setting::whereType('global')->whereFlag(1)->get(['title', 'value'])->toArray(), function ($result, $item) {
            $result[$item['title']] = $item['value'];
            return $result;
        }, array());

        // Update Themes Dir
        $global_setting['theme_dir'] = 'content/themes/' . $global_setting['theme_name'];
        $global_setting['theme_assets'] = 'content/themes/' . $global_setting['theme_name'];

        $global_setting['theme_dir_child'] = 'content/themes/' . $global_setting['theme_child'];
        $global_setting['theme_assets_child'] = 'content/themes/' . $global_setting['theme_child'];

        $global_setting['plugin_assets'] = 'content/plugins';

        // Other Global Settings
        $global_setting['userinfo'] = Setting::user_info();
        $global_setting['isLogged'] = (Auth::check()) ? true : false;

        // Load Auto Model
        $global_auto = [];
        if ($allow_autoload) {
            $this_auto = new Auto;
            $global_auto = $this_auto->loadData();
        }

        // Merge
        $setting = array_merge($global_setting, $global_auto);

        // Return the setting
        return $setting;
    }

    /**
     * Todo: This method is used to pre load all required procedure when opening a page
     *
     * @param string or integer $term default is null
     * @param array $passed default is empty array
     */
    public static function preLoad($term = null, $passed = [])
    {
        $page_name = $term;
        // Check $term if is not null
        if (!is_null($term)) {
            $find_term = (is_numeric($term)) ? ['id' => $term] : ['slug' => $term];
            // Check If is Array
            (is_array($term)) ? $find_term = $term : $page_name = $term;

            // Load Url Model
            $db_url = Term::where($find_term)->select('slug', 'table', 'related as id')->first();
            if (!is_null($db_url)) {
                $db_url = $db_url->toArray();
                // Get Page Name from -> contents
                if ($db_url['table'] == 'contents') {
                    $page_name = ''; // Content::where($db_url['table'], $db_url['id'])->value('page_name');
                }
            }
        }

        // Load all global settings
        $global_setting = self::globalSetting();

        // Page
        $global_setting['page_name'] = $page_name ?? '';

        // Merge all settings into one array
        $settings = array_merge($global_setting, $passed);

        // Return all settings
        return $settings;
    }

    /**
     * Todo: This method is used to pre load all required procedure when opening a page
     *
     * @param string or integer $term default is null
     * @param array $passed default is empty array
     */
    public static function adminLoad($term = null, $passed = [])
    {
        $page_name = $term;
        // Check $term if is not null
        if (!is_null($term)) {
            $find_term = (is_numeric($term)) ? ['id' => $term] : ['slug' => $term];
            // Check If is Array
            (is_array($term)) ? $find_term = $term : $page_name = $term;

            // Load Url Model
            $db_url = Term::where($find_term)->select('slug', 'table', 'related as id')->first();
            if (!is_null($db_url)) {
                $db_url = $db_url->toArray();
                // Get Page Name from -> contents
                if ($db_url['table'] == 'contents') {
                    $page_name = ''; // Content::where($db_url['table'], $db_url['id'])->value('page_name');
                }
            }
        }

        // Load all global settings
        $global_setting = self::globalSetting(true); //Don't allow Auto Load

        // Page
        $global_setting['page_name'] = $page_name ?? '';
        $global_setting['theme_dir'] = 'admin';
        $global_setting['theme_assets'] = 'admin';

        // Merge all settings into one array
        $settings = array_merge($global_setting, $passed);

        // Return all settings
        return $settings;
    }

    /**
     *
     * This function is used to load user info
     * All values are return as one array (data)
     *
     * @param string $user_id
     */
    public static function user_info($user_id = null)
    {
        // ? Check the authenticated user
        if (Auth::user() && is_null($user_id)) {
            $user_id = Auth::user()->id;
        }

        // Default
        $found = ['name' => 'Admin Account', 'email' => '', 'profile' => 'admin/images/users/avatar-2.jpg'];
        if (!is_numeric($user_id)) {
            return (object) $found;
        }

        $user_select = \App\Models\User::whereId($user_id)->select('name', 'email')->first()->toArray();
        $found = (object) array_merge($found, $user_select);

        // Return
        return $found;
    }
}
