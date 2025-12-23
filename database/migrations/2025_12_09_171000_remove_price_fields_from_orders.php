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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_amount', 10, 2)->nullable()->after('notes');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
        });
    }
};
