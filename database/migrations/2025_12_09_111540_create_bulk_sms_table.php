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
        Schema::create('bulk_sms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('message');
            $table->string('sender');
            $table->string('message_type')->default('normal');
            $table->string('message_content_type')->default('bilgi');
            $table->string('target_type'); // 'users', 'customers', 'custom'
            $table->json('target_ids')->nullable();
            $table->string('status')->default('draft'); // 'draft', 'sending', 'completed', 'failed'
            $table->integer('total_recipients');
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_sms');
    }
};
