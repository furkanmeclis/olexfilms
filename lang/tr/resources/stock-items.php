<?php

return [
    'navigation_label' => 'Stok Envanteri',
    'model_label' => 'Stok Kalemi',
    'plural_model_label' => 'Stok Kalemleri',
    'fields' => [
        'barcode' => 'Barkod',
        'product_id' => 'Ürün',
        'product' => 'Ürün Adı',
        'sku' => 'Stok Kodu',
        'location' => 'Konum',
        'status' => 'Durum',
        'dealer_id' => 'Bayi',
        'dealer' => 'Bayi',
        'created_at' => 'Oluşturulma',
    ],
    'sections' => [
        'temel_bilgiler' => 'Temel Bilgiler',
    ],
    'table' => [
        'columns' => [
            'barcode' => 'Barkod',
            'product' => 'Ürün',
            'sku' => 'Stok Kodu',
            'location' => 'Konum',
            'status' => 'Durum',
            'dealer' => 'Bayi',
            'created_at' => 'Oluşturulma',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
    ],
];

