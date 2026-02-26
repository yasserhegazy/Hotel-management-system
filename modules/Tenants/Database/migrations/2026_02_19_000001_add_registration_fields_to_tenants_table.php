<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('phone', 32)->unique()->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('phone');
            $table->unsignedBigInteger('owner_id')->nullable()->change();
        });

        // Modify status enum - MySQL syntax
        // Note: For testing with SQLite, the status column should be recreated with the new values
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tenants MODIFY status ENUM('pending_verification', 'verified', 'active', 'disabled') DEFAULT 'pending_verification'");
        } else {
            // SQLite: recreate the column with new values
            DB::statement("UPDATE tenants SET status = 'pending_verification' WHERE status NOT IN ('active', 'disabled')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email_verified_at']);
            $table->unsignedBigInteger('owner_id')->nullable(false)->change();
        });

        // Restore original status enum - MySQL syntax
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tenants MODIFY status ENUM('active', 'disabled') DEFAULT 'active'");
        }
    }
};
