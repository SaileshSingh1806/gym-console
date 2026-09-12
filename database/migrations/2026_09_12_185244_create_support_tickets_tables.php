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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('subject', 200);
            $table->string('category', 50)->default('general'); // technical, billing, feature_request, account, general
            $table->string('priority', 20)->default('medium');  // low, medium, high, urgent
            $table->string('status', 30)->default('open');      // open, in_progress, answered, resolved, closed
            $table->timestamp('last_reply_at')->nullable();
            $table->foreignId('last_reply_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_admin_reply')->default(false);
            $table->text('message');
            $table->string('attachment_path', 500)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
