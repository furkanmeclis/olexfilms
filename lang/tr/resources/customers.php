<?php

return [
    'navigation_label' => 'Müşteriler',
    'model_label' => 'Müşteri',
    'plural_model_label' => 'Müşteriler',
    'fields' => [
        'name' => 'Ad Soyad / Firma',
        'type' => 'Tip',
        'phone' => 'Telefon',
        'email' => 'E-posta',
        'tc_no' => 'TC Kimlik No',
        'tax_no' => 'Vergi No',
        'tax_office' => 'Vergi Dairesi',
        'city' => 'İl',
        'district' => 'İlçe',
        'address' => 'Adres Detayı',
        'dealer' => 'Bayi',
        'creator' => 'Oluşturan',
        'fcm_token' => 'FCM Token',
        'created_at' => 'Oluşturulma',
        'updated_at' => 'Güncellenme',
    ],
    'sections' => [
        'musteri_bilgileri' => 'Müşteri Bilgileri',
        'adres_bilgileri' => 'Adres Bilgileri',
        'bayi_ve_olusturan' => 'Bayi ve Oluşturan',
        'fcm_token' => 'FCM Token',
        'tarihce' => 'Tarihçe',
    ],
    'table' => [
        'columns' => [
            'name' => 'Ad Soyad / Firma',
            'type' => 'Tip',
            'phone' => 'Telefon',
            'email' => 'E-posta',
            'dealer' => 'Bayi',
            'created_at' => 'Oluşturulma',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
        'open_customer_page' => 'Müşteri Sayfasını Aç',
    ],
];

