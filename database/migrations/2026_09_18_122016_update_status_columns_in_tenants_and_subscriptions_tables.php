<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `tenants` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING_PAYMENT'");
        DB::statement("ALTER TABLE `subscriptions` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `tenants` MODIFY COLUMN `status` ENUM('TRIAL', 'ACTIVE', 'PAST_DUE', 'GRACE_PERIOD', 'SUSPENDED', 'CANCELLED', 'EXPIRED') NOT NULL DEFAULT 'TRIAL'");
        DB::statement("ALTER TABLE `subscriptions` MODIFY COLUMN `status` ENUM('TRIAL', 'ACTIVE', 'PAST_DUE', 'GRACE_PERIOD', 'SUSPENDED', 'CANCELLED', 'EXPIRED') NOT NULL DEFAULT 'TRIAL'");
    }
};
