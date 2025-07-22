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
        Schema::create(config('vormia.table_prefix') . 'auth_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 40); // e.g., email_verification, otp, reset_password
            $table->string('name', 40)->nullable()->default(null); // optional: e.g., login_otp, email_code
            $table->text('token');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Composite index for quick lookups by entity
            $table->index(['user_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vormia.table_prefix') . 'auth_tokens');
    }
};
