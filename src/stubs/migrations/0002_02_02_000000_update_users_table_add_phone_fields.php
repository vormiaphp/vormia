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
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->unique()->nullable()->default(null)->after('email');
            }

            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->default(null)->after('phone');
            }

            if (!Schema::hasColumn('users', 'flag')) {
                $table->integer('flag')->default(1)->after('remember_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('users', 'phone') ? 'phone' : null,
                Schema::hasColumn('users', 'phone_verified_at') ? 'phone_verified_at' : null,
                Schema::hasColumn('users', 'flag') ? 'flag' : null,
            ]));
        });
    }
};
