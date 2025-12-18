# Araç Parça Seçim Komponenti

Araç parça seçim komponenti, Filament 4 için geliştirilmiş interaktif bir form ve görüntüleme komponentidir. Kullanıcıların araç parçalarını görsel olarak seçmesine ve görüntülemesine olanak sağlar.

## İçindekiler

- [Genel Bakış](#genel-bakış)
- [Kurulum](#kurulum)
- [Kullanım](#kullanım)
  - [Form Komponenti (CarPartPicker)](#form-komponenti-carpartpicker)
  - [Infolist Komponenti (CarPartView)](#infolist-komponenti-carpartview)
- [Özellikler](#özellikler)
- [Hızlı Seçim Butonları](#hızlı-seçim-butonları)
- [Parça Listesi](#parça-listesi)
- [Veritabanı Yapısı](#veritabanı-yapısı)
- [Örnekler](#örnekler)

## Genel Bakış

Komponent iki ana parçadan oluşur:

1. **CarPartPicker**: Form sayfalarında kullanılan, interaktif parça seçim komponenti
2. **CarPartView**: View/Infolist sayfalarında kullanılan, sadece görüntüleme komponenti

## Kurulum

Komponent zaten projeye entegre edilmiştir. Gerekli dosyalar:

- `app/Filament/Forms/Components/CarPartPicker.php`
- `app/Filament/Infolists/Components/CarPartView.php`
- `app/Services/GenerateCarSvg.php`
- `app/Enums/CarPartEnum.php`
- View dosyaları: `resources/views/filament/forms/components/car-part-picker.blade.php`
- View dosyaları: `resources/views/filament/infolists/components/car-part-view.blade.php`

## Kullanım

### Form Komponenti (CarPartPicker)

Form sayfalarında parça seçimi için kullanılır:

```php
use App\Filament\Forms\Components\CarPartPicker;

CarPartPicker::make('available_parts')
    ->label('Mevcut Parçalar')
    ->required()
    ->helperText('Araç parçalarını seçin')
```

#### Özellikler

- **Interaktif SVG**: Tıklanabilir araç görseli
- **Checkbox Listesi**: Sağ tarafta parça listesi
- **Hızlı Seçim Butonları**: Önceden tanımlı parça grupları
- **Hover Efektleri**: Parçaların üzerine gelindiğinde renk değişimi
- **Frontend-Only State**: Her değişiklikte backend'e gitmez, sadece form submit anında gönderilir

### Infolist Komponenti (CarPartView)

View/Infolist sayfalarında seçili parçaları görüntülemek için kullanılır:

```php
use App\Filament\Infolists\Components\CarPartView;

CarPartView::make('available_parts')
    ->label('Mevcut Parçalar')
```

#### Özellikler

- **Renklendirilmiş SVG**: Seçili parçalar renklendirilmiş olarak gösterilir
- **Parça Listesi**: Seçili parçaların listesi
- **Sadece Görüntüleme**: Düzenlenemez

## Özellikler

### Interaktif SVG

- Parçalara tıklayarak seçim yapabilirsiniz
- Hover efekti ile parçalar vurgulanır
- Seçili parçalar yeşil (body) veya mavi (window) renkte gösterilir

### Checkbox Listesi

- Sağ tarafta parçalar kategorilere ayrılmış şekilde listelenir
- **GÖVDE**: Tüm body parçaları
- **CAMLAR**: Tüm window parçaları

### State Yönetimi

- Frontend'de Alpine.js ile yönetilir
- Her değişiklikte backend'e gitmez
- Sadece form submit anında `dehydrateStateUsing` ile JSON string'e çevrilir
- Veritabanından okunurken `afterStateHydrated` ile array'e çevrilir

## Hızlı Seçim Butonları

Komponent üç adet hızlı seçim butonu içerir:

### 1. Ön Üç Parça
- Sağ Ön Çamurluk
- Kaput
- Sol Ön Çamurluk

### 2. Ön Dört Parça
- Sağ Ön Çamurluk
- Kaput
- Ön Tampon
- Sol Ön Çamurluk

### 3. Sadece Kaput
- Sadece Kaput parçası

#### Buton Özellikleri

- **Toggle Özelliği**: Aktif butona tekrar tıklandığında seçim kaldırılır
- **Aktif Durum**: Butonun parçaları seçiliyse buton aktif görünür (daha koyu renk ve ring efekti)
- **Replace Modu**: Butona tıklandığında mevcut seçimler temizlenir, sadece o butonun parçaları seçilir

## Parça Listesi

### Body Parçaları (Gövde)

| Enum Değeri | Label | Açıklama |
|------------|-------|----------|
| `body_tavan` | Tavan | Araç tavanı |
| `body_kaput` | Kaput | Motor kaputu |
| `body_bagaj` | Bagaj | Bagaj kapağı |
| `body_arka_tampon` | Arka Tampon | Arka tampon |
| `body_on_tampon` | Ön Tampon | Ön tampon |
| `body_sol_arka_camurluk` | Sol Arka Çamurluk | Sol arka çamurluk |
| `body_sol_on_camurluk` | Sol Ön Çamurluk | Sol ön çamurluk |
| `body_sol_arka_kapi` | Sol Arka Kapı | Sol arka kapı |
| `body_sol_on_kapi` | Sol Ön Kapı | Sol ön kapı |
| `body_sag_arka_camurluk` | Sağ Arka Çamurluk | Sağ arka çamurluk |
| `body_sag_on_camurluk` | Sağ Ön Çamurluk | Sağ ön çamurluk |
| `body_sag_arka_kapi` | Sağ Arka Kapı | Sağ arka kapı |
| `body_sag_on_kapi` | Sağ Ön Kapı | Sağ ön kapı |

### Window Parçaları (Camlar)

| Enum Değeri | Label | Açıklama |
|------------|-------|----------|
| `window_sunroof` | Sunroof | Tavan camı |
| `window_on_cam` | Ön Cam | Ön cam |
| `window_arka_cam` | Arka Cam | Arka cam |
| `window_sol_arka_kapi` | Sol Arka Kapı Camı | Sol arka kapı camı |
| `window_sol_on_kapi` | Sol Ön Kapı Camı | Sol ön kapı camı |
| `window_sag_arka_kapi` | Sağ Arka Kapı Camı | Sağ arka kapı camı |
| `window_sag_on_kapi` | Sağ Ön Kapı Camı | Sağ ön kapı camı |

## Veritabanı Yapısı

Komponent veritabanında JSON string olarak saklanır. Örnek:

```json
["body_kaput", "body_on_tampon", "window_on_cam"]
```

### Migration Örneği

```php
Schema::table('product_categories', function (Blueprint $table) {
    $table->text('available_parts')->nullable();
});
```

### Model Kullanımı

```php
use App\Enums\CarPartEnum;

class ProductCategory extends Model
{
    protected $casts = [
        'available_parts' => 'array', // JSON string'i otomatik array'e çevirir
    ];
    
    public function getAvailablePartsAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }
    
    public function setAvailablePartsAttribute($value)
    {
        $this->attributes['available_parts'] = is_array($value) 
            ? json_encode($value) 
            : $value;
    }
}
```

## Örnekler

### ProductCategories Resource'unda Kullanım

#### Form Schema

```php
use App\Filament\Forms\Components\CarPartPicker;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('Parça Bilgileri')
                ->schema([
                    CarPartPicker::make('available_parts')
                        ->label('Mevcut Parçalar')
                        ->required()
                        ->helperText('Bu kategori için hangi parçalar mevcut?'),
                ]),
        ]);
}
```

#### Infolist Schema

```php
use App\Filament\Infolists\Components\CarPartView;

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            Section::make('Parça Bilgileri')
                ->schema([
                    CarPartView::make('available_parts')
                        ->label('Mevcut Parçalar'),
                ]),
        ]);
}
```

### Enum Kullanımı

```php
use App\Enums\CarPartEnum;

// Tüm parçaları al
$allParts = CarPartEnum::cases();

// Sadece body parçaları
$bodyParts = CarPartEnum::getBodyParts();

// Sadece window parçaları
$windowParts = CarPartEnum::getWindowParts();

// Parça etiketlerini al
$labels = CarPartEnum::getLabels();
// ['body_kaput' => 'Kaput', 'window_on_cam' => 'Ön Cam', ...]

// Parça kontrolü
$part = CarPartEnum::BODY_KAPUT;
$isBody = $part->isBody(); // true
$isWindow = $part->isWindow(); // false
```

### GenerateCarSvg Servisi

```php
use App\Services\GenerateCarSvg;

$service = app(GenerateCarSvg::class);

// Seçili parçaları renklendirilmiş SVG olarak al (base64)
$selectedParts = ['body_kaput', 'body_on_tampon', 'window_on_cam'];
$svgBase64 = $service->fillCar($selectedParts, true);

// Veya dosya olarak kaydet
$filePath = $service->fillCar($selectedParts, false);
```

## Renkler

### Body Parçaları
- **Varsayılan**: `#efefef` (Açık gri)
- **Seçili**: `#1a8f14` (Yeşil)
- **Hover (Seçili)**: `#1a6711` (Koyu yeşil)
- **Hover (Seçili Değil)**: `#1a8f14` (Yeşil)

### Window Parçaları
- **Varsayılan**: `#bababa` (Gri)
- **Seçili**: `#3db5ff` (Mavi)
- **Hover (Seçili)**: `#156ca1` (Koyu mavi)
- **Hover (Seçili Değil)**: `#3db5ff` (Mavi)

## Teknik Detaylar

### State Formatı

- **Frontend**: `['body_kaput', 'body_on_tampon', 'window_on_cam']` (Array)
- **Backend/DB**: `"[\"body_kaput\",\"body_on_tampon\",\"window_on_cam\"]"` (JSON String)

### Performans

- SVG statik olarak yüklenir (her render'da template'den çekilmez)
- Frontend-only state yönetimi (her değişiklikte backend'e gitmez)
- Sadece form submit anında state gönderilir

### Bağımlılıklar

- Filament 4
- Alpine.js (Filament ile birlikte gelir)
- Livewire (Filament ile birlikte gelir)

## Sorun Giderme

### Parçalar Görünmüyor

- SVG dosyasının `storage/olex_car_template.svg` konumunda olduğundan emin olun
- Vite build'inin çalıştırıldığından emin olun: `pnpm run build`

### Renkler Güncellenmiyor

- Tarayıcı cache'ini temizleyin
- Vite dev server'ı yeniden başlatın: `pnpm run dev`

### State Kaydedilmiyor

- Veritabanı kolonunun `text` veya `json` tipinde olduğundan emin olun
- Model'de `$casts` tanımının doğru olduğundan emin olun

## Geliştirme Notları

- Komponent Filament 4 best practices'lerine uygun olarak geliştirilmiştir
- Türkçe label'lar kullanılmıştır
- Responsive tasarım desteklenir
- Dark mode desteklenir









