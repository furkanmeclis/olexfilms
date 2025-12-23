<?php

return [
    'navigation_label' => 'Ürünler',
    'model_label' => 'Ürün',
    'plural_model_label' => 'Ürünler',
    'fields' => [
        'name' => 'Ürün Adı',
        'sku' => 'Stok Kodu',
        'category_id' => 'Kategori',
        'category' => 'Kategori',
        'description' => 'Açıklama',
        'warranty_duration' => 'Garanti Süresi',
        'micron_thickness' => 'Mikron Kalınlığı',
        'price' => 'Fiyat',
        'image_path' => 'Ürün Görseli',
        'is_active' => 'Aktif',
        'created_at' => 'Oluşturulma',
        'updated_at' => 'Güncellenme',
    ],
    'sections' => [
        'temel_bilgiler' => 'Temel Bilgiler',
        'aciklama' => 'Açıklama',
        'fiyat_ve_garanti' => 'Fiyat ve Garanti',
        'gorsel' => 'Görsel',
        'zaman_bilgileri' => 'Zaman Bilgileri',
    ],
    'table' => [
        'columns' => [
            'name' => 'Ürün Adı',
            'sku' => 'Stok Kodu',
            'category' => 'Kategori',
            'price' => 'Fiyat',
            'is_active' => 'Aktif',
            'created_at' => 'Oluşturulma',
        ],
        'filters' => [
            'category_id' => 'Kategori',
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
