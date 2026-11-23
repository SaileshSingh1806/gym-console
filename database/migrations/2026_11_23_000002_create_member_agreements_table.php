<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agreement_template_id')->nullable()->constrained('tenant_agreement_templates')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            
            // Unique secure link tokens
            $table->string('token', 64)->unique();
            $table->string('token_hash', 64)->index();
            $table->timestamp('token_expires_at')->nullable();
            
            // Overall status
            $table->string('status', 35)->default('PENDING')->index();
            $table->timestamp('link_sent_at')->nullable();
            $table->string('link_sent_channel', 30)->nullable(); // whatsapp, sms, manual_copy, email
            
            // Step 1: OTP Verification
            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->unsignedSmallInteger('otp_attempts')->default(0);
            
            // Step 2: Member Profile Confirmation
            $table->timestamp('profile_confirmed_at')->nullable();
            
            // Step 3: Health Declaration (PAR-Q)
            $table->json('health_declaration_answers')->nullable();
            $table->timestamp('health_declaration_accepted_at')->nullable();
            $table->boolean('has_medical_conditions')->default(false)->index();
            $table->text('medical_review_notes')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            
            // Step 4: Terms & Conditions and Open Terrace
            $table->timestamp('terms_accepted_at')->nullable();
            $table->boolean('terrace_rules_included')->default(false);
            $table->timestamp('terrace_declaration_accepted_at')->nullable();
            
            // Step 5: Electronic Signature & Audit
            $table->string('signature_type', 35)->default('digital_drawn');
            $table->longText('signature_data')->nullable();
            $table->string('signature_provider', 60)->default('GymDeskHub Digital Signature Audit Engine');
            $table->string('signature_reference', 100)->nullable()->index();
            $table->string('signature_hash', 64)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signer_ip', 45)->nullable();
            $table->text('signer_user_agent')->nullable();
            
            // PDF Document Storage
            $table->string('pdf_path', 255)->nullable();
            
            // Rejection / Decline
            $table->text('declined_reason')->nullable();
            $table->timestamp('declined_at')->nullable();
            
            // Full immutable audit trail (JSON array of events)
            $table->json('audit_trail')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_agreements');
    }
};
