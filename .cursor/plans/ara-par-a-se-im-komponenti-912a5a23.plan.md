<!-- 912a5a23-48ea-4eac-ac31-2dbb42457fe7 d73be6ac-c4af-429b-a240-f254021b8c2f -->
# Araç Parça Seçim Komponenti - Filament 4

## Genel Bakış

React tabanlı araç parça seçim komponenti Filament 4'e uygun olarak yeniden yapılacak. İki komponent oluşturulacak:

1. **CarPartPicker** - Form için editable komponent (Livewire + Blade)
2. **CarPartView** - View/Infolist için sadece görüntüleme komponenti

## Yapılacaklar

### 1. CarPartEnum Genişletme

- **Dosya:** `app/Enums/CarPartEnum.php`
- SVG'deki tüm parçaları enum'a ekle:
  - Window parçaları: `WINDOW_SUNROOF`, `WINDOW_ON_CAM`, `WINDOW_ARKA_CAM`, `WINDOW_SOL_ARKA_KAPI`, `WINDOW_SOL_ON_KAPI`, `WINDOW_SAG_ARKA_KAPI`, `WINDOW_SAG_ON_KAPI`
  - Mevcut body parçaları zaten var
  - `getLabels()` metodunu güncelle
  - Parça kategorileri için helper metodlar ekle (`isBody()`, `isWindow()` gibi)

### 2. GenerateCarSvg Servisi

- **Dosya:** `app/Services/GenerateCarSvg.php`
- Kullanıcının verdiği kod yapısını kullan
- SVG template'i `storage/olex_car_template.svg` dosyasından oku
- `fillCar()` metodu ile seçili parçaları renklendir
- Base64 veya dosya olarak döndür
- Parça renkleri: body için `#1a8f14`, window için `#3db5ff`

### 3. CarPartPicker Form Komponenti

- **Dosya:** `app/Filament/Forms/Components/CarPartPicker.php`
- `Filament\Forms\Components\Field` extend edecek
- **View:** `resources/views/filament/forms/components/car-part-picker.blade.php`
- Özellikler:
  - SVG araç görseli (editable, tıklanabilir)
  - Sağ tarafta tree view (GÖVDE ve CAMLAR kategorileri)
  - Checkbox seçimleri
  - Hover efektleri (body: yeşil, window: mavi)
  - Seçili parçaları state'te tut
  - Livewire reactive
- State: array of CarPartEnum values

### 4. CarPartView Infolist Komponenti

- **Dosya:** `app/Filament/Infolists/Components/CarPartView.php`
- `Filament\Infolists\Components\Entry` extend edecek
- **View:** `resources/views/filament/infolists/components/car-part-view.blade.php`
- Özellikler:
  - GenerateCarSvg kullanarak SVG oluştur
  - Sadece görüntüleme (editable değil)
  - Seçili parçaları liste olarak göster
  - Base64 encoded SVG göster

### 5. Blade View Dosyaları

- **Form View:** `resources/views/filament/forms/components/car-part-picker.blade.php`
  - SVG render (editable)
  - Tree view sidebar
  - Alpine.js ile interaktivite
  - Livewire entangle ile state yönetimi
- **Infolist View:** `resources/views/filament/infolists/components/car-part-view.blade.php`
  - GenerateCarSvg ile SVG render
  - Seçili parçalar listesi

### 6. JavaScript ve CSS

- **JavaScript:** `resources/js/filament/car-part-picker.js`
  - SVG path click handlers
  - Hover efektleri
  - Tree view ile SVG senkronizasyonu
- **CSS:** `resources/css/filament/car-part-picker.css`
  - SVG styling
  - Tree view styling
  - Responsive layout

### 7. Vite Config Güncelleme

- `vite.config.js` dosyasına yeni asset'leri ekle

## Teknik Detaylar

### Form Component Kullanımı

```php
use App\Filament\Forms\Components\CarPartPicker;

CarPartPicker::make('car_parts')
    ->label('Araç Parçaları')
    ->multiple()
    ->required()
```

### Infolist Component Kullanımı

```php
use App\Filament\Infolists\Components\CarPartView;

CarPartView::make('car_parts')
    ->label('Araç Parçaları')
```

### State Formatı

- Form state: `['body_tavan', 'body_kaput', 'window_on_cam']` (array of strings)
- Database: JSON column veya comma-separated string

## Dosya Yapısı

```
app/
  Enums/
    CarPartEnum.php (güncellenecek)
  Services/
    GenerateCarSvg.php (yeni)
  Filament/
    Forms/
      Components/
        CarPartPicker.php (yeni)
    Infolists/
      Components/
        CarPartView.php (yeni)

resources/
  views/
    filament/
      forms/
        components/
          car-part-picker.blade.php (yeni)
      infolists/
        components/
          car-part-view.blade.php (yeni)
  js/
    filament/
      car-part-picker.js (yeni)
  css/
    filament/
      car-part-picker.css (yeni)
```

## Notlar

- Filament 4 kurallarına uygun olacak
- Context7 MCP'den sürekli dokümantasyon kontrol edilecek
- Türkçe label'lar kullanılacak
- Responsive tasarım olacak
- SVG path'lerine `data-part-id` attribute'u eklenecek
- Tree view için Filament'in kendi component'leri veya custom implementation

### To-dos

- [ ] CarPartEnum'u SVG'ye göre genişlet (window parçaları ekle, helper metodlar ekle)
- [ ] GenerateCarSvg servis sınıfını oluştur (fillCar, convertBase64 metodları)
- [ ] CarPartPicker form komponenti sınıfını oluştur (Field extend)
- [ ] CarPartView infolist komponenti sınıfını oluştur (Entry extend)
- [ ] car-part-picker.blade.php view dosyasını oluştur (SVG + tree view)
- [ ] car-part-view.blade.php view dosyasını oluştur (GenerateCarSvg kullan)
- [ ] JavaScript dosyasını oluştur (SVG interaktivite, hover, click handlers)
- [ ] CSS dosyasını oluştur (styling, responsive)
- [ ] Vite config'i güncelle (yeni asset'leri ekle)