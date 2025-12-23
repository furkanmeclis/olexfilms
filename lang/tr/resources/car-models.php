<?php

return [
    'navigation_label' => 'Araç Modelleri',
    'model_label' => 'Araç Modeli',
    'plural_model_label' => 'Araç Modelleri',
    'fields' => [
        'brand_id' => 'Marka',
        'brand' => 'Marka',
        'name' => 'Model Adı',
        'external_id' => 'Dış ID',
        'powertrain' => 'Güç Aktarımı',
        'yearstart' => 'Başlangıç Yılı',
        'yearstop' => 'Bitiş Yılı',
        'coupe' => 'Gövde Tipi',
        'is_active' => 'Aktif',
        'created_at' => 'Oluşturulma',
    ],
    'sections' => [
        'model_bilgileri' => 'Model Bilgileri',
        'modification_bilgileri' => 'Modification Bilgileri',
    ],
    'table' => [
        'columns' => [
            'brand' => 'Marka Logo',
            'brand_name' => 'Marka',
            'name' => 'Model Adı',
            'powertrain' => 'Motor Tipi',
            'yearstart' => 'Yıl Başlangıç',
            'yearstop' => 'Yıl Bitiş',
            'coupe' => 'Kasa Tipi',
            'is_active' => 'Aktif',
            'created_at' => 'Oluşturulma',
        ],
        'filters' => [
            'powertrain' => 'Motor Tipi',
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
