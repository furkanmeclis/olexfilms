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
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('car_brands')->onDelete('cascade');
            $table->string('name');
            $table->string('external_id')->unique();
            $table->timestamp('last_update')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['brand_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};
