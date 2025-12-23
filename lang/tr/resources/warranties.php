<?php

return [
    'navigation_label' => 'Garantiler',
    'model_label' => 'Garanti',
    'plural_model_label' => 'Garantiler',
    'fields' => [
        'service_id' => 'Hizmet No',
        'service' => 'Hizmet',
        'stock_item_id' => 'Stok Kalemi',
        'stockItem' => 'Stok Kalemi',
        'barcode' => 'Barkod',
        'product' => 'Ürün Adı',
        'start_date' => 'Başlangıç',
        'end_date' => 'Bitiş',
        'days_remaining' => 'Kalan Gün',
        'is_active' => 'Durum',
        'dealer' => 'Bayi',
    ],
    'sections' => [
        'garanti_bilgileri' => 'Garanti Bilgileri',
    ],
    'table' => [
        'columns' => [
            'service_no' => 'Hizmet No',
            'barcode' => 'Barkod',
            'product' => 'Ürün Adı',
            'start_date' => 'Başlangıç',
            'end_date' => 'Bitiş',
            'days_remaining' => 'Kalan Gün',
            'status' => 'Durum',
            'dealer' => 'Bayi',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'view_pdf' => 'PDF Görüntüle',
        'view_service' => 'Hizmet Görüntüle',
    ],
];
