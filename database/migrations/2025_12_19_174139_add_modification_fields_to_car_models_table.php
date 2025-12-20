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
        Schema::table('car_models', function (Blueprint $table) {
            $table->string('powertrain')->nullable()->after('last_update');
            $table->integer('yearstart')->nullable()->after('powertrain');
            $table->integer('yearstop')->nullable()->after('yearstart');
            $table->string('coupe')->nullable()->after('yearstop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            $table->dropColumn(['powertrain', 'yearstart', 'yearstop', 'coupe']);
        });
    }
};
