<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `attendance` MODIFY `check_in` DATETIME NULL DEFAULT NULL');
        DB::statement('ALTER TABLE `attendance` MODIFY `check_out` DATETIME NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `attendance` MODIFY `check_in` TIMESTAMP NULL DEFAULT NULL');
    }
};
