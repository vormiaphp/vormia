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
        Schema::create(config('vormia.table_prefix') . 'user_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'key']); // Prevent duplicate keys per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'user_meta');
    }
};
