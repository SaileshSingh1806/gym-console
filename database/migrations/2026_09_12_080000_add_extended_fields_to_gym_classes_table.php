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
        Schema::table('gym_classes', function (Blueprint $table) {
            $table->string('class_type')->default('Yoga')->after('name');
            $table->foreignId('instructor_id')->nullable()->after('class_type')->constrained('trainers')->nullOnDelete();
            $table->string('thumbnail_path')->nullable()->after('instructor_id');
            $table->integer('validity_days')->default(30)->after('fee');
            $table->integer('total_sessions')->nullable()->after('validity_days');
            $table->integer('cancellation_hours')->default(4)->after('total_sessions');
            $table->string('sac_code')->default('999721')->after('cancellation_hours');
            $table->decimal('gst_rate', 5, 2)->default(0.00)->after('sac_code');
            $table->boolean('price_includes_gst')->default(false)->after('gst_rate');
            $table->integer('duration_minutes')->default(60)->after('price_includes_gst');
            $table->string('room_location')->nullable()->after('duration_minutes');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->string('status')->default('ACTIVE')->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instructor_id');
            $table->dropColumn([
                'class_type',
                'thumbnail_path',
                'validity_days',
                'total_sessions',
                'cancellation_hours',
                'sac_code',
                'gst_rate',
                'price_includes_gst',
                'duration_minutes',
                'room_location',
                'is_featured',
                'status',
            ]);
        });
    }
};
