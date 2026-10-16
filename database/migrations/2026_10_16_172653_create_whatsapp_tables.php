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
        // 1. WhatsApp Contacts
        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 25)->index(); // Standard E.164 format (+91XXXXXXXXXX)
            $table->string('city')->nullable();
            $table->enum('type', ['member', 'lead', 'external'])->default('external');
            $table->string('source', 50)->default('manual'); // 'manual', 'csv_import', 'member_sync', 'lead_sync', 'website'
            $table->json('tags')->nullable();
            $table->enum('consent_status', ['opted_in', 'unknown', 'opted_out'])->default('unknown');
            $table->timestamp('consent_updated_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->enum('status', ['active', 'invalid', 'blocked'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'consent_status']);
            $table->index(['tenant_id', 'type']);
        });

        // 2. WhatsApp Contact Consent Audit Log
        Schema::create('whatsapp_contact_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('whatsapp_contacts')->cascadeOnDelete();
            $table->enum('consent_status', ['opted_in', 'unknown', 'opted_out']);
            $table->string('source', 100); // e.g. 'csv_import_documented', 'inbound_stop', 'inbound_start', 'admin_toggle', 'registration'
            $table->string('wording_version')->nullable(); // Version / statement text reference
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('evidence_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['tenant_id', 'contact_id']);
        });

        // 3. WhatsApp Message Templates (Cached/Synced from Meta WABA)
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('waba_template_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('language', 10)->default('en');
            $table->enum('category', ['MARKETING', 'UTILITY', 'AUTHENTICATION'])->default('MARKETING');
            $table->enum('status', ['APPROVED', 'PENDING', 'REJECTED', 'PAUSED', 'DISABLED'])->default('PENDING');
            $table->enum('header_type', ['NONE', 'TEXT', 'IMAGE', 'VIDEO', 'DOCUMENT'])->default('NONE');
            $table->text('header_content')->nullable();
            $table->text('body_text');
            $table->string('footer_text')->nullable();
            $table->json('buttons')->nullable();
            $table->json('variables')->nullable();
            $table->json('meta_raw_data')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name', 'language']);
            $table->index(['tenant_id', 'status']);
        });

        // 4. WhatsApp Campaigns
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('whatsapp_templates')->nullOnDelete();
            $table->string('name');
            $table->string('target_audience')->default('all_opted_in'); // 'all_opted_in', 'members', 'leads', 'custom_filter'
            $table->json('filters')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'running', 'paused', 'completed', 'cancelled', 'failed'])->default('draft');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('read_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->decimal('estimated_cost', 10, 2)->default(0.00);
            $table->decimal('actual_cost', 10, 2)->default(0.00);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // 5. WhatsApp Campaign Recipients
        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('whatsapp_campaigns')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('whatsapp_contacts')->cascadeOnDelete();
            $table->string('phone', 25);
            $table->json('parameters')->nullable();
            $table->enum('status', ['pending', 'queued', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->string('wamid')->nullable()->index();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'contact_id']);
            $table->index(['campaign_id', 'status']);
        });

        // 6. WhatsApp Conversations (Customer Service & Inbox)
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('whatsapp_contacts')->cascadeOnDelete();
            $table->string('phone', 25)->index();
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_preview')->nullable();
            $table->timestamp('last_incoming_at')->nullable(); // Marks the start of the 24-hr service window
            $table->unsignedInteger('unread_count')->default(0);
            $table->enum('status', ['open', 'archived'])->default('open');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'last_message_at']);
        });

        // 7. WhatsApp Messages (Inbound & Outbound)
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('whatsapp_contacts')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('whatsapp_campaigns')->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('type', 30)->default('text'); // text, template, image, video, audio, document, interactive, button_reply
            $table->text('message_body')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('media_mime_type', 100)->nullable();
            $table->text('media_caption')->nullable();
            $table->string('wamid')->nullable()->index();
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed', 'received'])->default('queued');
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta_raw_payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        // 8. WhatsApp Automations
        Schema::create('whatsapp_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger_event', 50); // member_created, membership_expiring, membership_expired, payment_received, member_birthday, lead_created
            $table->foreignId('template_id')->nullable()->constrained('whatsapp_templates')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('days_offset')->default(0); // -3 for 3 days before, 0 for day of, +1 for 1 day after
            $table->string('send_time', 10)->default('10:00'); // HH:mm
            $table->enum('message_category', ['UTILITY', 'MARKETING'])->default('UTILITY');
            $table->json('parameter_mappings')->nullable(); // {"1": "member.first_name", "2": "membership.end_date"}
            $table->json('config')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'trigger_event', 'is_active']);
        });

        // 9. WhatsApp Automation Execution Logs
        Schema::create('whatsapp_automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('automation_id')->constrained('whatsapp_automations')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('whatsapp_contacts')->cascadeOnDelete();
            $table->string('entity_type', 50)->nullable(); // Member, Membership, Payment, Lead
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('wamid')->nullable();
            $table->enum('status', ['sent', 'failed', 'skipped', 'throttled'])->default('sent');
            $table->string('skip_reason')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();

            $table->index(['automation_id', 'contact_id', 'entity_type', 'entity_id'], 'ws_auto_exec_idx');
        });

        // 10. WhatsApp Webhook Events (Idempotency and Debugging)
        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->index();
            $table->string('event_type', 50)->default('unknown');
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_events');
        Schema::dropIfExists('whatsapp_automation_logs');
        Schema::dropIfExists('whatsapp_automations');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_campaign_recipients');
        Schema::dropIfExists('whatsapp_campaigns');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_contact_consents');
        Schema::dropIfExists('whatsapp_contacts');
    }
};
