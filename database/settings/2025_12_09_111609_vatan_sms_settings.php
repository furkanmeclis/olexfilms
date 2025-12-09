<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('vatan_sms', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('api_id', env('VATAN_SMS_API_ID'));
            $blueprint->add('api_key', env('VATAN_SMS_API_KEY'));
            $blueprint->add('sender', env('VATAN_SMS_SENDER'));
            $blueprint->add('endpoint', env('VATAN_SMS_ENDPOINT', 'https://api.vatansms.net/api/v1'));
            $blueprint->add('installed', env('VATAN_SMS_API_INSTALLED', 0) == 1);
        });
    }
};
