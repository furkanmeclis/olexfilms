<?php

namespace App\Enums;

enum NexptgPlaceIdEnum: string
{
    case LEFT = 'left';
    case RIGHT = 'right';
    case TOP = 'top';
    case BACK = 'back';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::LEFT->value => 'Sol',
            self::RIGHT->value => 'Sağ',
            self::TOP->value => 'Üst',
            self::BACK->value => 'Arka',
        ];
    }
}

