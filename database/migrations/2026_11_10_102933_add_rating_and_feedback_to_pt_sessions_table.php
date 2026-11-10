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
        Schema::table('pt_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('completed_at');
            $table->string('member_feedback', 255)->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pt_sessions', function (Blueprint $table) {
            $table->dropColumn(['rating', 'member_feedback']);
        });
    }
};
