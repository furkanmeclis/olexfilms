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
        Schema::create('nexptg_api_user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nexptg_api_user_id')->constrained('nexptg_api_users')->onDelete('cascade');
            $table->string('type'); // auth_error, validation_error, sync_error, exception
            $table->integer('status_code')->nullable();
            $table->text('message');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index('nexptg_api_user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexptg_api_user_logs');
    }
};
