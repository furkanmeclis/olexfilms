<?php

namespace App\Enums;

enum NotificationStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::ACTIVE->value => 'Aktif',
            self::INACTIVE->value => 'Pasif',
        ];
    }
}

