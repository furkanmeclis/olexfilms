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
        Schema::table('nexptg_reports', function (Blueprint $table) {
            $table->foreignId('api_user_id')->nullable()->after('id')->constrained('nexptg_api_users')->onDelete('cascade');
            $table->index('api_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nexptg_reports', function (Blueprint $table) {
            $table->dropForeign(['api_user_id']);
            $table->dropIndex(['api_user_id']);
            $table->dropColumn('api_user_id');
        });
    }
};
