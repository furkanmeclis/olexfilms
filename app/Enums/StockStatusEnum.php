<?php

namespace App\Enums;

enum StockStatusEnum: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case USED = 'used';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::AVAILABLE->value => 'Müsait',
            self::RESERVED->value => 'Rezerve',
            self::USED->value => 'Kullanıldı',
        ];
    }
}
