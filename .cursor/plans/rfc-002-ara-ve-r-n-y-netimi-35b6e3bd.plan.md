<!-- 35b6e3bd-3f5d-48eb-9f8d-77bd97446f2c 4d3d0b74-4399-40b4-b5ed-54e17454bc4b -->
# RFC-002: Araç Veri Tabanı ve Ürün Yönetimi

## 1. Veritabanı Migrations

### 1.1. Car Brands Tablosu

- `database/migrations/YYYY_MM_DD_HHMMSS_create_car_brands_table.php` oluştur
- Legacy SQL yapısını aynen koru: `id`, `name` (indexed), `external_id` (unique), `logo` (nullable), `last_update` (nullable), `is_active` (default: 1), `timestamps`, `softDeletes`

### 1.2. Car Models Tablosu

- `database/migrations/YYYY_MM_DD_HHMMSS_create_car_models_table.php` oluştur
- Legacy SQL yapısını aynen koru: `id`, `brand_id` (foreignId, cascade), `name`, `external_id` (unique), `last_update` (nullable), `is_active` (default: 1), `timestamps`, `softDeletes`
- Index: `['brand_id', 'name']`

### 1.3. Product Categories Tablosu

- `database/migrations/YYYY_MM_DD_HHMMSS_create_product_categories_table.php` oluştur
- Alanlar: `id`, `name` (string), `available_parts` (json), `is_active` (boolean, default: true), `timestamps`, `softDeletes`

### 1.4. Products Tablosu

- `database/migrations/YYYY_MM_DD_HHMMSS_create_products_table.php` oluştur
- Alanlar: `id`, `category_id` (foreignId, constrained), `name` (string), `sku` (string, unique, indexed), `description` (longText), `warranty_duration` (integer), `price` (decimal 10,2), `image_path` (string, nullable), `is_active` (boolean, default: true), `timestamps`, `softDeletes`

## 2. Enum ve Sabit Veriler

### 2.1. Car Part Enum

- `app/Enums/CarPartEnum.php` oluştur
- RFC'de belirtilen 13 parça için string backed enum
- Değerler: `BODY_TAVAN`, `BODY_KAPUT`, `BODY_BAGAJ`, vb.
- `getLabels()` static metodu ile key-label mapping döndür

## 3. Modeller ve İlişkiler

### 3.1. CarBrand Model

- `app/Models/CarBrand.php` oluştur
- `models()` relationship (hasMany CarModel)
- Fillable: `name`, `external_id`, `logo`, `last_update`, `is_active`
- Casts: `is_active` (boolean), `last_update` (datetime)
- SoftDeletes trait

### 3.2. CarModel Model

- `app/Models/CarModel.php` oluştur
- `brand()` relationship (belongsTo CarBrand)
- Fillable: `brand_id`, `name`, `external_id`, `last_update`, `is_active`
- Casts: `is_active` (boolean), `last_update` (datetime)
- SoftDeletes trait

### 3.3. ProductCategory Model

- `app/Models/ProductCategory.php` oluştur
- `products()` relationship (hasMany Product)
- Fillable: `name`, `available_parts`, `is_active`
- Casts: `available_parts` (array), `is_active` (boolean)
- SoftDeletes trait

### 3.4. Product Model

- `app/Models/Product.php` oluştur
- `category()` relationship (belongsTo ProductCategory)
- Fillable: `category_id`, `name`, `sku`, `description`, `warranty_duration`, `price`, `image_path`, `is_active`
- Casts: `warranty_duration` (integer), `price` (decimal:2), `is_active` (boolean)
- **KRİTİK:** `protected $hidden = ['price']` - Serialization'da fiyat gizli
- SoftDeletes trait

## 4. Policies

### 4.1. CarBrandPolicy

- `app/Policies/CarBrandPolicy.php` oluştur
- `viewAny`, `view`, `create`, `update`, `delete`: Sadece `super_admin` rolü

### 4.2. CarModelPolicy

- `app/Policies/CarModelPolicy.php` oluştur
- `viewAny`, `view`, `create`, `update`, `delete`: Sadece `super_admin` rolü

### 4.3. ProductCategoryPolicy

- `app/Policies/ProductCategoryPolicy.php` oluştur
- `viewAny`: Herkes (admin, merkez, bayi)
- `create`, `update`, `delete`: Sadece `super_admin` ve `center_staff`

### 4.4. ProductPolicy

- `app/Policies/ProductPolicy.php` oluştur
- `viewAny`: Herkes (admin, merkez, bayi)
- `view`: Herkes
- `create`, `update`, `delete`: Sadece `super_admin` ve `center_staff`

## 5. Filament Resources

### 5.1. CarBrandResource

- `app/Filament/Resources/Cars/CarBrandResource.php` oluştur
- `app/Filament/Resources/Cars/Schemas/CarBrandForm.php` oluştur
- `app/Filament/Resources/Cars/Tables/CarBrandsTable.php` oluştur
- `app/Filament/Resources/Cars/Pages/` altında: `ListCarBrands.php`, `CreateCarBrand.php`, `EditCarBrand.php`, `ViewCarBrand.php`
- Form: Section ile "Marka Bilgileri" (name, external_id, logo, is_active)
- Table: name, external_id, is_active, created_at
- Infolist: Section ile detay görünümü
- Navigation: Sadece super_admin görür

### 5.2. CarModelResource

- `app/Filament/Resources/Cars/CarModelResource.php` oluştur
- `app/Filament/Resources/Cars/Schemas/CarModelForm.php` oluştur
- `app/Filament/Resources/Cars/Tables/CarModelsTable.php` oluştur
- `app/Filament/Resources/Cars/Pages/` altında: `ListCarModels.php`, `CreateCarModel.php`, `EditCarModel.php`, `ViewCarModel.php`
- Form: Section ile "Model Bilgileri" (brand_id Select/BelongsTo, name, external_id, is_active)
- Table: brand.name, name, external_id, is_active, created_at
- Infolist: Section ile detay görünümü
- Navigation: Sadece super_admin görür

### 5.3. ProductCategoryResource

- `app/Filament/Resources/Products/ProductCategoryResource.php` oluştur
- `app/Filament/Resources/Products/Schemas/ProductCategoryForm.php` oluştur
- `app/Filament/Resources/Products/Tables/ProductCategoriesTable.php` oluştur
- `app/Filament/Resources/Products/Pages/` altında: `ListProductCategories.php`, `CreateProductCategory.php`, `EditProductCategory.php`, `ViewProductCategory.php`
- Form: Section ile "Kategori Bilgileri"
  - `name` (TextInput)
  - `available_parts` (CheckboxList veya Select::multiple) - CarPartEnum'dan options al
  - `is_active` (Toggle)
- Table: name, available_parts (badge listesi), is_active, created_at
- Infolist: Section ile detay görünümü

### 5.4. ProductResource

- `app/Filament/Resources/Products/ProductResource.php` oluştur
- `app/Filament/Resources/Products/Schemas/ProductForm.php` oluştur
- `app/Filament/Resources/Products/Tables/ProductsTable.php` oluştur
- `app/Filament/Resources/Products/Pages/` altında: `ListProducts.php`, `CreateProduct.php`, `EditProduct.php`, `ViewProduct.php`
- Form: Section'lar ile
  - "Temel Bilgiler": category_id (Select), name, sku (unique validation), is_active
  - "Açıklama": description (RichEditor)
  - "Fiyat ve Garanti": warranty_duration (TextInput, suffix "Ay"), **price (TextInput, sadece super_admin için visible)**
  - "Görsel": image_path (FileUpload)
- Table: name, sku, category.name, price (sadece super_admin için visible), is_active, created_at
- Infolist: Section ile detay görünümü (price sadece super_admin için visible)
- **KRİTİK:** Fiyat alanları `visible(fn () => auth()->user()->hasRole('super_admin'))` ile kontrol edilmeli

## 6. Özel İş Kuralları

### 6.1. Fiyat Gizliliği

- Product model'de `$hidden = ['price']` attribute
- ProductResource form'da price field: `->visible(fn () => auth()->user()->hasRole('super_admin'))`
- ProductResource table'da price column: `->visible(fn () => auth()->user()->hasRole('super_admin'))`
- ProductResource infolist'te price entry: `->visible(fn () => auth()->user()->hasRole('super_admin'))`

### 6.2. SKU Unique Validation

- ProductForm'da sku field: `->unique(ignoreRecord: true)`

### 6.3. Warranty Duration Suffix

- ProductForm'da warranty_duration field: `->suffix('Ay')`

## 7. Dosya Yapısı

```
app/
├── Enums/
│   └── CarPartEnum.php
├── Models/
│   ├── CarBrand.php
│   ├── CarModel.php
│   ├── ProductCategory.php
│   └── Product.php
├── Policies/
│   ├── CarBrandPolicy.php
│   ├── CarModelPolicy.php
│   ├── ProductCategoryPolicy.php
│   └── ProductPolicy.php
└── Filament/Resources/
    ├── Cars/
    │   ├── CarBrandResource.php
    │   ├── CarModelResource.php
    │   ├── Schemas/
    │   │   ├── CarBrandForm.php
    │   │   └── CarModelForm.php
    │   ├── Tables/
    │   │   ├── CarBrandsTable.php
    │   │   └── CarModelsTable.php
    │   └── Pages/
    │       ├── ListCarBrands.php, CreateCarBrand.php, EditCarBrand.php, ViewCarBrand.php
    │       └── ListCarModels.php, CreateCarModel.php, EditCarModel.php, ViewCarModel.php
    └── Products/
        ├── ProductCategoryResource.php
        ├── ProductResource.php
        ├── Schemas/
        │   ├── ProductCategoryForm.php
        │   └── ProductForm.php
        ├── Tables/
        │   ├── ProductCategoriesTable.php
        │   └── ProductsTable.php
        └── Pages/
            ├── ListProductCategories.php, CreateProductCategory.php, EditProductCategory.php, ViewProductCategory.php
            └── ListProducts.php, CreateProduct.php, EditProduct.php, ViewProduct.php

database/migrations/
├── YYYY_MM_DD_HHMMSS_create_car_brands_table.php
├── YYYY_MM_DD_HHMMSS_create_car_models_table.php
├── YYYY_MM_DD_HHMMSS_create_product_categories_table.php
└── YYYY_MM_DD_HHMMSS_create_products_table.php
```

## 8. Test ve Doğrulama

- Migration'ları çalıştır
- Model ilişkilerini test et
- Policy'leri test et (farklı rollerle)
- Filament panelinde manuel test yap
- Fiyat gizliliğini doğrula (bayi rolü ile giriş yapıp price görünmediğini kontrol et)
- SKU unique validation'ı test et