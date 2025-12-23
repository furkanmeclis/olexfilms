<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration'ı çalıştır.
     */
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event', 64); // NotificationEventEnum - kısa string, index için sınırlandır
            $table->string('role', 32); // UserRoleEnum - kısa string, index için sınırlandır
            $table->text('message_template'); // Mesaj şablonu
            $table->string('priority', 16); // NotificationPriorityEnum - kısa string
            $table->string('status', 16)->default('active'); // NotificationStatusEnum (active/inactive)
            $table->timestamps();

            $table->unique(['event', 'role'], 'n_e_r_unique');
            // MySQL'de index key length hatası olmaması için uzunluk belirtiyoruz
            $table->index(['role', 'status'], 'n_r_s_index');
        });
    }

    /**
     * Migration'ı geri al.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
