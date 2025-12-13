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
        Schema::create('nexptg_report_tires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('nexptg_reports')->onDelete('cascade');
            $table->string('width')->nullable();
            $table->string('profile')->nullable();
            $table->string('diameter')->nullable();
            $table->string('maker')->nullable();
            $table->string('season')->nullable();
            $table->string('section')->nullable();
            $table->decimal('value1', 5, 2)->nullable();
            $table->decimal('value2', 5, 2)->nullable();
            $table->timestamps();

            $table->index('report_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexptg_report_tires');
    }
};
