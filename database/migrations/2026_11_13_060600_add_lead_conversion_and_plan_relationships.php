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
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'membership_plan_id')) {
                    $table->unsignedBigInteger('membership_plan_id')->nullable()->after('branch_id')->index();
                }
                if (!Schema::hasColumn('leads', 'converted_member_id')) {
                    $table->unsignedBigInteger('converted_member_id')->nullable()->after('status')->index();
                }
            });
        }

        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (!Schema::hasColumn('members', 'lead_id')) {
                    $table->unsignedBigInteger('lead_id')->nullable()->after('branch_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'membership_plan_id')) {
                    $table->dropColumn('membership_plan_id');
                }
                if (Schema::hasColumn('leads', 'converted_member_id')) {
                    $table->dropColumn('converted_member_id');
                }
            });
        }

        if (Schema::hasTable('members')) {
            Schema::table('members', function (Blueprint $table) {
                if (Schema::hasColumn('members', 'lead_id')) {
                    $table->dropColumn('lead_id');
                }
            });
        }
    }
};
