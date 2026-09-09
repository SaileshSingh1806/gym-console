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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('model')->nullable(); // e.g. Hikvision DS-K1T341AMF, Turnstile Pro, QR Reader
            $table->enum('type', ['hikvision_facial', 'hikvision_turnstile', 'rfid_reader', 'qr_scanner', 'generic_biometric'])->default('hikvision_facial');
            $table->string('serial_number')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('port')->default(80);
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted
            $table->string('device_secret')->nullable(); // Webhook API authentication token
            $table->enum('direction', ['in', 'out', 'both'])->default('both');
            $table->enum('status', ['ONLINE', 'OFFLINE', 'ERROR'])->default('OFFLINE');
            $table->timestamp('last_seen_at')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'status']);
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->enum('event_type', ['ENTRY', 'EXIT', 'DENIED'])->default('ENTRY');
            $table->enum('access_status', ['GRANTED', 'DENIED_EXPIRED', 'DENIED_UNRECOGNIZED', 'DENIED_WRONG_BRANCH', 'DENIED_SUSPENDED', 'DENIED_INACTIVE'])->default('GRANTED');
            $table->timestamp('event_time');
            $table->string('card_or_face_id')->nullable();
            $table->string('raw_event_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'event_time']);
            $table->index(['tenant_id', 'member_id', 'event_time']);
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->date('date');
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->enum('method', ['manual', 'qr', 'biometric', 'facial'])->default('manual');
            $table->enum('status', ['PRESENT', 'LATE', 'AUTO_CHECKOUT'])->default('PRESENT');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'date']);
            $table->index(['tenant_id', 'member_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('devices');
    }
};
