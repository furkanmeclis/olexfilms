<?php

namespace Database\Seeders;

use App\Settings\VatanSmsSettings;
use Illuminate\Database\Seeder;

class VatanSmsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = app(VatanSmsSettings::class);

        $settings->api_id = env('VATAN_SMS_API_ID');
        $settings->api_key = env('VATAN_SMS_API_KEY');
        $settings->sender = env('VATAN_SMS_SENDER');
        $settings->endpoint = env('VATAN_SMS_ENDPOINT', 'https://api.vatansms.net/api/v1');
        $settings->installed = env('VATAN_SMS_API_INSTALLED', 0) == 1;

        $settings->save();
    }
}
