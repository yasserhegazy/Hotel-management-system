<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('email', 128)->unique();
            $table->string('phone', 32)->nullable();
            $table->string('password', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('preferred_language', 8)->default('en');
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->string('setup_token', 64)->nullable()->unique();
            $table->timestamp('setup_token_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};
