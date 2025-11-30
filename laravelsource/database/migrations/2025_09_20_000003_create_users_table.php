<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_code', 6)->nullable();
            $table->timestamp('email_verification_code_expires_at')->nullable();
            $table->string('password');
            $table->string('auth_token', 64)->nullable()->unique();
            $table->string('discord_id')->nullable()->unique();
            $table->string('twitter_id')->nullable()->unique();
            $table->string('github_id')->nullable()->unique();
            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();

            // Add indexes for better performance
            $table->index('discord_id');
            $table->index('twitter_id');
            $table->index('github_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};