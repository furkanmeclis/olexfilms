<?php

namespace App\Enums;

enum CarPartEnum: string
{
    case BODY_TAVAN = 'body_tavan';
    case BODY_KAPUT = 'body_kaput';
    case BODY_BAGAJ = 'body_bagaj';
    case BODY_ARKA_TAMPON = 'body_arka_tampon';
    case BODY_ON_TAMPON = 'body_on_tampon';
    case BODY_SOL_ARKA_CAMURLUK = 'body_sol_arka_camurluk';
    case BODY_SOL_ON_CAMURLUK = 'body_sol_on_camurluk';
    case BODY_SOL_ARKA_KAPI = 'body_sol_arka_kapi';
    case BODY_SOL_ON_KAPI = 'body_sol_on_kapi';
    case BODY_SAG_ARKA_CAMURLUK = 'body_sag_arka_camurluk';
    case BODY_SAG_ON_CAMURLUK = 'body_sag_on_camurluk';
    case BODY_SAG_ARKA_KAPI = 'body_sag_arka_kapi';
    case BODY_SAG_ON_KAPI = 'body_sag_on_kapi';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::BODY_TAVAN->value => 'Tavan',
            self::BODY_KAPUT->value => 'Kaput',
            self::BODY_BAGAJ->value => 'Bagaj',
            self::BODY_ARKA_TAMPON->value => 'Arka Tampon',
            self::BODY_ON_TAMPON->value => 'Ön Tampon',
            self::BODY_SOL_ARKA_CAMURLUK->value => 'Sol Arka Çamurluk',
            self::BODY_SOL_ON_CAMURLUK->value => 'Sol Ön Çamurluk',
            self::BODY_SOL_ARKA_KAPI->value => 'Sol Arka Kapı',
            self::BODY_SOL_ON_KAPI->value => 'Sol Ön Kapı',
            self::BODY_SAG_ARKA_CAMURLUK->value => 'Sağ Arka Çamurluk',
            self::BODY_SAG_ON_CAMURLUK->value => 'Sağ Ön Çamurluk',
            self::BODY_SAG_ARKA_KAPI->value => 'Sağ Arka Kapı',
            self::BODY_SAG_ON_KAPI->value => 'Sağ Ön Kapı',
        ];
    }
}

