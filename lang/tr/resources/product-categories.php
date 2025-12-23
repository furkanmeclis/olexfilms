<?php

return [
    'navigation_label' => 'Ürün Kategorileri',
    'model_label' => 'Ürün Kategorisi',
    'plural_model_label' => 'Ürün Kategorileri',
    'fields' => [
        'name' => 'Kategori Adı',
        'available_parts' => 'Uygulanabilir Parçalar',
        'is_active' => 'Aktif',
        'created_at' => 'Oluşturulma',
        'updated_at' => 'Güncellenme',
    ],
    'sections' => [
        'kategori_bilgileri' => 'Kategori Bilgileri',
    ],
    'table' => [
        'columns' => [
            'name' => 'Kategori Adı',
            'available_parts' => 'Uygulanabilir Parçalar',
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

