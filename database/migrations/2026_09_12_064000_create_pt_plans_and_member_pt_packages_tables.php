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
        Schema::create('pt_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->integer('total_sessions')->default(12);
            $table->integer('validity_days')->default(30);
            $table->decimal('default_price', 10, 2)->default(0.00);
            $table->decimal('trainer_commission_percent', 5, 2)->nullable();
            $table->string('sac_code')->nullable()->default('999723');
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->boolean('price_includes_gst')->default(true);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('includes_gate_pass')->default(false);
            $table->boolean('show_on_mobile_app')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('member_pt_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('pt_plan_id')->nullable()->constrained('pt_plans')->nullOnDelete();
            $table->string('package_name');
            $table->integer('total_sessions')->default(12);
            $table->integer('used_sessions')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('final_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'CANCELLED', 'COMPLETED'])->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'member_id', 'status']);
            $table->index(['tenant_id', 'trainer_id']);
            $table->index(['tenant_id', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_pt_packages');
        Schema::dropIfExists('pt_plans');
    }
};
