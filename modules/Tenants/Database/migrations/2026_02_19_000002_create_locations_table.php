<?php

declare(strict_types=1);

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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->string('country_name', 64);
            $table->string('region_name', 64)->nullable();
            $table->string('city', 64);
            $table->string('address_line', 256)->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
