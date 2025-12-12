---
name: Dealer Geliştirmeleri
overview: ""
todos:
  - id: c651dacb-70e1-4150-8f4c-68bac4ed337c
    content: Dealer code migration'ı oluştur ve model'de otomatik kod üretimi ekle
    status: pending
  - id: 9893e3b0-9580-42b5-a969-2f35a4f7b4e8
    content: Sosyal medya ve konum migrationlarını oluştur
    status: pending
  - id: 604abeff-b534-425e-ad31-e800364da1b9
    content: LocationPicker custom component'ini oluştur (il-ilce.json kullanarak)
    status: pending
  - id: 85cd60d9-273b-49cb-975e-534c66ad2036
    content: DealerForm'a sosyal medya ve konum alanlarını ekle
    status: pending
  - id: f5a77049-b97d-4ca5-ac1b-5a19c52c41ba
    content: DealerExporter oluştur ve Excel export desteği ekle
    status: pending
  - id: e044deec-a33d-4e13-802e-e7ffacd58f7f
    content: UsersRelationManager oluştur ve hızlı çalışan ekleme özelliği ekle
    status: pending
  - id: 297e800b-924f-4419-abbf-aed3838d6146
    content: DealerResource'a UsersRelationManager'ı kaydet ve infolist'i güncelle
    status: pending
  - id: e4b98282-7baf-4b58-8b75-5c92bfea8014
    content: DealersTable'a dealer_code ve konum kolonlarını ekle
    status: pending
---

# Dealer Geliştirmeleri

## 1. Dealer Code Ekleme

### Migration

- `database/migrations/YYYY_MM_DD_HHMMSS_add_dealer_code_to_dealers_table.php` oluştur
- `dealer_code` kolonu ekle: `string('dealer_code', 8)->unique()->nullable()->after('id')`
- Mevcut kayıtlar için 8 haneli alfanumerik kod üret ve ata

### Model Güncellemeleri

- `app/Models/Dealer.php`:
- `dealer_code`'u `$fillable`'a ekle
- `boot()` metodunda otomatik kod üretimi ekle (oluşturma sırasında)
- `dealer_code`'u immutable yap (update sırasında değiştirilemez)

### Resource Güncellemeleri

- `app/Filament/Resources/Dealers/Tables/DealersTable.php`: `dealer_code` kolonunu ekle
- `app/Filament/Resources/Dealers/DealerResource.php`: Infolist'e `dealer_code` ekle

## 2. Excel Export Desteği

### Exporter Oluşturma

- `php artisan make:filament-exporter DealerExporter --generate` komutu ile exporter oluştur
- `app/Filament/Exports/DealerExporter.php` dosyasını düzenle:
- Tüm kolonları ekle (dealer_code, name, email, phone, address, city, district, social_media, is_active, created_at, updated_at)
- İlişkili veriler için (users_count gibi) custom state kullan

### Table'a Export Ekleme

- `app/Filament/Resources/Dealers/Tables/DealersTable.php`:
- `ExportAction` ekle (headerActions'a)
- `ExportBulkAction` ekle (toolbarActions'a)

## 3. Bayi Çalışanları Relation Manager

### Relation Manager Oluşturma

- `php artisan make:filament-relation-manager DealerResource users name` komutu ile oluştur
- `app/Filament/Resources/Dealers/RelationManagers/UsersRelationManager.php`:
- Form'da `dealer_id`'yi otomatik set et (Hidden component)
- Hızlı çalışan ekleme için `CreateAction` ekle
- Table'da kullanıcı bilgilerini göster (name, email, phone, roles, is_active)
- `headerActions`'a `CreateAction` ekle

### Resource'a Kayıt

- `app/Filament/Resources/Dealers/DealerResource.php`:
- `getRelations()` metoduna `UsersRelationManager::class` ekle

## 4. Sosyal Medya ve Konum Bilgileri

### Migration

- `database/migrations/YYYY_MM_DD_HHMMSS_add_social_media_and_location_to_dealers_table.php`:
- Sosyal medya: `facebook_url`, `instagram_url`, `twitter_url`, `linkedin_url`, `website_url` (nullable string)
- Konum: `city`, `district` (nullable string)

### Custom Location Component

- `php artisan make:filament-form-field LocationPicker` komutu ile oluştur
- `app/Filament/Forms/Components/LocationPicker.php`:
- İl ve İlçe seçimi için iki Select component
- `storage/il-ilce.json` dosyasını oku ve parse et
- İl seçildiğinde ilçeleri filtrele
- View: `resources/views/filament/forms/components/location-picker.blade.php`

### Form Güncellemeleri

- `app/Filament/Resources/Dealers/Schemas/DealerForm.php`:
- "Sosyal Medya" Section ekle (facebook, instagram, twitter, linkedin, website)
- "Konum Bilgileri" Section ekle (LocationPicker component kullan)
- Mevcut "Bayi Bilgileri" Section'ına `dealer_code` ekle (readonly, sadece görüntüleme için)

### Model Güncellemeleri

- `app/Models/Dealer.php`: Yeni alanları `$fillable`'a ekle

### Infolist Güncellemeleri

- `app/Filament/Resources/Dealers/DealerResource.php`:
- Sosyal medya bilgilerini göster (icon'larla)
- Konum bilgilerini göster

### Table Güncellemeleri

- `app/Filament/Resources/Dealers/Tables/DealersTable.php`:
- Konum bilgilerini göster (city, district)

## 5. Seeder Güncellemesi (Opsiyonel)

- Mevcut dealer kayıtları için `dealer_code` üret ve güncelle

## Dosyalar

### Yeni Dosyalar

- `database/migrations/YYYY_MM_DD_HHMMSS_add_dealer_code_to_dealers_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_add_social_media_and_location_to_dealers_table.php`
- `app/Filament/Exports/DealerExporter.php`
- `app/Filament/Resources/Dealers/RelationManagers/UsersRelationManager.php`
- `app/Filament/Forms/Components/LocationPicker.php`
- `resources/views/filament/forms/components/location-picker.blade.php`

### Güncellenecek Dosyalar

- `app/Models/Dealer.php`
- `app/Filament/Resources/Dealers/DealerResource.php`
- `app/Filament/Resources/Dealers/Tables/DealersTable.php`
- `app/Filament/Resources/Dealers/Schemas/DealerForm.php`