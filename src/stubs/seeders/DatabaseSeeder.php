<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Add SettingSeeder
        $this->call(SettingSeeder::class);

        $admin = User::with(['roles'])->create([
            'username' => 'admin',
            'name' => 'John Doe',
            'email' => 'admin@vormia.com',
            'password' => Hash::make('admin')
        ]);

        // Add RolesTableSeeder
        $roles = RolesTableSeeder::class;
        $this->call($roles);

        // Assign roles with ID 1 to the user
        $admin->roles()->attach(1);

        // Check if settings already exist before seeding
        if (!DB::table('settings')->exists()) {
            $this->call(SettingSeeder::class);
        }

        // Check if the admin user already exists
        $admin = User::where('email', 'admin@vormia.com')->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'admin',
                'name' => 'John Doe',
                'email' => 'admin@vormia.com',
                'password' => Hash::make('admin')
            ]);
        }

        // Check if roles already exist before seeding
        if (!DB::table('roles')->exists()) {
            $this->call(RolesTableSeeder::class);
        }

        // Assign role ID 1 to the admin if not already assigned
        if (!$admin->roles()->where('role_id', 1)->exists()) {
            $admin->roles()->attach(1);
        }
    }
}
