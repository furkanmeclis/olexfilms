<?php

return [
    'navigation_label' => 'Siparişler',
    'model_label' => 'Sipariş',
    'plural_model_label' => 'Siparişler',
    'fields' => [
        'id' => 'Sipariş No',
        'dealer_id' => 'Bayi',
        'dealer' => 'Bayi',
        'status' => 'Durum',
        'items' => 'Ürünler',
        'product_id' => 'Ürün',
        'quantity' => 'Adet',
        'stock_items' => 'Stok Kodları',
        'cargo_company' => 'Kargo Firması',
        'tracking_number' => 'Takip Numarası',
        'notes' => 'Notlar',
        'creator' => 'Oluşturan',
        'created_at' => 'Oluşturulma Tarihi',
    ],
    'sections' => [
        'temel_bilgiler' => 'Temel Bilgiler',
        'urunler' => 'Ürünler',
        'kargo_bilgileri' => 'Kargo Bilgileri',
        'siparis_bilgileri' => 'Sipariş Bilgileri',
        'siparis_kalemleri' => 'Sipariş Kalemleri',
    ],
    'table' => [
        'columns' => [
            'id' => 'Sipariş No',
            'dealer' => 'Bayi',
            'status' => 'Durum',
            'items_sum_quantity' => 'Toplam Adet',
            'created_at' => 'Oluşturulma Tarihi',
            'creator' => 'Oluşturan',
        ],
        'filters' => [
            'status' => 'Durum',
            'dealer_id' => 'Bayi',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'deliver_order' => 'Teslim Et',
    ],
];

