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
        Schema::create(config('vormia.table_prefix') . 'utilities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 15)->default('private');
            $table->string('key', 200);
            $table->longText('value')->nullable();
            $table->tinyInteger('flag')->default(1);
            $table->timestamps()->useCurrent();

            // Indexes
            $table->index('type');
            $table->index(['key', 'flag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'utilities');
    }
};
