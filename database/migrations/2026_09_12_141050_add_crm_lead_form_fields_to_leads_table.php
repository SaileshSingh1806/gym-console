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
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'instagram_handle')) {
                $table->string('instagram_handle')->nullable()->after('email');
            }
            if (! Schema::hasColumn('leads', 'city')) {
                $table->string('city')->nullable()->after('instagram_handle');
            }
            if (! Schema::hasColumn('leads', 'member_count')) {
                $table->integer('member_count')->default(1)->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['instagram_handle', 'city', 'member_count']);
        });
    }
};
