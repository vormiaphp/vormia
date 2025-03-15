<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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

        // Check if admin user already exists
        $existingUser = User::where('username', 'admin')
            ->orWhere('email', 'admin@vormia.com')
            ->first();

        if (!$existingUser) {
            // Create admin user only if it doesn't exist
            $admin = User::create([
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
        }
    }
}
