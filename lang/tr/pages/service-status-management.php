<?php

return [
    'navigation_label' => 'Hizmet Arama',
    'title' => 'Hizmet Arama',
    'fields' => [
        'service_no' => 'Hizmet No',
    ],
    'sections' => [
        'hizmet_arama' => 'Hizmet Arama',
    ],
    'actions' => [
        'add_service_status_log' => 'Servis Durum Kaydı Ekle',
        'search_service' => 'Hizmet Ara',
    ],
    'table' => [
        'columns' => [
            'fromDealer' => 'Uygulayan Bayi',
            'toDealer' => 'Gidilen Şube',
            'user' => 'Ekleyen Kullanıcı',
            'notes' => 'Notlar',
            'created_at' => 'Tarih',
        ],
        'empty' => [
            'heading' => 'Henüz log kaydı yok',
            'description' => 'Bu servis için henüz bir durum logu eklenmemiş.',
        ],
    ],
    'messages' => [
        'service_not_found' => 'Hizmet Bulunamadı',
        'service_not_found_body' => 'Önce bir hizmet araması yapmalısınız.',
        'service_no_required' => 'Hizmet No Gerekli',
        'service_no_required_body' => 'Lütfen bir hizmet numarası girin.',
        'service_not_found_error' => 'Girdiğiniz hizmet numarasına ait bir servis bulunamadı.',
        'service_found' => 'Hizmet Bulundu',
        'service_found_body' => 'Servis bilgileri yüklendi.',
        'status_log_added' => 'Servis Durum Kaydı Eklendi',
        'status_log_added_body' => 'Servis durum kaydı başarıyla eklendi.',
    ],
    'modals' => [
        'add_status_log' => [
            'title' => 'Servis Durum Kaydı Ekleme',
            'fields' => [
                'notes' => 'Notlar',
            ],
        ],
    ],
];

