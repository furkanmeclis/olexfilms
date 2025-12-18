<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasyonu çalıştır.
     */
    public function up(): void
    {
        Schema::create('service_nexptg_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('nexptg_report_id')->unique()->constrained('nexptg_reports')->onDelete('cascade');
            $table->string('match_type', 32); // 'before' or 'after' için uzunluk kısıtlandı
            $table->timestamps();

            // Çok uzun string indexinden kaçınmak için string tipi ve uzunluk sınırını belirttik
            $table->index(['service_id', 'match_type'], 'srv_nexptg_svcid_mt_idx');
        });
    }

    /**
     * Migrasyonu geri al.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_nexptg_report');
    }
};
