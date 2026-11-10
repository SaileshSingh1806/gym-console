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
        Schema::table('pt_sessions', function (Blueprint $table) {
            $table->string('status', 30)->default('PENDING')->change();
            $table->string('confirmation_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('token_expires_at')->nullable()->after('confirmation_token');
            $table->foreignId('logged_by_user_id')->nullable()->after('token_expires_at')->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by_member_id')->nullable()->after('logged_by_user_id')->constrained('members')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by_member_id');
            $table->timestamp('issue_reported_at')->nullable()->after('confirmed_at');
            $table->text('issue_notes')->nullable()->after('issue_reported_at');
            $table->string('otp_code', 10)->nullable()->after('issue_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pt_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('pt_sessions', 'logged_by_user_id')) {
                $table->dropForeign(['logged_by_user_id']);
            }
            if (Schema::hasColumn('pt_sessions', 'confirmed_by_member_id')) {
                $table->dropForeign(['confirmed_by_member_id']);
            }
            $table->dropColumn([
                'confirmation_token',
                'token_expires_at',
                'logged_by_user_id',
                'confirmed_by_member_id',
                'confirmed_at',
                'issue_reported_at',
                'issue_notes',
                'otp_code',
            ]);
        });
    }
};
