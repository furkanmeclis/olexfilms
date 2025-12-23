<?php

return [
    'navigation_label' => 'SMS Logları',
    'model_label' => 'SMS Logu',
    'plural_model_label' => 'SMS Logları',
    'fields' => [
        'phone' => 'Telefon',
        'message' => 'Mesaj',
        'status' => 'Durum',
        'sent_at' => 'Gönderilme Tarihi',
        'created_at' => 'Oluşturulma',
    ],
    'sections' => [
        'log_bilgileri' => 'Log Bilgileri',
    ],
    'table' => [
        'columns' => [
            'phone' => 'Telefon',
            'message' => 'Mesaj',
            'status' => 'Durum',
            'sent_at' => 'Gönderilme Tarihi',
            'created_at' => 'Oluşturulma',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
    ],
];
