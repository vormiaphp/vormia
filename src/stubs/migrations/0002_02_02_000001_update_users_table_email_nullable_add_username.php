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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only revert email if it exists
            if (Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable(false)->change();
            }

            // Only drop username if it exists
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
