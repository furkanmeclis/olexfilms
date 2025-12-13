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
        Schema::create('nexptg_history_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('history_id')->constrained('nexptg_histories')->onDelete('cascade');
            $table->integer('value')->nullable();
            $table->integer('interpretation')->nullable();
            $table->string('substrate_type')->nullable();
            $table->timestamp('date')->nullable();
            $table->timestamps();

            $table->index('history_id');
            $table->index(['history_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexptg_history_measurements');
    }
};
