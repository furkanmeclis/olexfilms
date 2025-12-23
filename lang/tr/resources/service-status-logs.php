<?php

return [
    'navigation_label' => 'Servis Durum Logları',
    'model_label' => 'Servis Durum Logu',
    'plural_model_label' => 'Servis Durum Logları',
    'fields' => [
        'service_id' => 'Servis No',
        'service' => 'Servis',
        'from_dealer_id' => 'Uygulayan Bayi',
        'fromDealer' => 'Uygulayan Bayi',
        'to_dealer_id' => 'Gidilen Şube',
        'toDealer' => 'Gidilen Şube',
        'user_id' => 'Ekleyen Kullanıcı',
        'user' => 'Ekleyen Kullanıcı',
        'notes' => 'Notlar',
        'log_notes' => 'Log Notları',
        'customer' => 'Müşteri',
        'carBrand' => 'Marka',
        'carModel' => 'Model',
        'plate' => 'Plaka',
        'status' => 'Servis Durumu',
        'created_at' => 'Tarih',
    ],
    'sections' => [
        'log_bilgileri' => 'Log Bilgileri',
        'servis_bilgileri' => 'Servis Bilgileri',
    ],
    'table' => [
        'columns' => [
            'service_no' => 'Servis No',
            'fromDealer' => 'Uygulayan Bayi',
            'toDealer' => 'Gidilen Şube',
            'user' => 'Ekleyen Kullanıcı',
            'notes' => 'Notlar',
            'created_at' => 'Tarih',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
    ],
];

