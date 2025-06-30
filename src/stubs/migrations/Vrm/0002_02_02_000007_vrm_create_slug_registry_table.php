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
        Schema::create(config('vormia.table_prefix') . 'slug_registry', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 100)->comment('Model class name or table name');
            $table->unsignedBigInteger('entity_id')->comment('Primary key of the related record');
            $table->string('slug', 200)->unique();
            $table->string('original_text', 255)->nullable()->comment('Original text before slugification');
            $table->boolean('is_primary')->default(true)->comment('If entity has multiple slugs, indicates primary one');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Composite index for quick lookups by entity
            $table->index(['entity_type', 'entity_id']);
            // Index for slug lookups
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'slug_registry');
    }
};
