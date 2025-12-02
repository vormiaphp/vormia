<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vrm\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            "name" => "Super Admin",
            "slug" => "super-admin",
            "module" => "Dashboard",
            "authority" => "main",
            "is_active" => 1,
            "description" => "Super Admin",
        ]);

        Role::create([
            "name" => "Admin",
            "slug" => "admin",
            "module" => "Dashboard",
            "authority" => "main",
            "is_active" => 1,
            "description" => "Admin",
        ]);

        Role::create([
            "name" => "Member",
            "slug" => "member",
            "module" => "contribution",
            "authority" => "member",
            "is_active" => 1,
            "description" => "Member",
        ]);
    }
}

