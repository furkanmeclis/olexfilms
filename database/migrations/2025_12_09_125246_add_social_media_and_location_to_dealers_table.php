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
        Schema::table('dealers', function (Blueprint $table) {
            // Sosyal medya alanları
            $table->string('facebook_url')->nullable()->after('address');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('twitter_url')->nullable()->after('instagram_url');
            $table->string('linkedin_url')->nullable()->after('twitter_url');
            $table->string('website_url')->nullable()->after('linkedin_url');

            // Konum alanları
            $table->string('city')->nullable()->after('website_url');
            $table->string('district')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'instagram_url',
                'twitter_url',
                'linkedin_url',
                'website_url',
                'city',
                'district',
            ]);
        });
    }
};
