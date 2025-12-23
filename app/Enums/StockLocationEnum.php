<?php

namespace App\Enums;

enum StockLocationEnum: string
{
    case CENTER = 'center';
    case DEALER = 'dealer';
    case SERVICE = 'service';
    case TRASH = 'trash';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::CENTER->value => 'Merkez',
            self::DEALER->value => 'Bayi',
            self::SERVICE->value => 'Servis',
            self::TRASH->value => 'Çöp',
        ];
    }
}
