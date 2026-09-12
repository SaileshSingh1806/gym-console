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
        Schema::create('gym_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->integer('duration_minutes')->default(60);
            $table->string('timeslot_availability')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->boolean('is_visible_in_portal')->default(true);
            $table->boolean('is_locker_service')->default(false);
            $table->boolean('is_session_countable')->default(false);
            $table->integer('session_count')->default(1);
            $table->timestamps();
        });

        Schema::create('gym_service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('gym_service_id')->constrained('gym_services')->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('booking_time')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->integer('total_sessions')->default(1);
            $table->integer('sessions_left')->default(1);
            $table->string('locker_number')->nullable();
            $table->string('status')->default('active'); // pending, active, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_service_bookings');
        Schema::dropIfExists('gym_services');
    }
};
