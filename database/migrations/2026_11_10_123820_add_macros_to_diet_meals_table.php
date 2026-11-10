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
        Schema::table('diet_meals', function (Blueprint $table) {
            $table->integer('protein_grams')->nullable()->after('calories');
            $table->integer('carbs_grams')->nullable()->after('protein_grams');
            $table->integer('fat_grams')->nullable()->after('carbs_grams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_meals', function (Blueprint $table) {
            $table->dropColumn(['protein_grams', 'carbs_grams', 'fat_grams']);
        });
    }
};
