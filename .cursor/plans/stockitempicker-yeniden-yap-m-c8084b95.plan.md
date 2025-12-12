---
name: StockItemPicker Yeniden Yapımı
overview: ""
todos: []
---

# StockItemPicker Yeniden Yapımı

## Mevcut Sorunlar

1. **View dosyasında aşırı debug log** - Production için uygun değil
2. **Wizard state erişimi karmaşık** - `../../` ve `../../../` ile deneme yanılma
3. **Reactive güncelleme yok** - `applied_parts` değiştiğinde otomatik güncellenmiyor
4. **Barkod/QR okutma yok** - ServiceNumberInput'taki gibi özellik eksik
5. **SQLite JSON sorgusu** - `like` ile yapılıyor, daha iyi yöntem gerekli
6. **Performance** - Her render'da query çalışıyor
7. **Modal state yönetimi** - Wizard adımları arasında geçişte sorun olabilir

## Çözüm Planı

### 1. PHP Component Güncellemesi (`app/Filament/Forms/Components/StockItemPicker.php`)

- `live()` modifier ekle - reactive güncelleme için
- `afterStateUpdated()` callback'leri düzenle
- Wizard state erişimi için daha güvenilir yöntem
- `get()` utility ile state erişimini optimize et
- Type hinting ve return type'ları iyileştir

### 2. View Dosyası Yeniden Yazımı (`resources/views/filament/forms/components/stock-item-picker.blade.php`)

- Tüm debug log'ları kaldır
- Alpine.js reactive state yönetimi
- Barkod/QR kod okutma özelliği ekle (ServiceNumberInput'tan ilham al)
- Modal açık/kapalı state yönetimi
- Wizard adımları arasında geçişte state korunması
- Loading state ekle
- Error handling iyileştir
- UI/UX iyileştirmeleri (animasyonlar, transitions)

### 3. Query Optimizasyonu

- `applied_parts` filtrelemesi için daha iyi SQL sorgusu
- SQLite JSON desteği için `json_extract` veya daha iyi yöntem
- Eager loading optimize et (`with()` kullanımı)
- Cache mekanizması ekle (opsiyonel)

### 4. ServiceForm Entegrasyonu (`app/Filament/Resources/Services/Schemas/ServiceForm.php`)

- `getStockStep()` metodunda `live()` modifier ekle
- State erişim yollarını sadeleştir
- Reactive güncelleme için `afterStateUpdated()` kullan

## Teknik Detaylar

### Barkod/QR Okutma

- Html5Qrcode kütüphanesi kullan (ServiceNumberInput'taki gibi)
- Barkod okutma için BarcodeDetector API
- Modal içinde kamera görüntüsü
- Okutulan barkod ile stok arama

### Reactive State Yönetimi

- `live()` modifier ile form state değişikliklerini dinle
- `$wire.watch()` ile `applied_parts` değişikliklerini izle
- Wizard adımları arasında state senkronizasyonu

### SQLite JSON Sorgusu

- `json_extract()` kullan (SQLite 3.38+)
- Fallback olarak `like` sorgusu
- Array intersection kontrolü

## Dosyalar

- `app/Filament/Forms/Components/StockItemPicker.php` - Component class
- `resources/views/filament/forms/components/stock-item-picker.blade.php` - View template
- `app/Filament/Resources/Services/Schemas/ServiceForm.php` - ServiceForm entegrasyonu

## Test Senaryoları

1. Wizard'da applied_parts seçildikten sonra stok listesinin güncellenmesi
2. Barkod/QR okutma ile stok seçimi
3. Wizard adımları arasında geçişte state korunması
4. Admin ve bayi kullanıcıları için dealer filtreleme
5. Boş applied_parts durumunda uygun mesaj gösterimi