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
            // Make email nullable
            $table->string('email')->nullable()->default(null)->change();

            // Add username column and make it unique
            $table->string('username')->nullable()->unique()->default(null)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert email to NOT NULL (assuming original was NOT NULL)
            $table->string('email')->nullable(false)->change();

            // Drop the username column
            $table->dropColumn('username');
        });
    }
};
