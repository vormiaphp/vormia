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
        Schema::create(config('vormia.table_prefix') . 'taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index()->comment('Describes entity type: country, category, gender, etc.');
            $table->string('group')->nullable()->default(null)->comment('Grouping');
            $table->foreignId('parent_id')->nullable()->constrained(config('vormia.table_prefix') . 'taxonomies')->nullOnDelete();
            $table->string('reference')->nullable()->default(null)->comment('Reference to external entity');
            $table->string('name', 500);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0)->comment('For manual ordering');
            $table->timestamps()->useCurrent();
            $table->softDeletes();

            $table->index(['type', 'group', 'parent_id', 'reference', 'is_active']);
        });

        Schema::create(config('vormia.table_prefix') . 'taxonomy_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained(config('vormia.table_prefix') . 'taxonomies')->onDelete('cascade');
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps()->useCurrent();

            // Composite index for quick lookups
            $table->unique(['taxonomy_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'taxonomy_meta');
        Schema::dropIfExists(config('vormia.table_prefix') . 'taxonomies');
    }
};
