<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class VatanSmsSettings extends Settings
{
    public ?string $api_id;

    public ?string $api_key;

    public ?string $sender;

    public string $endpoint;

    public bool $installed;

    public static function group(): string
    {
        return 'vatan_sms';
    }
}



