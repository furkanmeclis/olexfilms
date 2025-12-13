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
        Schema::create('nexptg_report_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('nexptg_reports')->onDelete('cascade');
            $table->boolean('is_inside')->default(false);
            $table->string('place_id');
            $table->string('part_type');
            $table->decimal('value', 10, 2)->nullable();
            $table->integer('interpretation')->nullable();
            $table->string('substrate_type')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->integer('position')->nullable();
            $table->timestamps();

            $table->index('report_id');
            $table->index(['report_id', 'is_inside']);
            $table->index('place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexptg_report_measurements');
    }
};
