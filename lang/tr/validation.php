<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute alanı kabul edilmelidir.',
    'accepted_if' => ':other alanı :value olduğunda :attribute alanı kabul edilmelidir.',
    'active_url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'after' => ':attribute alanı :date tarihinden sonra olmalıdır.',
    'after_or_equal' => ':attribute alanı :date tarihinden sonra veya eşit olmalıdır.',
    'alpha' => ':attribute alanı sadece harf içermelidir.',
    'alpha_dash' => ':attribute alanı sadece harf, rakam, tire ve alt çizgi içerebilir.',
    'alpha_num' => ':attribute alanı sadece harf ve rakam içermelidir.',
    'any_of' => ':attribute alanı geçersiz.',
    'array' => ':attribute alanı bir dizi olmalıdır.',
    'ascii' => ':attribute alanı sadece tek byte alfanumerik karakterler ve semboller içermelidir.',
    'before' => ':attribute alanı :date tarihinden önce olmalıdır.',
    'before_or_equal' => ':attribute alanı :date tarihinden önce veya eşit olmalıdır.',
    'between' => [
        'array' => ':attribute alanı :min ile :max arasında öğe içermelidir.',
        'file' => ':attribute alanı :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute alanı :min ile :max arasında olmalıdır.',
        'string' => ':attribute alanı :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute alanı true veya false olmalıdır.',
    'can' => ':attribute alanı yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute alanı onayı eşleşmiyor.',
    'contains' => ':attribute alanı gerekli bir değer eksik.',
    'current_password' => 'Şifre yanlış.',
    'date' => ':attribute alanı geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute alanı :date tarihine eşit olmalıdır.',
    'date_format' => ':attribute alanı :format formatına uymalıdır.',
    'decimal' => ':attribute alanı :decimal ondalık basamak içermelidir.',
    'declined' => ':attribute alanı reddedilmelidir.',
    'declined_if' => ':other alanı :value olduğunda :attribute alanı reddedilmelidir.',
    'different' => ':attribute alanı ve :other farklı olmalıdır.',
    'digits' => ':attribute alanı :digits basamak olmalıdır.',
    'digits_between' => ':attribute alanı :min ile :max basamak arasında olmalıdır.',
    'dimensions' => ':attribute alanı geçersiz görüntü boyutlarına sahip.',
    'distinct' => ':attribute alanı tekrarlanan bir değere sahip.',
    'doesnt_contain' => ':attribute alanı şunlardan herhangi birini içermemelidir: :values.',
    'doesnt_end_with' => ':attribute alanı şunlardan biriyle bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute alanı şunlardan biriyle başlamamalıdır: :values.',
    'email' => ':attribute alanı geçerli bir e-posta adresi olmalıdır.',
    'encoding' => ':attribute alanı :encoding kodlamasında olmalıdır.',
    'ends_with' => ':attribute alanı şunlardan biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'extensions' => ':attribute alanı şu uzantılardan birine sahip olmalıdır: :values.',
    'file' => ':attribute alanı bir dosya olmalıdır.',
    'filled' => ':attribute alanı bir değere sahip olmalıdır.',
    'gt' => [
        'array' => ':attribute alanı :value öğeden fazla içermelidir.',
        'file' => ':attribute alanı :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden büyük olmalıdır.',
        'string' => ':attribute alanı :value karakterden uzun olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute alanı :value veya daha fazla öğe içermelidir.',
        'file' => ':attribute alanı :value kilobayt veya daha büyük olmalıdır.',
        'numeric' => ':attribute alanı :value veya daha büyük olmalıdır.',
        'string' => ':attribute alanı :value veya daha fazla karakter içermelidir.',
    ],
    'hex_color' => ':attribute alanı geçerli bir onaltılık renk olmalıdır.',
    'image' => ':attribute alanı bir resim olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute alanı :other içinde bulunmalıdır.',
    'in_array_keys' => ':attribute alanı şu anahtarlardan en az birini içermelidir: :values.',
    'integer' => ':attribute alanı bir tam sayı olmalıdır.',
    'ip' => ':attribute alanı geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute alanı geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute alanı geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute alanı geçerli bir JSON string olmalıdır.',
    'list' => ':attribute alanı bir liste olmalıdır.',
    'lowercase' => ':attribute alanı küçük harf olmalıdır.',
    'lt' => [
        'array' => ':attribute alanı :value öğeden az içermelidir.',
        'file' => ':attribute alanı :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden küçük olmalıdır.',
        'string' => ':attribute alanı :value karakterden kısa olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute alanı :value öğeden fazla içermemelidir.',
        'file' => ':attribute alanı :value kilobayt veya daha küçük olmalıdır.',
        'numeric' => ':attribute alanı :value veya daha küçük olmalıdır.',
        'string' => ':attribute alanı :value veya daha az karakter içermelidir.',
    ],
    'mac_address' => ':attribute alanı geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute alanı :max öğeden fazla içermemelidir.',
        'file' => ':attribute alanı :max kilobayttan büyük olmamalıdır.',
        'numeric' => ':attribute alanı :max değerinden büyük olmamalıdır.',
        'string' => ':attribute alanı :max karakterden uzun olmamalıdır.',
    ],
    'max_digits' => ':attribute alanı :max basamaktan fazla olmamalıdır.',
    'mimes' => ':attribute alanı şu türde bir dosya olmalıdır: :values.',
    'mimetypes' => ':attribute alanı şu türde bir dosya olmalıdır: :values.',
    'min' => [
        'array' => ':attribute alanı en az :min öğe içermelidir.',
        'file' => ':attribute alanı en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute alanı en az :min olmalıdır.',
        'string' => ':attribute alanı en az :min karakter içermelidir.',
    ],
    'min_digits' => ':attribute alanı en az :min basamak içermelidir.',
    'missing' => ':attribute alanı eksik olmalıdır.',
    'missing_if' => ':other alanı :value olduğunda :attribute alanı eksik olmalıdır.',
    'missing_unless' => ':other alanı :value olmadığı sürece :attribute alanı eksik olmalıdır.',
    'missing_with' => ':values mevcut olduğunda :attribute alanı eksik olmalıdır.',
    'missing_with_all' => ':values mevcut olduğunda :attribute alanı eksik olmalıdır.',
    'multiple_of' => ':attribute alanı :value katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute alanı formatı geçersiz.',
    'numeric' => ':attribute alanı bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute alanı en az bir harf içermelidir.',
        'mixed' => ':attribute alanı en az bir büyük harf ve bir küçük harf içermelidir.',
        'numbers' => ':attribute alanı en az bir rakam içermelidir.',
        'symbols' => ':attribute alanı en az bir sembol içermelidir.',
        'uncompromised' => 'Verilen :attribute bir veri sızıntısında göründü. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute alanı mevcut olmalıdır.',
    'present_if' => ':other alanı :value olduğunda :attribute alanı mevcut olmalıdır.',
    'present_unless' => ':other alanı :value olmadığı sürece :attribute alanı mevcut olmalıdır.',
    'present_with' => ':values mevcut olduğunda :attribute alanı mevcut olmalıdır.',
    'present_with_all' => ':values mevcut olduğunda :attribute alanı mevcut olmalıdır.',
    'prohibited' => ':attribute alanı yasaklanmıştır.',
    'prohibited_if' => ':other alanı :value olduğunda :attribute alanı yasaklanmıştır.',
    'prohibited_if_accepted' => ':other alanı kabul edildiğinde :attribute alanı yasaklanmıştır.',
    'prohibited_if_declined' => ':other alanı reddedildiğinde :attribute alanı yasaklanmıştır.',
    'prohibited_unless' => ':other alanı :values içinde olmadığı sürece :attribute alanı yasaklanmıştır.',
    'prohibits' => ':attribute alanı :other alanının mevcut olmasını yasaklar.',
    'regex' => ':attribute alanı formatı geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_array_keys' => ':attribute alanı şu girişleri içermelidir: :values.',
    'required_if' => ':other alanı :value olduğunda :attribute alanı zorunludur.',
    'required_if_accepted' => ':other alanı kabul edildiğinde :attribute alanı zorunludur.',
    'required_if_declined' => ':other alanı reddedildiğinde :attribute alanı zorunludur.',
    'required_unless' => ':other alanı :values içinde olmadığı sürece :attribute alanı zorunludur.',
    'required_with' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_with_all' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_without' => ':values mevcut olmadığında :attribute alanı zorunludur.',
    'required_without_all' => ':values hiçbiri mevcut olmadığında :attribute alanı zorunludur.',
    'same' => ':attribute alanı :other ile eşleşmelidir.',
    'size' => [
        'array' => ':attribute alanı :size öğe içermelidir.',
        'file' => ':attribute alanı :size kilobayt olmalıdır.',
        'numeric' => ':attribute alanı :size olmalıdır.',
        'string' => ':attribute alanı :size karakter içermelidir.',
    ],
    'starts_with' => ':attribute alanı şunlardan biriyle başlamalıdır: :values.',
    'string' => ':attribute alanı bir string olmalıdır.',
    'timezone' => ':attribute alanı geçerli bir saat dilimi olmalıdır.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ':attribute yüklenemedi.',
    'uppercase' => ':attribute alanı büyük harf olmalıdır.',
    'url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'ulid' => ':attribute alanı geçerli bir ULID olmalıdır.',
    'uuid' => ':attribute alanı geçerli bir UUID olmalıdır.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        // Genel
        'ÜRÜN KODU' => 'Ürün Kodu',
        'BAYİ' => 'Bayi',
        'ÜRÜN' => 'Ürün',
        'BAYİ KODU' => 'Bayi Kodu',
        'ÜRÜN SKU' => 'Ürün SKU',
        'TARİH' => 'Tarih',
        'KATEGORİ' => 'Kategori',
        'MARKA' => 'Marka',
        
        // Product
        'category_id' => 'Kategori',
        'name' => 'Ad',
        'sku' => 'Stok Kodu',
        'description' => 'Açıklama',
        'warranty_duration' => 'Garanti Süresi',
        'micron_thickness' => 'Mikron Kalınlığı',
        'price' => 'Fiyat',
        'image_path' => 'Görsel',
        'is_active' => 'Aktif',
        
        // Dealer
        'dealer_code' => 'Bayi Kodu',
        'email' => 'E-posta',
        'phone' => 'Telefon',
        'address' => 'Adres',
        'logo_path' => 'Logo',
        'facebook_url' => 'Facebook',
        'instagram_url' => 'Instagram',
        'twitter_url' => 'Twitter/X',
        'linkedin_url' => 'LinkedIn',
        'website_url' => 'Web Sitesi',
        'city' => 'İl',
        'district' => 'İlçe',
        
        // User
        'password' => 'Şifre',
        'dealer_id' => 'Bayi',
        'avatar_path' => 'Avatar',
        'email_verified_at' => 'E-posta Doğrulandı',
        
        // Service
        'service_no' => 'Hizmet Numarası',
        'customer_id' => 'Müşteri',
        'user_id' => 'Kullanıcı',
        'car_brand_id' => 'Marka',
        'car_model_id' => 'Model',
        'year' => 'Yıl',
        'vin' => 'Şasi No',
        'plate' => 'Plaka',
        'km' => 'Kilometre',
        'package' => 'Paket',
        'applied_parts' => 'Uygulanan Parçalar',
        'notes' => 'Notlar',
        'status' => 'Durum',
        'completed_at' => 'Tamamlanma Tarihi',
        
        // Order
        'created_by' => 'Oluşturan',
        'cargo_company' => 'Kargo Firması',
        'tracking_number' => 'Takip Numarası',
        
        // Customer
        'type' => 'Tip',
        'tc_no' => 'TC Kimlik No',
        'tax_no' => 'Vergi No',
        'tax_office' => 'Vergi Dairesi',
        'fcm_token' => 'FCM Token',
        'notification_settings' => 'Bildirim Ayarları',
        
        // CarBrand
        'external_id' => 'Dış ID',
        'logo' => 'Logo',
        'last_update' => 'Son Güncelleme',
        
        // CarModel
        'brand_id' => 'Marka',
        'powertrain' => 'Güç Aktarımı',
        'yearstart' => 'Başlangıç Yılı',
        'yearstop' => 'Bitiş Yılı',
        'coupe' => 'Gövde Tipi',
        
        // StockItem
        'product_id' => 'Ürün',
        'barcode' => 'Barkod',
        'location' => 'Konum',
        
        // Warranty
        'service_id' => 'Hizmet',
        'stock_item_id' => 'Stok Kalemi',
        'start_date' => 'Başlangıç Tarihi',
        'end_date' => 'Bitiş Tarihi',
        
        // ProductCategory
        'available_parts' => 'Uygulanabilir Parçalar',
        
        // OrderItem
        'order_id' => 'Sipariş',
        'quantity' => 'Adet',
        
        // ServiceItem
        'usage_type' => 'Kullanım Tipi',
        
        // ServiceImage
        'title' => 'Başlık',
        'order' => 'Sıra',
        
        // ServiceStatusLog
        'from_dealer_id' => 'Uygulayan Bayi',
        'to_dealer_id' => 'Gidilen Şube',
        
        // StockMovement
        'action' => 'Aksiyon',
        
        // BulkSms
        'message' => 'Mesaj',
        'sender' => 'Gönderici',
        'message_type' => 'Mesaj Tipi',
        'message_content_type' => 'Mesaj İçerik Tipi',
        'target_type' => 'Hedef Tip',
        'target_ids' => 'Hedef ID\'ler',
        'total_recipients' => 'Toplam Alıcı',
        'sent_count' => 'Gönderilen',
        'failed_count' => 'Başarısız',
        'sent_at' => 'Gönderilme Tarihi',
        'completed_at' => 'Tamamlanma Tarihi',
        
        // SmsLog
        'response_id' => 'Yanıt ID',
        'amount' => 'Tutar',
        'number_count' => 'Numara Sayısı',
        'response_data' => 'Yanıt Verisi',
        'invalid_phones' => 'Geçersiz Telefonlar',
        'notifiable_type' => 'Bildirilebilir Tip',
        'notifiable_id' => 'Bildirilebilir ID',
        'bulk_sms_id' => 'Toplu SMS',
        'sent_by' => 'Gönderen',
        
        // NexptgApiUser
        'username' => 'Kullanıcı Adı',
        'last_used_at' => 'Son Kullanım',
        
        // NexptgApiUserLog
        'nexptg_api_user_id' => 'API Kullanıcı',
        'status_code' => 'Durum Kodu',
        'details' => 'Detaylar',
        
        // NexptgReport
        'api_user_id' => 'API Kullanıcı',
        'date' => 'Tarih',
        'calibration_date' => 'Kalibrasyon Tarihi',
        'device_serial_number' => 'Cihaz Seri No',
        'model' => 'Model',
        'brand' => 'Marka',
        'type_of_body' => 'Gövde Tipi',
        'capacity' => 'Kapasite',
        'power' => 'Güç',
        'fuel_type' => 'Yakıt Tipi',
        'unit_of_measure' => 'Ölçü Birimi',
        'extra_fields' => 'Ekstra Alanlar',
        'comment' => 'Yorum',
        
        // NexptgReportMeasurement
        'report_id' => 'Rapor',
        'is_inside' => 'İç',
        'place_id' => 'Yer ID',
        'part_type' => 'Parça Tipi',
        'value' => 'Değer',
        'interpretation' => 'Yorumlama',
        'substrate_type' => 'Alt Tabaka Tipi',
        'timestamp' => 'Zaman Damgası',
        'position' => 'Pozisyon',
        
        // NexptgReportTire
        'width' => 'Genişlik',
        'profile' => 'Profil',
        'diameter' => 'Çap',
        'maker' => 'Üretici',
        'season' => 'Mevsim',
        'section' => 'Bölüm',
        'value1' => 'Değer 1',
        'value2' => 'Değer 2',
        
        // NexptgHistory
        'history_id' => 'Geçmiş',
        
        // NexptgHistoryMeasurement
    ],

];

