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
        Schema::create('staff_sales_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('period_type', 30)->default('monthly'); // monthly, weekly, custom
            $table->string('period_key', 30)->nullable()->index(); // e.g. '2026-11'
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('target_revenue', 12, 2)->default(0);
            $table->unsignedInteger('target_admissions')->default(0);
            $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_sales_targets');
    }
};
