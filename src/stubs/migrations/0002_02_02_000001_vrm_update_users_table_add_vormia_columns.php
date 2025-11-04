<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only modify email if it exists
            if (Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->default(null)->change();
            }

            // Only add username if it doesn't exist
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->default(null)->after('name');
            }

            // Only add phone if it doesn't exist
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->unique()->nullable()->default(null)->after('email_verified_at');
            }

            // Only add phone_verified_at if it doesn't exist
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->default(null)->after('phone');
            }

            // Make password nullable (Socialite users may not have one)
            if (Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable()->default(null)->change();
            }

            // Add Socialite fields
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('password'); // e.g., 'google', 'github'
            }

            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider'); // Unique ID from provider
            }

            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider_id'); // Profile image URL
            }

            // Only add is_active if it doesn't exist
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('remember_token');
            }

            // Only add deleted_at if it doesn't exist (for soft deletes)
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only modify email if it exists
            if (Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable(false)->default('')->change();
            }

            // Only remove username if it exists
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }

            // Only remove phone if it exists
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            // Only remove phone_verified_at if it exists
            if (Schema::hasColumn('users', 'phone_verified_at')) {
                $table->dropColumn('phone_verified_at');
            }

            // Make password nullable (Socialite users may not have one)
            if (Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable(false)->default('')->change();
            }

            // Only remove Socialite fields if they exist
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }

            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropColumn('provider_id');
            }

            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }

            // Only remove is_active if it exists
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }

            // Only remove deleted_at if it exists (for soft deletes)
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
