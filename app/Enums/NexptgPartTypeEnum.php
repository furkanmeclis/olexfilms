<?php

namespace App\Enums;

enum NexptgPartTypeEnum: string
{
    case ENGINE_COMPARTMENT = 'ENGINE_COMPARTMENT';
    case HOOD = 'HOOD';
    case LEFT_FRONT_DOOR = 'LEFT_FRONT_DOOR';
    case LEFT_FRONT_FENDER = 'LEFT_FRONT_FENDER';
    case LEFT_PILLAR = 'LEFT_PILLAR';
    case LEFT_REAR_DOOR = 'LEFT_REAR_DOOR';
    case LEFT_REAR_FENDER = 'LEFT_REAR_FENDER';
    case LEFT_SIDE = 'LEFT_SIDE';
    case RIGHT_FRONT_DOOR = 'RIGHT_FRONT_DOOR';
    case RIGHT_FRONT_FENDER = 'RIGHT_FRONT_FENDER';
    case RIGHT_PILLAR = 'RIGHT_PILLAR';
    case RIGHT_REAR_DOOR = 'RIGHT_REAR_DOOR';
    case RIGHT_REAR_FENDER = 'RIGHT_REAR_FENDER';
    case RIGHT_SIDE = 'RIGHT_SIDE';
    case ROOF = 'ROOF';
    case TRUNK = 'TRUNK';
    case TRUNK_INSIDE = 'TRUNK_INSIDE';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::ENGINE_COMPARTMENT->value => 'Motor Bölmesi',
            self::HOOD->value => 'Kaput',
            self::LEFT_FRONT_DOOR->value => 'Sol Ön Kapı',
            self::LEFT_FRONT_FENDER->value => 'Sol Ön Çamurluk',
            self::LEFT_PILLAR->value => 'Sol Direk',
            self::LEFT_REAR_DOOR->value => 'Sol Arka Kapı',
            self::LEFT_REAR_FENDER->value => 'Sol Arka Çamurluk',
            self::LEFT_SIDE->value => 'Sol Yan',
            self::RIGHT_FRONT_DOOR->value => 'Sağ Ön Kapı',
            self::RIGHT_FRONT_FENDER->value => 'Sağ Ön Çamurluk',
            self::RIGHT_PILLAR->value => 'Sağ Direk',
            self::RIGHT_REAR_DOOR->value => 'Sağ Arka Kapı',
            self::RIGHT_REAR_FENDER->value => 'Sağ Arka Çamurluk',
            self::RIGHT_SIDE->value => 'Sağ Yan',
            self::ROOF->value => 'Tavan',
            self::TRUNK->value => 'Bagaj',
            self::TRUNK_INSIDE->value => 'Bagaj İçi',
        ];
    }
}

