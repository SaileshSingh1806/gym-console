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
        // Trainers
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('specialization')->nullable(); // e.g. Bodybuilding, Yoga, CrossFit, Cardio
            $table->decimal('hourly_rate', 10, 2)->default(0.00);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->text('bio')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
        });

        // Classes & Bookings
        Schema::create('gym_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->default(20);
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('gym_class_id')->constrained('gym_classes')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('room_or_studio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'day_of_week']);
        });

        Schema::create('class_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('class_schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('booking_date');
            $table->enum('status', ['BOOKED', 'ATTENDED', 'CANCELLED', 'NO_SHOW'])->default('BOOKED');
            $table->timestamps();

            $table->unique(['class_schedule_id', 'member_id', 'booking_date']);
        });

        // Workout Plans
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete(); // Nullable for templates
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->string('title');
            $table->enum('goal', ['weight_loss', 'muscle_gain', 'endurance', 'general_fitness', 'flexibility'])->default('general_fitness');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_template')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'member_id']);
        });

        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained('workout_plans')->cascadeOnDelete();
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'day_1', 'day_2', 'day_3', 'day_4', 'day_5', 'day_6', 'day_7']);
            $table->string('exercise_name');
            $table->integer('sets')->default(3);
            $table->string('reps')->default('10-12');
            $table->string('weight')->nullable(); // e.g. "20kg" or "Bodyweight"
            $table->integer('rest_seconds')->default(60);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Diet Plans
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->string('title');
            $table->integer('daily_calories')->nullable();
            $table->integer('protein_grams')->nullable();
            $table->integer('carbs_grams')->nullable();
            $table->integer('fat_grams')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_template')->default(false);
            $table->text('guidelines')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'member_id']);
        });

        Schema::create('diet_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_plan_id')->constrained('diet_plans')->cascadeOnDelete();
            $table->enum('meal_type', ['breakfast', 'morning_snack', 'lunch', 'evening_snack', 'dinner', 'post_workout'])->default('breakfast');
            $table->time('recommended_time')->nullable();
            $table->string('meal_name');
            $table->text('items_description'); // e.g. "3 egg whites, 1 bowl oatmeal, 1 banana"
            $table->integer('calories')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Leads CRM
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->enum('source', ['walk_in', 'social_media', 'referral', 'website', 'advertisement', 'other'])->default('walk_in');
            $table->enum('status', ['NEW', 'CONTACTED', 'TRIAL_SCHEDULED', 'CONVERTED', 'LOST'])->default('NEW');
            $table->date('follow_up_date')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'branch_id', 'status']);
        });

        // Expenses
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'upi', 'other'])->default('cash');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'expense_date']);
        });

        // Inventory
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->string('category')->nullable(); // supplements, apparel, equipment, beverages
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->decimal('selling_price', 10, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_threshold')->default(5);
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->enum('type', ['IN', 'OUT', 'SALE', 'ADJUSTMENT'])->default('IN');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('diet_meals');
        Schema::dropIfExists('diet_plans');
        Schema::dropIfExists('workout_exercises');
        Schema::dropIfExists('workout_plans');
        Schema::dropIfExists('class_bookings');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('gym_classes');
        Schema::dropIfExists('trainers');
    }
};
