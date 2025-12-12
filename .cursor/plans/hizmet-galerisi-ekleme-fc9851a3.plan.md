---
name: Hizmet Galerisi Ekleme
overview: ""
todos:
  - id: 2d70f50c-37de-4b76-91e5-0760eb7dcb50
    content: ServiceImage model oluştur (app/Models/ServiceImage.php) - fillable, casts, relationships
    status: pending
  - id: 58f39966-f1e5-4982-b594-4347584d21e3
    content: service_images tablosu için migration oluştur (service_id, image_path, title, order, timestamps)
    status: pending
  - id: cc5863c9-8cf7-4ef4-8889-e82500167d7c
    content: Service model'ine images() HasMany relationship ekle
    status: pending
  - id: b3713c0b-e963-44a7-baf7-36326b1bdc86
    content: ServiceForm'a getGalleryStep() metodu ekle ve configure() metoduna dahil et
    status: pending
  - id: 03defed3-f218-4804-9aa8-2e602b32d713
    content: CreateService wizard'ına 'Galeri' step'i ekle
    status: pending
  - id: a12ab0d9-9ba7-478c-9d64-8b2b6525ac4a
    content: ServiceResource infolist'ine 'Galeri' Section'ı ekle (RepeatableEntry ile)
    status: pending
---

# Hizmet Galerisi Ekleme

## Genel Bakış

Services resource'una galeri özelliği eklenecek. Her hizmete birden fazla görsel eklenebilecek ve her görsele başlık ve sıra numarası verilebilecek.

## Yapılacaklar

### 1. Database Yapısı

- `ServiceImage` model oluşturulacak (`app/Models/ServiceImage.php`)
- `service_images` tablosu için migration oluşturulacak:
  - `id` (primary key)
  - `service_id` (foreign key, services tablosuna)
  - `image_path` (string, görsel yolu)
  - `title` (string, nullable, görsel başlığı)
  - `order` (integer, sıra numarası, default: 0)
  - `timestamps`

### 2. Model İlişkileri

- `Service` model'ine `images()` relationship eklenecek (HasMany)
- `ServiceImage` model'ine `service()` relationship eklenecek (BelongsTo)

### 3. Form Yapısı

- `ServiceForm` class'ına yeni bir metod eklenecek: `getGalleryStep()`
  - Repeater component kullanılacak
  - Her repeater item'da:
    - FileUpload (image_path) - görsel yükleme
    - TextInput (title) - başlık
    - TextInput (order) - sıra numarası (numeric)
  - Repeater relationship ile `images` bağlanacak
- `CreateService` wizard'ına yeni step eklenecek: "Galeri"
- `EditService` sayfasında form'a galeri bölümü eklenecek

### 4. Infolist Yapısı

- `ServiceResource::infolist()` metoduna yeni Section eklenecek: "Galeri"
  - `RepeatableEntry` kullanılacak
  - Her entry'de:
    - `ImageEntry` (image_path) - görsel gösterimi
    - `TextEntry` (title) - başlık
    - `TextEntry` (order) - sıra numarası
  - Görseller sıralı gösterilecek (order'a göre)

### 5. Dosya Yapısı

- Görseller `storage/app/public/services/gallery/` klasörüne kaydedilecek
- FileUpload component'inde:
  - `directory('services/gallery')`
  - `visibility('public')`
  - `imageEditor()` (görsel düzenleme özelliği)
  - `maxSize(2048)` (2MB limit)

## Dosya Değişiklikleri

### Yeni Dosyalar

- `app/Models/ServiceImage.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_service_images_table.php`

### Değiştirilecek Dosyalar

- `app/Models/Service.php` - `images()` relationship eklenecek
- `app/Filament/Resources/Services/Schemas/ServiceForm.php` - `getGalleryStep()` metodu eklenecek ve `configure()` metoduna dahil edilecek
- `app/Filament/Resources/Services/Pages/CreateService.php` - Wizard'a "Galeri" step'i eklenecek
- `app/Filament/Resources/Services/ServiceResource.php` - Infolist'e "Galeri" Section'ı eklenecek

## Teknik Detaylar

### Repeater Kullanımı

```php
Repeater::make('images')
    ->relationship('images')
    ->label('Galeri Görselleri')
    ->schema([
        FileUpload::make('image_path')
            ->label('Görsel')
            ->image()
            ->directory('services/gallery')
            ->visibility('public')
            ->imageEditor()
            ->maxSize(2048)
            ->required(),
        TextInput::make('title')
            ->label('Başlık')
            ->maxLength(255),
        TextInput::make('order')
            ->label('Sıra')
            ->numeric()
            ->default(0)
            ->minValue(0),
    ])
    ->reorderable()
    ->defaultItems(0)
    ->addActionLabel('Görsel Ekle')
```

### Infolist Gösterimi

```php
Section::make('Galeri')
    ->schema([
        RepeatableEntry::make('images')
            ->label('')
            ->schema([
                ImageEntry::make('image_path')
                    ->label('Görsel')
                    ->height(200),
                TextEntry::make('title')
                    ->label('Başlık'),
                TextEntry::make('order')
                    ->label('Sıra'),
            ])
            ->columns(3)
    ])
```

## Notlar

- Görseller public storage'da tutulacak
- Sıralama için `order` field'ı kullanılacak
- Repeater'da `reorderable()` özelliği ile drag-drop sıralama yapılabilecek
- Galeri step'i CreateService wizard'ında son step olacak
- EditService'de galeri bölümü form'un sonuna eklenecek