<?php

namespace App\Enums;

enum CarPartEnum: string
{
    // Body parts
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

    // Window parts
    case WINDOW_SUNROOF = 'window_sunroof';
    case WINDOW_ON_CAM = 'window_on_cam';
    case WINDOW_ARKA_CAM = 'window_arka_cam';
    case WINDOW_SOL_ARKA_KAPI = 'window_sol_arka_kapi';
    case WINDOW_SOL_ON_KAPI = 'window_sol_on_kapi';
    case WINDOW_SAG_ARKA_KAPI = 'window_sag_arka_kapi';
    case WINDOW_SAG_ON_KAPI = 'window_sag_on_kapi';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            // Body parts
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
            // Window parts
            self::WINDOW_SUNROOF->value => 'Sunroof',
            self::WINDOW_ON_CAM->value => 'Ön Cam',
            self::WINDOW_ARKA_CAM->value => 'Arka Cam',
            self::WINDOW_SOL_ARKA_KAPI->value => 'Sol Arka Kapı Camı',
            self::WINDOW_SOL_ON_KAPI->value => 'Sol Ön Kapı Camı',
            self::WINDOW_SAG_ARKA_KAPI->value => 'Sağ Arka Kapı Camı',
            self::WINDOW_SAG_ON_KAPI->value => 'Sağ Ön Kapı Camı',
        ];
    }

    /**
     * Check if this part is a body part
     */
    public function isBody(): bool
    {
        return str_starts_with($this->value, 'body_');
    }

    /**
     * Check if this part is a window part
     */
    public function isWindow(): bool
    {
        return str_starts_with($this->value, 'window_');
    }

    /**
     * Get all body parts
     *
     * @return array<self>
     */
    public static function getBodyParts(): array
    {
        return array_filter(
            self::cases(),
            fn (self $case) => $case->isBody()
        );
    }

    /**
     * Get all window parts
     *
     * @return array<self>
     */
    public static function getWindowParts(): array
    {
        return array_filter(
            self::cases(),
            fn (self $case) => $case->isWindow()
        );
    }

    /**
     * Get parts grouped by category
     *
     * @return array<string, array<self>>
     */
    public static function getGroupedParts(): array
    {
        return [
            'body' => self::getBodyParts(),
            'window' => self::getWindowParts(),
        ];
    }
}

