<?php

namespace App\Enums;

enum NexptgApiLogTypeEnum: string
{
    case AUTH_ERROR = 'auth_error';
    case VALIDATION_ERROR = 'validation_error';
    case SYNC_ERROR = 'sync_error';
    case EXCEPTION = 'exception';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::AUTH_ERROR->value => 'Kimlik Doğrulama Hatası',
            self::VALIDATION_ERROR->value => 'Doğrulama Hatası',
            self::SYNC_ERROR->value => 'Senkronizasyon Hatası',
            self::EXCEPTION->value => 'İstisna',
        ];
    }
}


