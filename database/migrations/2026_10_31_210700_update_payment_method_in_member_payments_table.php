<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE member_payments MODIFY payment_method VARCHAR(50) NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE member_payments MODIFY payment_method ENUM('cash','card','pos','upi','bank_transfer','online') NOT NULL DEFAULT 'cash'");
    }
};
