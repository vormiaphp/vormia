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
        Schema::create(config('vormia.table_prefix') . 'permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->references('id')->on(config('vormia.table_prefix') . 'permissions')->onDelete('cascade');
            $table->foreignId('role_id')->references('id')->on(config('vormia.table_prefix') . 'roles')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps()->useCurrent();

            // Primary key
            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'permission_role');
    }
};
