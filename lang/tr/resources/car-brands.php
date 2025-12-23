<?php

return [
    'navigation_label' => 'Araç Markaları',
    'model_label' => 'Araç Markası',
    'plural_model_label' => 'Araç Markaları',
    'fields' => [
        'name' => 'Marka Adı',
        'external_id' => 'Dış ID',
        'logo' => 'Logo',
        'is_active' => 'Aktif',
        'created_at' => 'Oluşturulma',
    ],
    'sections' => [
        'marka_bilgileri' => 'Marka Bilgileri',
    ],
    'table' => [
        'columns' => [
            'logo' => 'Logo',
            'name' => 'Marka Adı',
            'is_active' => 'Aktif',
            'created_at' => 'Oluşturulma',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
        'force_delete' => 'Kalıcı Sil',
        'restore' => 'Geri Yükle',
    ],
];

