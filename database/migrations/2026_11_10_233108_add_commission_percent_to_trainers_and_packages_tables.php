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
        Schema::table('trainers', function (Blueprint $table) {
            if (! Schema::hasColumn('trainers', 'commission_percent')) {
                $table->decimal('commission_percent', 5, 2)->nullable()->default(40.00)->after('salary_type');
            }
        });

        Schema::table('member_pt_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('member_pt_packages', 'trainer_commission_percent')) {
                $table->decimal('trainer_commission_percent', 5, 2)->nullable()->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            if (Schema::hasColumn('trainers', 'commission_percent')) {
                $table->dropColumn('commission_percent');
            }
        });

        Schema::table('member_pt_packages', function (Blueprint $table) {
            if (Schema::hasColumn('member_pt_packages', 'trainer_commission_percent')) {
                $table->dropColumn('trainer_commission_percent');
            }
        });
    }
};
