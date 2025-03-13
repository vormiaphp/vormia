<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            "name" => 'Admin',
            "slug" => 'admin',
            "module" => 'dashboard,users,permissions,setup,control',
        ]);

        Role::create([
            "name" => 'Customer',
            "slug" => 'customer',
            "module" => 'portal',
        ]);

        Role::create([
            "name" => 'Member',
            "slug" => 'member',
            "module" => 'portal',
        ]);

        Role::create([
            "name" => 'User',
            "slug" => 'user',
            "module" => 'portal',
        ]);
    }
}
