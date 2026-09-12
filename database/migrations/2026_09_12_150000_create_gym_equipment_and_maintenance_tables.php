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
        Schema::create('gym_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('category')->default('Cardio'); // Cardio, Strength & Weight, AC & HVAC, Cables & Racks, Electrical & Facility, Audio/Visual, Other
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable(); // e.g. "Cardio Zone", "Main Hall", "AC Plant / Duct 1"
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->default(0.00);
            $table->date('warranty_expiry_date')->nullable();
            $table->integer('maintenance_interval_days')->default(90); // 30, 60, 90, 180, 365
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->string('status')->default('OPERATIONAL'); // OPERATIONAL, MAINTENANCE_DUE, UNDER_REPAIR, OUT_OF_SERVICE
            $table->string('vendor_name')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'next_service_date']);
            $table->index(['tenant_id', 'category']);
        });

        Schema::create('equipment_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('gym_equipment_id')->constrained('gym_equipment')->cascadeOnDelete();
            $table->string('maintenance_type')->default('Routine Service'); // Routine Service, AC Gas & Filter Service, Repair, Part Replacement, Inspection
            $table->date('service_date');
            $table->string('technician_name')->nullable();
            $table->string('technician_contact')->nullable();
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->string('status_after_service')->default('OPERATIONAL');
            $table->text('work_summary')->nullable();
            $table->text('replaced_parts')->nullable();
            $table->date('next_service_date')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'gym_equipment_id']);
            $table->index(['tenant_id', 'service_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance_logs');
        Schema::dropIfExists('gym_equipment');
    }
};
