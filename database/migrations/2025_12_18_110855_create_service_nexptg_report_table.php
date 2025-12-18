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
        Schema::create('service_nexptg_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('nexptg_report_id')->unique()->constrained('nexptg_reports')->onDelete('cascade');
            $table->string('match_type'); // 'before' or 'after'
            $table->timestamps();

            $table->index(['service_id', 'match_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_nexptg_report');
    }
};
