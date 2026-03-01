<?php

namespace VormiaPHP\Vormia\Tests\Integration;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use VormiaPHP\Vormia\Tests\IntegrationTestCase;
use Vormia\Vormia\Models\Permission;
use Vormia\Vormia\Models\Role;
use Workbench\App\Models\User;

class FullPackageIntegrationTest extends IntegrationTestCase
{
    public function test_migrations_run_successfully(): void
    {
        // RefreshDatabase already ran migrations in setUp - verify tables exist
        $this->assertTrue(Schema::hasTable('vrm_utilities'));
        $this->assertTrue(Schema::hasTable('vrm_roles'));
        $this->assertTrue(Schema::hasTable('vrm_permissions'));
    }

    public function test_role_model_crud(): void
    {
        $role = Role::create([
            'name' => 'editor',
            'slug' => 'editor',
            'module' => 'content',
            'authority' => 'main',
        ]);

        $this->assertDatabaseHas('vrm_roles', ['name' => 'editor']);
        $this->assertSame('editor', $role->slug);
    }

    public function test_permission_model_crud(): void
    {
        $permission = Permission::create([
            'name' => 'edit-posts',
            'slug' => 'edit-posts',
        ]);

        $this->assertDatabaseHas('vrm_permissions', ['name' => 'edit-posts']);
        $this->assertSame('edit-posts', $permission->slug);
    }

    public function test_api_roles_index(): void
    {
        Role::create(['name' => 'admin', 'slug' => 'admin', 'authority' => 'main']);

        $response = $this->getJson('/api/vrm/roles');

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_api_roles_store(): void
    {
        $response = $this->postJson('/api/vrm/roles', [
            'name' => 'moderator',
            'description' => 'Content moderator',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('vrm_roles', ['name' => 'moderator']);
    }

    public function test_api_login_success(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'access_token',
                'token_type',
            ],
            'message',
        ]);
        $this->assertNotNull($response->json('data.access_token'));
    }

    public function test_api_login_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_protected_route_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    public function test_api_protected_route_with_token(): void
    {
        $user = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => Hash::make('secret'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/user');

        $response->assertOk();
        $response->assertJsonPath('id', $user->id);
    }

    public function test_vormia_commands_registered(): void
    {
        $this->artisan('vormia:help')
            ->assertSuccessful();
    }

    public function test_vormia_help_command(): void
    {
        $this->artisan('vormia:help')
            ->assertSuccessful();
    }
}
