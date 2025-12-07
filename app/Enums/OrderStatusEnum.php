<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::PENDING->value => 'Bekliyor',
            self::PROCESSING->value => 'Hazırlanıyor',
            self::SHIPPED->value => 'Kargoda',
            self::DELIVERED->value => 'Teslim Edildi',
            self::CANCELLED->value => 'İptal Edildi',
        ];
    }
}

