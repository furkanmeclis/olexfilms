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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_no')->unique();
            $table->foreignId('dealer_id')->constrained('dealers')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_brand_id')->constrained('car_brands')->onDelete('cascade');
            $table->foreignId('car_model_id')->constrained('car_models')->onDelete('cascade');
            $table->integer('year');
            $table->string('vin')->nullable();
            $table->string('plate')->nullable();
            $table->integer('km');
            $table->string('package');
            $table->json('applied_parts')->nullable();
            $table->text('notes')->nullable();
            $table->string('status'); // draft, pending, processing, ready, completed, cancelled
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('service_no');
            $table->index('dealer_id');
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('car_brand_id');
            $table->index('car_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
