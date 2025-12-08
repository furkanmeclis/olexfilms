<!-- f1ddd83b-77e9-42e9-96a5-bf420fc669c4 476291a3-9fb2-4e2e-97a8-65e1f57ca2ee -->
# PDF Servisi ve Araç Parça Seçimi Komponenti

## Genel Bakış

Bu plan, geçmiş projeden PDF servisini uyarlayıp Filament 4 için araç parça seçimi komponenti oluşturmayı içerir. PDF servisi Service model'i için garanti sertifikası oluşturacak.

## Yapılacaklar

### 1. Composer Paket Kurulumu

- `barryvdh/laravel-dompdf` paketini `composer.json`'a ekle
- Paketi kur

### 2. PDF Servis Sınıfları

- `app/Services/GenerateCarSvg.php` oluştur
  - SVG template'i `storage/olex_car_template.svg`'den okuyacak
  - Seçilen parçaları renklendirecek (yeşil: #1a8f14)
  - Base64 formatında SVG döndürecek
- `app/Services/PdfService.php` oluştur
  - `GenerateCarSvg` servisini kullanacak
  - Logo'yu `public/logo.png`'den çekecek (fallback: storage'dan)
  - Service model'inden veri alıp PDF oluşturacak
  - Logo cache mekanizması ekle

### 3. Filament 4 Custom Form Component

- `app/Filament/Forms/Components/CarPartSelector.php` oluştur
  - `Filament\Forms\Components\Field` extend edecek
  - `CarPartEnum` kullanacak
  - SVG üzerinde tıklanabilir alanlar içerecek
  - Alpine.js ile interaktif olacak
  - Seçilen parçaları array olarak döndürecek
- `resources/views/filament/forms/components/car-part-selector.blade.php` view dosyası oluştur
  - SVG template'i gösterecek
  - Tıklanabilir path elementleri olacak
  - Seçilen parçalar yeşil renkte gösterilecek

### 4. PDF View Dosyası

- `resources/views/pdf/warranty.blade.php` oluştur
  - Eski PDF view'ını uyarlayacak
  - Service model'inden gelen verileri kullanacak
  - Logo'yu `public/logo.png`'den çekecek
  - CarBrand logo'sunu kullanacak
  - ServiceItem'ları tabloda gösterecek

### 5. ServiceForm Güncellemesi

- `app/Filament/Resources/Services/Schemas/ServiceForm.php` güncelle
  - `CheckboxList` yerine `CarPartSelector` kullan
  - Komponenti "Kaplama Alanları" bölümüne ekle

### 6. Service Resource'a PDF Action Ekle

- `app/Filament/Resources/Services/Pages/ViewService.php` güncelle
  - PDF indirme action'ı ekle
  - `PdfService` kullanarak PDF oluştur
  - Download response döndür

### 7. Dokümantasyon

- `docs/pdf-service.md` oluştur
  - PdfService kullanımı
  - GenerateCarSvg kullanımı
  - Service model'inden PDF oluşturma örnekleri
- `docs/car-part-selector.md` oluştur
  - CarPartSelector komponenti kullanımı
  - Form'larda nasıl kullanılacağı
  - Özelleştirme seçenekleri

## Dosya Yapısı

```
app/
  Services/
    GenerateCarSvg.php
    PdfService.php
  Filament/
    Forms/
      Components/
        CarPartSelector.php
resources/
  views/
    pdf/
      warranty.blade.php
    filament/
      forms/
        components/
          car-part-selector.blade.php
docs/
  pdf-service.md
  car-part-selector.md
```

## Önemli Notlar

- Logo her zaman `public/logo.png`'den çekilecek
- SVG template `storage/olex_car_template.svg`'de mevcut
- CarPartEnum sadece body parçalarını içeriyor (window parçaları yok)
- PDF servisi Service model'inin ilişkilerini kullanacak (carBrand, carModel, items, customer)
- Filament 4 custom component yapısı kullanılacak