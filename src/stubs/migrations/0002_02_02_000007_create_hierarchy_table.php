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
        Schema::create('hierarchy', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('default');
            $table->string('group')->nullable()->default(null);
            $table->bigInteger('parent')->nullable()->default(0);
            $table->string('name', 500);
            $table->integer('flag')->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hierarchy');
    }
};
