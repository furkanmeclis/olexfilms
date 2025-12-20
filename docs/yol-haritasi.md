# Glorian v2.3 - Geliştirme Yol Haritası

Bu doküman, projenin geliştirilmesi gereken önemli özelliklerinin teknik karşılıklarını ve uygulama detaylarını içermektedir.

## 1. Garanti Sayfası

### Teknik Karşılık
**Dosya:** `app/Filament/Resources/Warranties/WarrantyResource.php`

### Mevcut Durum
- ✅ Warranty model mevcut (`app/Models/Warranty.php`)
- ✅ WarrantyResource mevcut ve çalışıyor
- ✅ ViewWarranty sayfası mevcut
- ✅ WarrantyInfolist şeması mevcut

### Geliştirilmesi Gerekenler

#### 1.1. Garanti Listesi Sayfası İyileştirmeleri
**Dosya:** `app/Filament/Resources/Warranties/Tables/WarrantiesTable.php`

**Yapılacaklar:**
- Garanti durumu filtreleri ekle (Aktif, Süresi Dolmuş, Yakında Bitecek)
- Garanti bitiş tarihine göre sıralama
- Kalan gün sayısı kolonu ekle
- Garanti süresi dolmak üzere olan garantiler için uyarı badge'i
- Service ve StockItem bilgilerini daha detaylı göster

**Teknik Detaylar:**
```php
// Filtreler
->filters([
    SelectFilter::make('is_active')
        ->label('Durum')
        ->options([
            '1' => 'Aktif',
            '0' => 'Pasif',
        ]),
    SelectFilter::make('expired')
        ->label('Süre Durumu')
        ->options([
            'expired' => 'Süresi Dolmuş',
            'expiring_soon' => 'Yakında Bitecek (30 gün)',
            'active' => 'Aktif',
        ]),
])

// Kalan gün kolonu
TextColumn::make('days_remaining')
    ->label('Kalan Gün')
    ->badge()
    ->color(fn ($state) => match (true) {
        $state === null => 'gray',
        $state <= 0 => 'danger',
        $state <= 30 => 'warning',
        default => 'success',
    })
```

#### 1.2. Garanti Detay Sayfası İyileştirmeleri
**Dosya:** `app/Filament/Resources/Warranties/Schemas/WarrantyInfolist.php`

**Yapılacaklar:**
- Garanti süresi görselleştirmesi (progress bar)
- Garanti geçmişi (warranty history) bölümü
- İlgili hizmet ve ürün bilgilerini daha detaylı göster
- Garanti uzatma işlemi için action butonu (opsiyonel)

**Teknik Detaylar:**
```php
// Progress bar için custom component veya formatStateUsing
TextEntry::make('warranty_progress')
    ->label('Garanti İlerlemesi')
    ->formatStateUsing(function ($record) {
        $startDate = $record->start_date;
        $endDate = $record->end_date;
        $now = now();
        
        if (!$startDate || !$endDate) {
            return 'Bilinmiyor';
        }
        
        $totalDays = $startDate->diffInDays($endDate);
        $elapsedDays = $startDate->diffInDays($now);
        $percentage = min(100, max(0, ($elapsedDays / $totalDays) * 100));
        
        return "{$percentage}% tamamlandı";
    })
```

#### 1.3. Garanti Widget'ı (Opsiyonel)
**Dosya:** `app/Filament/Widgets/WarrantyStatsWidget.php` (yeni)

**Yapılacaklar:**
- Dashboard'a garanti istatistikleri widget'ı
- Aktif garanti sayısı
- Süresi dolmak üzere olan garanti sayısı
- Süresi dolmuş garanti sayısı

---

## 2. Hizmet PDF'i

### Teknik Karşılık
**Dosya:** `app/Filament/Resources/Services/Pages/ViewService.php` (yeni action)
**PDF Generator:** `app/Services/ServicePdfGenerator.php` (yeni)

### Mevcut Durum
- ✅ Service model mevcut
- ✅ ServiceResource mevcut
- ✅ ViewService sayfası mevcut
- ❌ PDF export özelliği yok

### Geliştirilmesi Gerekenler

#### 2.1. PDF Generator Service Oluşturma
**Dosya:** `app/Services/ServicePdfGenerator.php` (yeni)

**Yapılacaklar:**
- Laravel PDF paketi entegrasyonu (barryvdh/laravel-dompdf veya spatie/laravel-pdf)
- Hizmet bilgilerini PDF formatına dönüştürme
- PDF template oluşturma (Blade view)
- PDF'de gösterilecek bilgiler:
  - Hizmet numarası
  - Müşteri bilgileri
  - Araç bilgileri
  - Kullanılan ürünler
  - Garanti bilgileri
  - Hizmet görselleri (opsiyonel)
  - Hizmet notları
  - Tarih bilgileri

**Teknik Detaylar:**
```php
// composer.json'a eklenecek paket
"barryvdh/laravel-dompdf": "^2.0"

// ServicePdfGenerator.php yapısı
class ServicePdfGenerator
{
    public function generate(Service $service): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'service' => $service->load([
                'customer',
                'carBrand',
                'carModel',
                'items.stockItem.product',
                'warranties.stockItem.product',
                'images',
            ]),
        ];
        
        return \PDF::loadView('pdfs.service', $data);
    }
}
```

#### 2.2. PDF Template Oluşturma
**Dosya:** `resources/views/pdfs/service.blade.php` (yeni)

**Yapılacaklar:**
- Profesyonel PDF tasarımı
- Logo ve başlık
- Hizmet bilgileri tabloları
- Garanti bilgileri bölümü
- Footer (sayfa numarası, tarih)

#### 2.3. ViewService Sayfasına PDF Export Action Ekleme
**Dosya:** `app/Filament/Resources/Services/Pages/ViewService.php`

**Yapılacaklar:**
- Header actions'a PDF indirme butonu ekle
- PDF oluşturma ve indirme işlevi

**Teknik Detaylar:**
```php
protected function getHeaderActions(): array
{
    return [
        Action::make('downloadPdf')
            ->label('PDF İndir')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function (Service $record) {
                $pdfGenerator = app(ServicePdfGenerator::class);
                $pdf = $pdfGenerator->generate($record);
                
                return $pdf->download("hizmet-{$record->service_no}.pdf");
            }),
        // ... diğer actions
    ];
}
```

#### 2.4. PDF İçeriği Detayları
**Gösterilecek Bölümler:**
1. **Başlık ve Logo**
   - Şirket logosu
   - Hizmet numarası
   - Tarih

2. **Müşteri Bilgileri**
   - Ad Soyad / Firma
   - Telefon
   - E-posta
   - Adres

3. **Araç Bilgileri**
   - Marka, Model, Yıl
   - Plaka
   - Şasi No
   - Kilometre

4. **Hizmet Detayları**
   - Paket bilgisi
   - Kaplama alanları (applied_parts)
   - Durum

5. **Kullanılan Ürünler**
   - Ürün adı
   - Barkod
   - Stok kodu
   - Kullanım tipi

6. **Garanti Bilgileri**
   - Ürün adı
   - Başlangıç tarihi
   - Bitiş tarihi
   - Kalan gün

7. **Notlar**
   - Hizmet notları

---

## 3. Müşteri Ekranı Düzeltmeleri

### Teknik Karşılık
**Dosya:** `app/Filament/Resources/Customers/CustomerResource.php`
**Frontend:** `resources/js/` (müşteri ekranı React/Inertia component'leri)

### Mevcut Durum
- ✅ Customer model mevcut
- ✅ CustomerResource mevcut
- ✅ ViewCustomer sayfası mevcut
- ✅ Customer model'de `getServices()` metodu mevcut (müşteri ekranı için)

### Geliştirilmesi Gerekenler

#### 3.1. Customer Infolist İyileştirmeleri
**Dosya:** `app/Filament/Resources/Customers/CustomerResource.php`

**Yapılacaklar:**
- Müşteri hizmetlerini gösteren bölüm ekle
- Müşteri istatistikleri (toplam hizmet sayısı, aktif garanti sayısı)
- Son hizmetler listesi
- Müşteri iletişim geçmişi (SMS logları)

**Teknik Detaylar:**
```php
// CustomerResource infolist'e eklenecek
Section::make('Hizmet İstatistikleri')
    ->schema([
        TextEntry::make('services_count')
            ->label('Toplam Hizmet')
            ->formatStateUsing(fn ($record) => $record->services()->count() . ' hizmet')
            ->badge()
            ->color('info'),
        
        TextEntry::make('active_warranties_count')
            ->label('Aktif Garanti')
            ->formatStateUsing(function ($record) {
                $count = 0;
                foreach ($record->services as $service) {
                    $count += $service->warranties()
                        ->where('is_active', true)
                        ->where('end_date', '>=', now())
                        ->count();
                }
                return "{$count} aktif garanti";
            })
            ->badge()
            ->color('success'),
    ])
    ->columns(2),

Section::make('Son Hizmetler')
    ->schema([
        RepeatableEntry::make('services')
            ->label('')
            ->schema([
                TextEntry::make('service_no')
                    ->label('Hizmet No'),
                TextEntry::make('carBrand.name')
                    ->label('Marka'),
                TextEntry::make('status')
                    ->label('Durum')
                    ->badge(),
                TextEntry::make('created_at')
                    ->label('Tarih')
                    ->date('d.m.Y'),
            ])
            ->columns(4)
            ->limit(5),
    ])
    ->collapsible(),
```

#### 3.2. Müşteri Ekranı (Frontend) Düzeltmeleri
**Dosyalar:** 
- `resources/js/Pages/Customer/CustomerPage.tsx` (varsa)
- `app/Http/Controllers/CustomerController.php` (varsa)
- `routes/web.php` (müşteri ekranı route'u)

**Yapılacaklar:**
- Müşteri bilgileri görüntüleme
- Hizmet listesi görüntüleme
- Garanti bilgileri görüntüleme
- Garanti süresi progress bar'ı
- Responsive tasarım iyileştirmeleri
- Hata yönetimi

**Teknik Detaylar:**
```php
// Route tanımı (web.php)
Route::get('/musteri/{customer}', [CustomerController::class, 'show'])
    ->name('customer.show');

// Controller
public function show(Customer $customer)
{
    $customer->load([
        'services.carBrand',
        'services.carModel',
        'services.warranties.stockItem.product',
    ]);
    
    return Inertia::render('Customer/CustomerPage', [
        'customer' => $customer,
        'services' => $customer->getServices(),
    ]);
}
```

#### 3.3. Müşteri Arama ve Filtreleme İyileştirmeleri
**Dosya:** `app/Filament/Resources/Customers/Tables/CustomersTable.php`

**Yapılacaklar:**
- Telefon numarasına göre arama iyileştirmesi
- Hizmet sayısına göre filtreleme
- Son hizmet tarihine göre sıralama

---

## 4. Hizmet Stok Kodu Seçimi

### Teknik Karşılık
**Dosya:** `app/Filament/Resources/Services/Schemas/ServiceForm.php`
**Dosya:** `app/Filament/Resources/Services/RelationManagers/ServiceItemsRelationManager.php`

### Mevcut Durum
- ✅ ServiceItem model mevcut
- ✅ ServiceItemsRelationManager mevcut
- ✅ ServiceForm'da stok seçimi yapılıyor
- ⚠️ Stok seçimi iyileştirilmesi gerekiyor

### Geliştirilmesi Gerekenler

#### 4.1. ServiceForm'da Stok Seçimi İyileştirmesi
**Dosya:** `app/Filament/Resources/Services/Schemas/ServiceForm.php`

**Yapılacaklar:**
- Stok seçiminde sadece müsait stokları göster
- Bayi bazlı filtreleme (bayi kullanıcıları sadece kendi stoklarını görsün)
- Stok arama iyileştirmesi (barkod, SKU, ürün adı)
- Stok durumu bilgisi göster (available, reserved, used)
- Stok seçiminde ürün bilgilerini daha detaylı göster

**Teknik Detaylar:**
```php
// ServiceForm'da stok seçimi
Select::make('stock_item_id')
    ->label('Stok Kodu / Barkod')
    ->relationship(
        'stockItem',
        'barcode',
        fn (Builder $query) => $query
            ->where('status', StockStatusEnum::AVAILABLE->value)
            ->where('location', StockLocationEnum::DEALER->value)
            ->when(
                auth()->user()?->dealer_id && !auth()->user()?->hasAnyRole(['super_admin', 'center_staff']),
                fn ($q) => $q->where('dealer_id', auth()->user()->dealer_id)
            )
    )
    ->searchable(['barcode', 'sku', 'product.name'])
    ->preload()
    ->required()
    ->getOptionLabelFromRecordUsing(fn (StockItem $record) => 
        "{$record->barcode} - {$record->product->name} ({$record->sku})"
    )
    ->helperText('Sadece müsait stoklar gösterilir')
```

#### 4.2. ServiceItemsRelationManager İyileştirmesi
**Dosya:** `app/Filament/Resources/Services/RelationManagers/ServiceItemsRelationManager.php`

**Yapılacaklar:**
- Stok seçiminde daha iyi arama
- Stok durumu bilgisi
- Kullanılan stokların listelenmesi
- Stok silme işlemi (opsiyonel, sadece draft hizmetler için)

**Teknik Detaylar:**
```php
// RelationManager form'da
Select::make('stock_item_id')
    ->label('Stok Seç')
    ->relationship(
        'stockItem',
        'barcode',
        fn (Builder $query) => {
            $service = $this->getOwnerRecord();
            $query->where('status', StockStatusEnum::AVAILABLE->value);
            
            // Bayi kullanıcıları sadece kendi stoklarını görsün
            if (auth()->user()?->dealer_id && !auth()->user()?->hasAnyRole(['super_admin', 'center_staff'])) {
                $query->where('dealer_id', auth()->user()->dealer_id);
            }
            
            // Merkez stokları da göster
            $query->orWhere(function ($q) {
                $q->where('location', StockLocationEnum::CENTER->value)
                  ->where('status', StockStatusEnum::AVAILABLE->value);
            });
        }
    )
    ->searchable(['barcode', 'sku', 'product.name'])
    ->preload()
    ->required()
    ->getOptionLabelFromRecordUsing(fn (StockItem $record) => 
        "{$record->barcode} - {$record->product->name} (SKU: {$record->sku})"
    )
    ->helperText('Barkod, SKU veya ürün adı ile arayabilirsiniz')
```

#### 4.3. Stok Seçiminde Validasyon
**Yapılacaklar:**
- Aynı stok kodunun iki kez seçilmesini engelle
- Seçilen stokun müsait olduğunu kontrol et
- Hizmet durumu değiştiğinde stok durumunu güncelle (ServiceObserver)

**Teknik Detaylar:**
```php
// ServiceForm validation rules
->rules([
    'items.*.stock_item_id' => [
        'required',
        'exists:stock_items,id',
        function ($attribute, $value, $fail) {
            $stockItem = StockItem::find($value);
            if ($stockItem && $stockItem->status !== StockStatusEnum::AVAILABLE->value) {
                $fail('Seçilen stok müsait değil.');
            }
        },
    ],
])
```

#### 4.4. Stok Kullanımı Sonrası Durum Güncelleme
**Dosya:** `app/Observers/ServiceObserver.php` (varsa)

**Yapılacaklar:**
- Hizmet tamamlandığında kullanılan stokların durumunu "used" yap
- Stok konumunu "service" yap
- StockMovement logu oluştur

**Teknik Detaylar:**
```php
// ServiceObserver
public function updated(Service $service): void
{
    if ($service->status === ServiceStatusEnum::COMPLETED->value) {
        foreach ($service->items as $item) {
            $stockItem = $item->stockItem;
            $stockItem->update([
                'status' => StockStatusEnum::USED->value,
                'location' => StockLocationEnum::SERVICE->value,
            ]);
            
            StockMovement::create([
                'stock_item_id' => $stockItem->id,
                'user_id' => auth()->id(),
                'action' => StockMovementActionEnum::USED_IN_SERVICE->value,
                'description' => "Hizmet #{$service->service_no} için kullanıldı",
            ]);
        }
    }
}
```

---

## 5. Stok Envanteri Excel Import Kısımları

### Teknik Karşılık
**Dosya:** `app/Imports/StockItemImport.php`
**Dosya:** `app/Filament/Resources/StockItems/Pages/ListStockItems.php`

### Mevcut Durum
- ✅ StockItemImport class mevcut
- ✅ Excel import action mevcut
- ✅ Hızlı stok girişi action mevcut
- ⚠️ Import işlemi iyileştirilmesi gerekiyor

### Geliştirilmesi Gerekenler

#### 5.1. Excel Import Validasyon İyileştirmeleri
**Dosya:** `app/Imports/StockItemImport.php`

**Yapılacaklar:**
- Excel header validasyonu iyileştirme
- Hata mesajlarını daha anlaşılır hale getirme
- Import öncesi önizleme (opsiyonel)
- Toplu hata raporlama
- Duplicate barcode kontrolü

**Teknik Detaylar:**
```php
// beforeCollection metodunda iyileştirme
protected function beforeCollection(Collection $collection): void
{
    // Header kontrolü
    if ($collection->isEmpty()) {
        $this->stopImportWithError('Excel dosyası boş veya geçersiz format.');
        return;
    }
    
    $firstRow = $collection->first();
    $requiredHeaders = ['ÜRÜN KODU', 'BAYİ KODU'];
    $headers = array_keys($firstRow);
    
    $missingHeaders = array_diff($requiredHeaders, $headers);
    if (!empty($missingHeaders)) {
        $this->stopImportWithError(
            'Eksik kolonlar: ' . implode(', ', $missingHeaders)
        );
        return;
    }
    
    // Duplicate barcode kontrolü
    $barcodes = $collection->pluck('ÜRÜN KODU')->filter();
    $duplicates = $barcodes->duplicates();
    if ($duplicates->isNotEmpty()) {
        $this->stopImportWithWarning(
            'Excel içinde tekrar eden barkodlar bulundu: ' . 
            $duplicates->unique()->implode(', ')
        );
    }
}
```

#### 5.2. Import Sonrası Raporlama İyileştirmesi
**Dosya:** `app/Imports/StockItemImport.php`

**Yapılacaklar:**
- Detaylı import raporu
- Başarılı, başarısız, atlanan satır sayıları
- Hata detayları (hangi satır, neden atlandı)
- Import log dosyası oluşturma (opsiyonel)

**Teknik Detaylar:**
```php
// afterCollection metodunda iyileştirme
protected function afterCollection(Collection $collection): void
{
    // Import raporu oluştur
    $report = [
        'total_rows' => $collection->count(),
        'success' => $this->successCount,
        'created' => $this->createdCount,
        'updated' => $this->updatedCount,
        'skipped' => count($this->skippedRows),
        'errors' => $this->skippedRows,
    ];
    
    // Log dosyasına kaydet (opsiyonel)
    if (count($this->skippedRows) > 0) {
        Log::info('Stock Import Report', $report);
    }
    
    // Mesaj oluştur
    $message = sprintf(
        'Import tamamlandı. Toplam: %d, Başarılı: %d (Oluşturulan: %d, Güncellenen: %d), Atlanan: %d',
        $report['total_rows'],
        $report['success'],
        $report['created'],
        $report['updated'],
        $report['skipped']
    );
    
    if ($report['skipped'] > 0) {
        $this->stopImportWithWarning($message);
    } else {
        $this->stopImportWithSuccess($message);
    }
}
```

#### 5.3. Excel Template Oluşturma
**Dosya:** `public/templates/stock-import-template.xlsx` (yeni)

**Yapılacaklar:**
- Örnek Excel template dosyası oluştur
- Template indirme butonu ekle (ListStockItems sayfasına)
- Template'te örnek veriler ve açıklamalar

**Teknik Detaylar:**
```php
// ListStockItems sayfasında template indirme action
Action::make('downloadTemplate')
    ->label('Template İndir')
    ->icon('heroicon-o-arrow-down-tray')
    ->color('info')
    ->url(asset('templates/stock-import-template.xlsx'))
    ->openUrlInNewTab()
```

#### 5.4. Import İşlemi Performans İyileştirmesi
**Dosya:** `app/Imports/StockItemImport.php`

**Yapılacaklar:**
- Büyük dosyalar için chunk işleme
- Database transaction optimizasyonu
- Bulk insert kullanımı (mümkünse)
- Memory kullanımı optimizasyonu

**Teknik Detaylar:**
```php
// Chunk işleme için
public function chunkSize(): int
{
    return 100; // Her seferde 100 satır işle
}

// Bulk insert için (çok sayıda yeni kayıt varsa)
protected function bulkInsert(array $items): void
{
    if (count($items) > 50) {
        StockItem::insert($items);
    } else {
        foreach ($items as $item) {
            StockItem::create($item);
        }
    }
}
```

#### 5.5. Import Öncesi Önizleme (Opsiyonel)
**Dosya:** `app/Filament/Resources/StockItems/Pages/ListStockItems.php`

**Yapılacaklar:**
- Excel dosyası yüklendikten sonra önizleme göster
- Kullanıcı onayı al
- Önizlemede hata olan satırları işaretle

#### 5.6. Import İşlemi Queue'ya Alma (Opsiyonel)
**Dosya:** `app/Jobs/ImportStockItemsJob.php` (yeni)

**Yapılacaklar:**
- Büyük dosyalar için queue job oluştur
- Background'da import işlemi
- Import tamamlandığında bildirim

**Teknik Detaylar:**
```php
// Job oluşturma
php artisan make:job ImportStockItemsJob

// Job içeriği
class ImportStockItemsJob implements ShouldQueue
{
    public function handle(): void
    {
        // Import işlemi
        Excel::import(new StockItemImport, $this->filePath);
        
        // Bildirim gönder
        // ...
    }
}
```

---

## Genel Notlar

### Öncelik Sırası
1. **Hizmet Stok Kodu Seçimi** - En kritik, günlük kullanımda
2. **Müşteri Ekranı Düzeltmeleri** - Kullanıcı deneyimi için önemli
3. **Garanti Sayfası** - İyileştirme gerektiriyor
4. **Hizmet PDF'i** - Raporlama için önemli
5. **Stok Envanteri Excel Import** - İyileştirme gerektiriyor

### Teknik Gereksinimler
- Laravel 12
- Filament 4
- PHP 8.2+
- Composer paketleri:
  - `barryvdh/laravel-dompdf` (PDF için)
  - `maatwebsite/excel` (zaten mevcut)

### Test Edilmesi Gerekenler
- Her özellik için unit test
- Integration test
- Manual test (Filament panelinde)
- Performance test (büyük veri setleri için)

### Dokümantasyon
- Her yeni özellik için kod içi dokümantasyon
- Kullanıcı kılavuzu güncellemesi (opsiyonel)
- API dokümantasyonu (varsa)

---

## Sonuç

Bu yol haritası, projenin geliştirilmesi gereken önemli özelliklerini teknik detaylarıyla birlikte içermektedir. Her madde için:
- Mevcut durum analizi
- Yapılacaklar listesi
- Teknik detaylar ve kod örnekleri
- Dosya konumları

belirtilmiştir. Geliştirme sırasında bu dokümana referans olarak başvurulabilir.

