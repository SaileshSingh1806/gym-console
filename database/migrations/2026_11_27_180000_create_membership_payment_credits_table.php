<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_payment_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('source_payment_id');
            $table->unsignedBigInteger('target_membership_id');
            $table->unsignedBigInteger('amount_cents');
            $table->string('reason', 500);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamp('created_at');
            $table->index(['tenant_id', 'member_id']);
            $table->index('source_payment_id');
            $table->index('target_membership_id');
            $table->unique(['source_payment_id', 'target_membership_id'], 'membership_credit_source_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_payment_credits');
    }
};
