<?php

namespace App\Enums;

enum StockMovementActionEnum: string
{
    case IMPORTED = 'imported';
    case TRANSFERRED_TO_DEALER = 'transferred_to_dealer';
    case RECEIVED = 'received';
    case USED_IN_SERVICE = 'used_in_service';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::IMPORTED->value => 'Stok Girişi',
            self::TRANSFERRED_TO_DEALER->value => 'Bayiye Transfer',
            self::RECEIVED->value => 'Teslim Alındı',
            self::USED_IN_SERVICE->value => 'Serviste Kullanıldı',
        ];
    }
}
