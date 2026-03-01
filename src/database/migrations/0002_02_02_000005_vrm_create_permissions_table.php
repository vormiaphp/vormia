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
        Schema::create(config('vormia.table_prefix') . 'permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'create-post', 'delete-user'
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('guard_name')->default('web');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'permissions');
    }
};
