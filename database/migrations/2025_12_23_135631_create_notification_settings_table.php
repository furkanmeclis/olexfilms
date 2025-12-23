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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event'); // NotificationEventEnum
            $table->string('role'); // UserRoleEnum
            $table->text('message_template'); // Mesaj şablonu
            $table->string('priority'); // NotificationPriorityEnum
            $table->string('status')->default('active'); // NotificationStatusEnum (active/inactive)
            $table->timestamps();

            $table->unique(['event', 'role'], 'n_event_role_unique');
            $table->index(['role', 'status'], 'n_role_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
