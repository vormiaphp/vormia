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
        Schema::create(config('vormia.table_prefix') . 'roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('module', 2000)->nullable()->default('dashboard,users,permissions,upload,update,download');
            $table->string('authority', 20)->nullable()->default('main')->comment('main - backend, comp - commpany, part - partner');
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
        Schema::dropIfExists(config('vormia.table_prefix') . 'roles');
    }
};
