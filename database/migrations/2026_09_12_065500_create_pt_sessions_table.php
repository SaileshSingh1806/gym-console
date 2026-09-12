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
        Schema::create('pt_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->foreignId('member_pt_package_id')->nullable()->constrained('member_pt_packages')->nullOnDelete();
            $table->date('session_date');
            $table->time('start_time')->default('10:00:00');
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['SCHEDULED', 'COMPLETED', 'NO_SHOW', 'CANCELLED'])->default('SCHEDULED');
            $table->string('focus_area')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'session_date', 'status']);
            $table->index(['tenant_id', 'member_id']);
            $table->index(['tenant_id', 'trainer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_sessions');
    }
};
