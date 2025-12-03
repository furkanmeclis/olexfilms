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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('dealer_id')->nullable()->constrained('dealers')->onDelete('set null');
            $table->string('phone')->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['dealer_id']);
            $table->dropColumn(['dealer_id', 'phone', 'avatar_path', 'is_active']);
        });
    }
};
