<!-- 0b02d300-72ff-46ff-8fde-6618985c102f da7f58c4-8d9c-4380-97f8-7920489bba75 -->
# RFC-001: Kullanıcı Rol Yönetimi ve Bayi Mimarisi

## 1. Paket Kurulumu ve Yapılandırma

- Spatie Laravel Permission paketini kur (`composer require spatie/laravel-permission`)
- Migration dosyalarını publish et
- User modeline `HasRoles` trait'ini ekle
- Permission cache yapılandırmasını kontrol et

## 2. Veritabanı Migrations

### 2.1. Dealers Tablosu

- `database/migrations/YYYY_MM_DD_HHMMSS_create_dealers_table.php` oluştur
- Alanlar: `id`, `name`, `email`, `phone`, `address`, `logo_path` (nullable), `is_active` (default: true), `timestamps`

### 2.2. Users Tablosu Genişletme

- `database/migrations/YYYY_MM_DD_HHMMSS_add_dealer_fields_to_users_table.php` oluştur
- `dealer_id` (foreignId, nullable, constrained to dealers)
- `phone` (string, required)
- `avatar_path` (string, nullable)
- `is_active` (boolean, default: true)

### 2.3. Spatie Permission Migrations

- Paket migration'larını çalıştır (roles, permissions, model_has_roles, model_has_permissions, role_has_permissions)

## 3. Model ve İlişkiler

### 3.1. Dealer Model

- `app/Models/Dealer.php` oluştur
- `users` relationship (hasMany)
- `isActive()` scope
- Fillable fields tanımla

### 3.2. User Model Genişletme

- `HasRoles` trait ekle (Spatie)
- `dealer()` relationship (belongsTo, nullable)
- `isActive()` scope
- `canAccess()` method (bayi aktif kontrolü)
- Fillable fields genişlet (`dealer_id`, `phone`, `avatar_path`, `is_active`)

## 4. Enum Yapıları

### 4.1. UserRoleEnum

- `app/Enums/UserRoleEnum.php` oluştur
- Değerler: `SUPER_ADMIN`, `CENTER_STAFF`, `DEALER_OWNER`, `DEALER_STAFF`
- String backed enum

### 4.2. UserStatusEnum (Opsiyonel)

- `app/Enums/UserStatusEnum.php` oluştur
- Değerler: `ACTIVE`, `PASSIVE`
- İleride genişletilebilirlik için

## 5. Seeders

### 5.1. RolesAndPermissionsSeeder

- `database/seeders/RolesAndPermissionsSeeder.php` oluştur
- Roller: `super_admin`, `center_staff`, `dealer_owner`, `dealer_staff`
- İlk super_admin kullanıcısı oluştur

## 6. Middleware ve Authentication

### 6.1. CheckDealerActive Middleware

- `app/Http/Middleware/CheckDealerActive.php` oluştur
- Login sonrası: kullanıcı aktif mi? → Bayiye bağlıysa bayi aktif mi?
- Pasifse logout yap ve hata mesajı göster

### 6.2. Authentication Logic

- `app/Http/Controllers/Auth/LoginController.php` veya Filament login hook'u
- Login öncesi kontrol ekle

## 7. Filament Resources

### 7.1. UserResource

- `app/Filament/Resources/UserResource.php` oluştur
- **Form Schema:**
- Admin için: `dealer_id` Select (nullable)
- Bayi Sahibi için: `dealer_id` Hidden (otomatik kendi ID)
- Rol seçimi (Select) - Admin tüm rolleri, Bayi Sahibi sadece `dealer_staff`
- Şifre alanı opsiyonel (sadece create'de veya edit'te değiştirilebilir)
- **Table:**
- Admin: Tüm kullanıcılar
- Bayi Sahibi: `getEloquentQuery()` override ile sadece kendi `dealer_id`'sine sahip kullanıcılar
- **Policies:** Authorization kontrolü

### 7.2. DealerResource

- `app/Filament/Resources/DealerResource.php` oluştur
- **Erişim:** Sadece `super_admin` ve yetkili `center_staff`
- **Form Schema:** Wizard kullan
- Adım 1: Bayi bilgileri (name, email, phone, address, logo_path, is_active)
- Adım 2: Bayi Yöneticisi oluştur (opsiyonel)
- Mevcut kullanıcı seç veya yeni kullanıcı oluştur
- Yeni kullanıcı: name, email, phone, password
- Rol: `dealer_owner` otomatik atanır
- **Actions:** "Bayi Yöneticisi Değiştir" action butonu
- **Policies:** Authorization kontrolü

## 8. Policies

### 8.1. UserPolicy

- `app/Policies/UserPolicy.php` oluştur
- `viewAny`, `view`, `create`, `update`, `delete` metodları
- Admin: Tüm yetkiler
- Bayi Sahibi: Sadece kendi dealer_id'sine sahip kullanıcılar

### 8.2. DealerPolicy

- `app/Policies/DealerPolicy.php` oluştur
- `viewAny`, `view`, `create`, `update`, `delete` metodları
- Sadece `super_admin` ve yetkili `center_staff`

## 9. Global Scopes (Opsiyonel)

### 9.1. ActiveUserScope

- `app/Models/Scopes/ActiveUserScope.php` oluştur
- Sadece aktif kullanıcıları getir (opsiyonel, manuel filtreleme de yapılabilir)

### 9.2. ActiveDealerScope

- `app/Models/Scopes/ActiveDealerScope.php` oluştur
- Sadece aktif bayileri getir

## 10. Factory ve Seeder Güncellemeleri

### 10.1. DealerFactory

- `database/factories/DealerFactory.php` oluştur

### 10.2. UserFactory Güncelleme

- `dealer_id`, `phone`, `avatar_path`, `is_active` alanlarını ekle

## 11. Test ve Doğrulama

- Migration'ları çalıştır
- Seeder'ları çalıştır
- Filament panelinde test et:
- Admin olarak giriş yap
- Bayi oluştur (wizard ile)
- Kullanıcı oluştur
- Bayi Sahibi olarak giriş yap
- Scoping kontrolü yap