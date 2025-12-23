<?php

return [
    'car_part' => [
        'body_tavan' => 'Tavan',
        'body_kaput' => 'Kaput',
        'body_bagaj' => 'Bagaj',
        'body_arka_tampon' => 'Arka Tampon',
        'body_on_tampon' => 'Ön Tampon',
        'body_sol_arka_camurluk' => 'Sol Arka Çamurluk',
        'body_sol_on_camurluk' => 'Sol Ön Çamurluk',
        'body_sol_arka_kapi' => 'Sol Arka Kapı',
        'body_sol_on_kapi' => 'Sol Ön Kapı',
        'body_sag_arka_camurluk' => 'Sağ Arka Çamurluk',
        'body_sag_on_camurluk' => 'Sağ Ön Çamurluk',
        'body_sag_arka_kapi' => 'Sağ Arka Kapı',
        'body_sag_on_kapi' => 'Sağ Ön Kapı',
        'window_sunroof' => 'Sunroof',
        'window_on_cam' => 'Ön Cam',
        'window_arka_cam' => 'Arka Cam',
        'window_sol_arka_kapi' => 'Sol Arka Kapı Camı',
        'window_sol_on_kapi' => 'Sol Ön Kapı Camı',
        'window_sag_arka_kapi' => 'Sağ Arka Kapı Camı',
        'window_sag_on_kapi' => 'Sağ Ön Kapı Camı',
    ],

    'customer_type' => [
        'individual' => 'Bireysel',
        'corporate' => 'Kurumsal',
    ],

    'nexptg_api_log_type' => [
        'auth_error' => 'Kimlik Doğrulama Hatası',
        'validation_error' => 'Doğrulama Hatası',
        'sync_error' => 'Senkronizasyon Hatası',
        'exception' => 'İstisna',
    ],

    'nexptg_part_type' => [
        'ENGINE_COMPARTMENT' => 'Motor Bölmesi',
        'HOOD' => 'Kaput',
        'LEFT_FRONT_DOOR' => 'Sol Ön Kapı',
        'LEFT_FRONT_FENDER' => 'Sol Ön Çamurluk',
        'LEFT_PILLAR' => 'Sol Direk',
        'LEFT_REAR_DOOR' => 'Sol Arka Kapı',
        'LEFT_REAR_FENDER' => 'Sol Arka Çamurluk',
        'LEFT_SIDE' => 'Sol Yan',
        'RIGHT_FRONT_DOOR' => 'Sağ Ön Kapı',
        'RIGHT_FRONT_FENDER' => 'Sağ Ön Çamurluk',
        'RIGHT_PILLAR' => 'Sağ Direk',
        'RIGHT_REAR_DOOR' => 'Sağ Arka Kapı',
        'RIGHT_REAR_FENDER' => 'Sağ Arka Çamurluk',
        'RIGHT_SIDE' => 'Sağ Yan',
        'ROOF' => 'Tavan',
        'TRUNK' => 'Bagaj',
        'TRUNK_INSIDE' => 'Bagaj İçi',
    ],

    'nexptg_place_id' => [
        'left' => 'Sol',
        'right' => 'Sağ',
        'top' => 'Üst',
        'back' => 'Arka',
    ],

    'order_status' => [
        'pending' => 'Bekliyor',
        'processing' => 'Hazırlanıyor',
        'shipped' => 'Kargoda',
        'delivered' => 'Teslim Edildi',
        'cancelled' => 'İptal Edildi',
    ],

    'service_item_usage_type' => [
        'full' => 'Tamamı',
        'partial' => 'Parça',
    ],

    'service_report_match_type' => [
        'before' => 'Hizmet Öncesi',
        'after' => 'Hizmet Sonrası',
    ],

    'service_status' => [
        'draft' => 'Taslak',
        'pending' => 'Bekliyor',
        'processing' => 'İşlemde',
        'ready' => 'Hazır',
        'completed' => 'Tamamlandı',
        'cancelled' => 'İptal Edildi',
    ],

    'stock_location' => [
        'center' => 'Merkez',
        'dealer' => 'Bayi',
        'service' => 'Servis',
        'trash' => 'Çöp',
    ],

    'stock_movement_action' => [
        'imported' => 'Stok Girişi',
        'transferred_to_dealer' => 'Bayiye Transfer',
        'received' => 'Teslim Alındı',
        'used_in_service' => 'Serviste Kullanıldı',
    ],

    'stock_status' => [
        'available' => 'Müsait',
        'reserved' => 'Rezerve',
        'used' => 'Kullanıldı',
    ],

    'user_role' => [
        'super_admin' => 'Süper Admin',
        'center_staff' => 'Merkez Çalışanı',
        'dealer_owner' => 'Bayi Sahibi',
        'dealer_staff' => 'Bayi Çalışanı',
    ],

    'user_status' => [
        'active' => 'Aktif',
        'passive' => 'Pasif',
    ],
];

