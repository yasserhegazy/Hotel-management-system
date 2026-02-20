<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('name', 128);
            $table->string('email', 128)->unique();
            $table->string('slug', 64)->unique();
            $table->string('database_name', 128)->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->enum('status', ['active', 'disabled'])->default('active');

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
