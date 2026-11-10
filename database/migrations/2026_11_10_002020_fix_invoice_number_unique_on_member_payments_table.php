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
        Schema::table('member_payments', function (Blueprint $table) {
            // Drop the global unique constraint on invoice_number
            $table->dropUnique('member_payments_invoice_number_unique');

            // Add tenant-scoped unique constraint so each tenant has their own invoice numbers
            $table->unique(['tenant_id', 'invoice_number'], 'member_payments_tenant_invoice_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_payments', function (Blueprint $table) {
            $table->dropUnique('member_payments_tenant_invoice_unique');
            $table->unique('invoice_number', 'member_payments_invoice_number_unique');
        });
    }
};
