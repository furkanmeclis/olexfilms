<?php

return [
    'navigation_label' => 'Kullanıcılar',
    'model_label' => 'Kullanıcı',
    'plural_model_label' => 'Kullanıcılar',
    'fields' => [
        'name' => 'Ad Soyad',
        'email' => 'E-posta',
        'phone' => 'Telefon',
        'password' => 'Şifre',
        'dealer_id' => 'Bayi',
        'dealer' => 'Bayi',
        'role' => 'Rol',
        'roles' => 'Rol',
        'is_active' => 'Aktif',
        'email_verified_at' => 'E-posta Doğrulandı',
        'created_at' => 'Oluşturulma',
        'updated_at' => 'Güncellenme',
    ],
    'sections' => [
        'kisisel_bilgiler' => 'Kişisel Bilgiler',
        'yetkilendirme' => 'Yetkilendirme',
        'guvenlik' => 'Güvenlik',
        'durum' => 'Durum',
    ],
    'table' => [
        'columns' => [
            'name' => 'Ad Soyad',
            'email' => 'E-posta',
            'phone' => 'Telefon',
            'dealer' => 'Bayi',
            'roles' => 'Rol',
            'is_active' => 'Aktif',
            'email_verified_at' => 'E-posta Doğrulandı',
            'created_at' => 'Oluşturulma',
            'updated_at' => 'Güncellenme',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
    ],
];
