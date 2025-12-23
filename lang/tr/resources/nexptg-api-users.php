<?php

return [
    'navigation_label' => 'API Kullanıcıları',
    'model_label' => 'API Kullanıcı',
    'plural_model_label' => 'API Kullanıcıları',
    'fields' => [
        'username' => 'Kullanıcı Adı',
        'user_id' => 'Bağlı Kullanıcı',
        'user' => 'Bağlı Kullanıcı',
        'is_active' => 'Durum',
        'last_used_at' => 'Son Kullanım',
        'creator' => 'Oluşturan',
        'created_at' => 'Oluşturulma',
        'updated_at' => 'Güncellenme',
    ],
    'sections' => [
        'api_kullanici_bilgileri' => 'API Kullanıcı Bilgileri',
    ],
    'table' => [
        'columns' => [
            'username' => 'Kullanıcı Adı',
            'user' => 'Bağlı Kullanıcı',
            'is_active' => 'Durum',
            'last_used_at' => 'Son Kullanım',
            'created_at' => 'Oluşturulma',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
    ],
];
