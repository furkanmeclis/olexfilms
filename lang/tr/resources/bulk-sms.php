<?php

return [
    'navigation_label' => 'Toplu SMS',
    'model_label' => 'Toplu SMS',
    'plural_model_label' => 'Toplu SMS',
    'fields' => [
        'message' => 'Mesaj',
        'recipients' => 'Alıcılar',
        'sent_at' => 'Gönderilme Tarihi',
        'created_at' => 'Oluşturulma',
    ],
    'sections' => [
        'temel_bilgiler' => 'Temel Bilgiler',
    ],
    'table' => [
        'columns' => [
            'message' => 'Mesaj',
            'recipients' => 'Alıcılar',
            'sent_at' => 'Gönderilme Tarihi',
            'created_at' => 'Oluşturulma',
        ],
    ],
    'actions' => [
        'view' => 'Görüntüle',
        'create' => 'Oluştur',
    ],
];
