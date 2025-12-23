<?php

namespace App\Enums;

enum NotificationPriorityEnum: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::CRITICAL->value => 'Kritik',
            self::HIGH->value => 'Yüksek',
            self::MEDIUM->value => 'Orta',
            self::LOW->value => 'Düşük',
        ];
    }
}
