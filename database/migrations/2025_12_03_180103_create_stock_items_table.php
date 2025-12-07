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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('dealer_id')->nullable()->constrained('dealers')->onDelete('set null');
            $table->string('sku')->index();
            $table->string('barcode')->unique();
            $table->string('location'); // center, dealer, service, trash
            $table->string('status'); // available, reserved, used
            $table->timestamps();

            $table->index(['location', 'status']);
            $table->index(['dealer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
