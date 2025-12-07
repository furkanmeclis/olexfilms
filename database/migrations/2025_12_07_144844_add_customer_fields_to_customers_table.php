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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('tc_no')->nullable()->after('type');
            $table->string('tax_no')->nullable()->after('tc_no');
            $table->string('tax_office')->nullable()->after('tax_no');
            $table->string('city')->nullable()->after('address');
            $table->string('district')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['tc_no', 'tax_no', 'tax_office', 'city', 'district']);
        });
    }
};
