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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('payslip_number', 50)->index();
            $table->unsignedTinyInteger('month'); // 1 - 12
            $table->unsignedSmallInteger('year'); // e.g. 2026
            $table->date('pay_period_start');
            $table->date('pay_period_end');

            // Salary Structure
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->json('allowances')->nullable(); // [['name' => 'HRA', 'amount' => 2000], ...]
            $table->json('deductions')->nullable(); // [['name' => 'Advance', 'amount' => 1000], ...]
            $table->decimal('gross_salary', 10, 2)->default(0.00);
            $table->decimal('total_deductions', 10, 2)->default(0.00);
            $table->decimal('net_salary', 10, 2)->default(0.00);

            // Attendance & Days
            $table->unsignedSmallInteger('working_days')->default(30);
            $table->unsignedSmallInteger('present_days')->default(30);
            $table->unsignedSmallInteger('leaves_taken')->default(0);

            // Lifecycle & Payment
            $table->string('status', 30)->default('DRAFT')->index(); // DRAFT, APPROVED, PAID, CANCELLED
            $table->string('payment_method', 50)->nullable(); // CASH, UPI, BANK_TRANSFER, CHEQUE
            $table->string('transaction_reference', 100)->nullable(); // UTR / Cheque No / Txn ID
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();

            // Payout Details
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('ifsc_code', 50)->nullable();
            $table->string('upi_id', 100)->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'payslip_number'], 'tenant_payslip_unique');
            $table->index(['tenant_id', 'user_id', 'month', 'year'], 'tenant_user_month_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
