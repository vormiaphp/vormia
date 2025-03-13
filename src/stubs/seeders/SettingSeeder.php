<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vrm\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            "type" => "global",
            "title" => "site_name",
            "value" => "Vormia",
            "flag" => 1,
        ]);

        Setting::create([
            "type" => "global",
            "title" => "assets",
            "value" => "admin",
            "flag" => 1,
        ]);

        Setting::create([
            "type" => "global",
            "title" => "theme_name",
            "value" => "starter",
            "flag" => 1,
        ]);

        Setting::create([
            "type" => "global",
            "title" => "theme_child",
            "value" => "",
            "flag" => 1,
        ]);

        Setting::create([
            "type" => "hierarchy",
            "title" => "hierarchy",
            "value" => "default,category,tag,gender,country,currency",
            "flag" => 1,
        ]);
    }
}
