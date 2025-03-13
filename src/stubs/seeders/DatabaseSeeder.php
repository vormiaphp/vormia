<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        // User::factory(10)->create();
        $admin = User::with(['roles'])->create([
            'username' => 'admin',
            'name' => 'John Doe',
            'email' => 'admin@vormia.com',
            'password' => Hash::make('admin')
        ]);

        // Add RolesTableSeeder
        $roles = RolesTableSeeder::class;
        $this->call($roles);

        // ? Assign roles with ID 1 to the user
        $admin->roles()->attach(1);
    }
}
