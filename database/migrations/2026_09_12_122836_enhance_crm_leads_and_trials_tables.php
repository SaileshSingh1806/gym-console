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
        //
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'stage')) {
                $table->string('stage')->default('NEW_LEAD')->after('status');
            }
            if (! Schema::hasColumn('leads', 'remarks')) {
                $table->string('remarks')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('leads', 'estimated_value')) {
                $table->decimal('estimated_value', 10, 2)->default(0)->after('remarks');
            }
            if (! Schema::hasColumn('leads', 'next_action')) {
                $table->string('next_action')->nullable()->after('follow_up_date');
            }
            if (! Schema::hasColumn('leads', 'trial_date')) {
                $table->date('trial_date')->nullable()->after('next_action');
            }
            if (! Schema::hasColumn('leads', 'trial_time')) {
                $table->string('trial_time')->nullable()->after('trial_date');
            }
            if (! Schema::hasColumn('leads', 'trial_status')) {
                $table->string('trial_status')->nullable()->default('upcoming')->after('trial_time');
            }
        });

        if (! Schema::hasTable('lead_trials')) {
            Schema::create('lead_trials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->date('trial_date');
                $table->string('trial_time')->nullable(); // e.g. "01:03 PM" or "10:00 AM"
                $table->string('status')->default('upcoming'); // upcoming, completed, cancelled, no_show
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'branch_id', 'trial_date', 'status']);
            });
        }

        if (! Schema::hasTable('crm_feedback')) {
            Schema::create('crm_feedback', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->string('member_name');
                $table->string('member_phone')->nullable();
                $table->integer('rating')->default(5);
                $table->string('category')->default('General');
                $table->text('comments')->nullable();
                $table->string('status')->default('New'); // New, Reviewed, Resolved
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('crm_feedback');
        Schema::dropIfExists('lead_trials');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'stage',
                'remarks',
                'estimated_value',
                'next_action',
                'trial_date',
                'trial_time',
                'trial_status',
            ]);
        });
    }
};
