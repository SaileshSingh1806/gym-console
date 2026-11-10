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
        Schema::table('crm_feedback', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_feedback', 'member_id')) {
                $table->foreignId('member_id')->nullable()->after('branch_id')->constrained('members')->nullOnDelete();
            }
            if (! Schema::hasColumn('crm_feedback', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('member_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('crm_feedback', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('comments');
            }
            if (! Schema::hasColumn('crm_feedback', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('is_anonymous');
            }
            if (! Schema::hasColumn('crm_feedback', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('admin_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_feedback', function (Blueprint $table) {
            $dropCols = [];
            foreach (['member_id', 'user_id', 'is_anonymous', 'admin_notes', 'resolved_at'] as $col) {
                if (Schema::hasColumn('crm_feedback', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (! empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
