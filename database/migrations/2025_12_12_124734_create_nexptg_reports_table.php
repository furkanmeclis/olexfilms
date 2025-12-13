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
        Schema::create('nexptg_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique();
            $table->string('name');
            $table->timestamp('date');
            $table->timestamp('calibration_date')->nullable();
            $table->string('device_serial_number');
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->string('type_of_body')->nullable();
            $table->string('capacity')->nullable();
            $table->string('power')->nullable();
            $table->string('vin')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('year')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->json('extra_fields')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('external_id');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexptg_reports');
    }
};
