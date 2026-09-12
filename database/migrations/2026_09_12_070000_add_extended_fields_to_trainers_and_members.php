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
        Schema::table('trainers', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('bio');
            $table->string('certification')->nullable()->after('specialization');
            $table->decimal('salary', 10, 2)->default(0.00)->after('hourly_rate');
            $table->string('salary_type')->default('Fixed Monthly')->after('salary');
            $table->string('salary_pay_day')->default('1st of every month')->after('salary_type');
            $table->date('joining_date')->nullable()->after('salary_pay_day');
            $table->boolean('is_featured')->default(false)->after('status');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('trainer_id')->nullable()->after('branch_id')->constrained('trainers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trainer_id');
        });

        Schema::table('trainers', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'certification',
                'salary',
                'salary_type',
                'salary_pay_day',
                'joining_date',
                'is_featured',
            ]);
        });
    }
};
