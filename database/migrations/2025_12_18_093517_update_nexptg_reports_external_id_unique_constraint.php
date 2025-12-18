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
            // Mevcut unique constraint'i kaldır
            $table->dropUnique(['external_id']);
            
            // api_user_id ve external_id kombinasyonu için composite unique constraint ekle
            $table->unique(['api_user_id', 'external_id'], 'nexptg_reports_api_user_external_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nexptg_reports', function (Blueprint $table) {
            // Composite unique constraint'i kaldır
            $table->dropUnique('nexptg_reports_api_user_external_unique');
            
            // Eski unique constraint'i geri ekle
            $table->unique('external_id');
        });
    }
};
