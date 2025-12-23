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
        // SQLite için geçici tabloyu temizle
        if (\DB::getDriverName() === 'sqlite') {
            \DB::statement('DROP TABLE IF EXISTS "__temp__services"');
        }

        Schema::table('services', function (Blueprint $table) {
            // Önce mevcut NULL değerleri düzelt
            \DB::table('services')
                ->whereNull('plate')
                ->update(['plate' => 'PLAKA-YOK']);

            // Şimdi kolonları güncelle
            $table->string('plate')->nullable(false)->change();
            $table->integer('km')->nullable()->change();
            $table->string('package')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('plate')->nullable()->change();
            $table->integer('km')->nullable(false)->change();
            $table->string('package')->nullable(false)->change();
        });
    }
};
