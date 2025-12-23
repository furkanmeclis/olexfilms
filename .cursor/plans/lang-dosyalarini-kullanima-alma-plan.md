# Lang Dosyalarını Kullanıma Alma Planı

## 1. Mevcut Durum Analizi

Sistemde şu anda tüm label'lar hardcoded olarak yazılmış durumda:
- Resource'larda: `navigationLabel`, `modelLabel`, `pluralModelLabel` hardcoded
- Form'larda: `->label('...')` hardcoded
- Table'larda: `->label('...')` hardcoded
- Infolist'lerde: `->label('...')` hardcoded
- Section'larda: `Section::make('...')` hardcoded
- Action'larda: `->label('...')` hardcoded
- Notification'larda: `->title('...')`, `->body('...')` hardcoded
- Enum'larda: `getLabels()` metodları hardcoded (lang dosyalarından çekilmiyor)

## 2. Çözüm Stratejisi

### 2.1. Helper Trait/Class Oluşturma

Lang dosyalarından çekmek için helper metodlar oluşturulacak:
- `app/Helpers/LangHelper.php` veya `app/Traits/HasTranslations.php`
- Enum label'ları için helper metod
- Resource label'ları için helper metod
- Field label'ları için helper metod

### 2.2. Enum Label'larını Lang Dosyalarından Çekme

Enum'lardaki `getLabels()` metodlarını lang dosyalarından çekecek şekilde güncelleme:
- `app/Enums/*.php` dosyalarında `getLabels()` metodları `trans('enums.enum_name')` kullanacak
- Fallback olarak mevcut hardcoded değerler korunacak

### 2.3. Resource Label'larını Lang Dosyalarından Çekme

Her Resource'ta:
- `navigationLabel` → `trans('resources.{resource}.navigation_label')`
- `modelLabel` → `trans('resources.{resource}.model_label')`
- `pluralModelLabel` → `trans('resources.{resource}.plural_model_label')`

### 2.4. Form/Table/Infolist Label'larını Lang Dosyalarından Çekme

Her form/table/infolist dosyasında:
- `->label('...')` → `->label(__('resources.{resource}.fields.{field}'))`
- Section başlıkları → `Section::make(__('resources.{resource}.sections.{section}'))`

### 2.5. Action Label'larını Lang Dosyalarından Çekme

Action'larda:
- `->label('...')` → `->label(__('resources.{resource}.actions.{action}'))`
- Veya `->label(__('common.actions.{action}'))` (genel action'lar için)

### 2.6. Notification Mesajlarını Lang Dosyalarından Çekme

Notification'larda:
- `->title('...')` → `->title(__('resources.{resource}.messages.{message}'))`
- `->body('...')` → `->body(__('resources.{resource}.messages.{message}_body'))`

## 3. Implementation Adımları

### Adım 1: Helper Trait/Class Oluşturma

**Dosya:** `app/Helpers/LangHelper.php` veya `app/Traits/HasTranslations.php`

Helper metodlar:
- `transEnum(string $enumName, string $value): string` - Enum label'ları için
- `transResource(string $resource, string $key, ?string $default = null): string` - Resource label'ları için
- `transField(string $resource, string $field, ?string $default = null): string` - Field label'ları için
- `transSection(string $resource, string $section, ?string $default = null): string` - Section başlıkları için
- `transAction(string $resource, string $action, ?string $default = null): string` - Action label'ları için

### Adım 2: Enum'ları Güncelleme

Her enum dosyasında `getLabels()` metodunu güncelle:
- `app/Enums/CarPartEnum.php`
- `app/Enums/CustomerTypeEnum.php`
- `app/Enums/OrderStatusEnum.php`
- `app/Enums/ServiceStatusEnum.php`
- `app/Enums/StockLocationEnum.php`
- `app/Enums/StockStatusEnum.php`
- `app/Enums/StockMovementActionEnum.php`
- `app/Enums/ServiceItemUsageTypeEnum.php`
- `app/Enums/ServiceReportMatchTypeEnum.php`
- `app/Enums/NexptgApiLogTypeEnum.php`
- `app/Enums/NexptgPartTypeEnum.php`
- `app/Enums/NexptgPlaceIdEnum.php`
- `app/Enums/UserRoleEnum.php` (getLabels() metodu yok, eklenmeli)
- `app/Enums/UserStatusEnum.php` (getLabels() metodu yok, eklenmeli)

### Adım 3: Resource'ları Güncelleme (16 adet)

Her Resource'ta:
1. `navigationLabel` → `trans('resources.{resource}.navigation_label')`
2. `modelLabel` → `trans('resources.{resource}.model_label')`
3. `pluralModelLabel` → `trans('resources.{resource}.plural_model_label')`

Resource'lar:
- Products
- Dealers
- Users
- Services
- Orders
- CarBrands
- CarModels
- Customers
- ProductCategories
- StockItems
- Warranties
- BulkSms
- NexptgApiUsers
- NexptgReports
- ServiceStatusLogs
- SmsLogs

### Adım 4: Form Dosyalarını Güncelleme

Her form dosyasında:
1. Section başlıkları: `Section::make(__('resources.{resource}.sections.{section}'))`
2. Field label'ları: `->label(__('resources.{resource}.fields.{field}'))`

Form dosyaları:
- `app/Filament/Resources/*/Schemas/*Form.php` (16 adet)

### Adım 5: Table Dosyalarını Güncelleme

Her table dosyasında:
1. Column label'ları: `->label(__('resources.{resource}.table.columns.{column}'))`
2. Filter label'ları: `->label(__('resources.{resource}.table.filters.{filter}'))`
3. Action label'ları: `->label(__('resources.{resource}.actions.{action}'))`

Table dosyaları:
- `app/Filament/Resources/*/Tables/*Table.php` (16 adet)

### Adım 6: Infolist Dosyalarını Güncelleme

Her infolist dosyasında:
1. Section başlıkları: `Section::make(__('resources.{resource}.sections.{section}'))`
2. Entry label'ları: `->label(__('resources.{resource}.fields.{field}'))`

Infolist dosyaları:
- `app/Filament/Resources/*/Schemas/*Infolist.php` (mevcut olanlar)

### Adım 7: Page Dosyalarını Güncelleme

Custom page'lerde:
1. `navigationLabel` → `trans('pages.{page}.navigation_label')`
2. `title` → `trans('pages.{page}.title')`
3. Field label'ları: `->label(__('pages.{page}.fields.{field}'))`
4. Section başlıkları: `Section::make(__('pages.{page}.sections.{section}'))`
5. Action label'ları: `->label(__('pages.{page}.actions.{action}'))`
6. Notification mesajları: `->title(__('pages.{page}.messages.{message}'))`

Page dosyaları:
- `app/Filament/Pages/ServiceStatusManagement.php`
- `app/Filament/Pages/ManageVatanSmsSettings.php`

### Adım 8: Notification Mesajlarını Güncelleme

Tüm notification'larda:
- `->title('...')` → `->title(__('resources.{resource}.messages.{message}'))`
- `->body('...')` → `->body(__('resources.{resource}.messages.{message}_body'))`

### Adım 9: Action Label'larını Güncelleme

Tüm action'larda:
- `->label('...')` → `->label(__('resources.{resource}.actions.{action}'))`
- Veya genel action'lar için: `->label(__('common.actions.{action}'))`

### Adım 10: Placeholder ve Helper Text'leri Güncelleme

Placeholder ve helper text'ler için lang dosyalarına eklemeler yapılacak ve kullanılacak.

## 4. Dosya Yapısı

```
app/
├── Helpers/
│   └── LangHelper.php (yeni)
├── Enums/
│   └── *.php (güncellenecek - 14 adet)
├── Filament/
│   ├── Resources/
│   │   └── */ (güncellenecek - 16 resource)
│   │       ├── *Resource.php
│   │       ├── Schemas/
│   │       │   ├── *Form.php
│   │       │   └── *Infolist.php
│   │       └── Tables/
│   │           └── *Table.php
│   └── Pages/
│       └── *.php (güncellenecek - 2 adet)
```

## 5. Örnek Kullanım

### Enum Örneği:
```php
// Önce:
public static function getLabels(): array
{
    return [
        self::PENDING->value => 'Bekliyor',
        self::PROCESSING->value => 'Hazırlanıyor',
    ];
}

// Sonra:
public static function getLabels(): array
{
    return trans('enums.order_status', [], 'tr');
}
```

### Resource Örneği:
```php
// Önce:
protected static ?string $navigationLabel = 'Ürünler';

// Sonra:
protected static ?string $navigationLabel = null;

public static function getNavigationLabel(): string
{
    return static::$navigationLabel ?? __('resources.products.navigation_label');
}
```

### Form Örneği:
```php
// Önce:
TextInput::make('name')
    ->label('Ürün Adı')

// Sonra:
TextInput::make('name')
    ->label(__('resources.products.fields.name'))
```

### Section Örneği:
```php
// Önce:
Section::make('Temel Bilgiler')

// Sonra:
Section::make(__('resources.products.sections.temel_bilgiler'))
```

## 6. Önemli Notlar

1. **Fallback Mekanizması**: Lang dosyasından çekilemezse, mevcut hardcoded değerler fallback olarak kullanılacak
2. **Backward Compatibility**: Mevcut kod çalışmaya devam etmeli
3. **Performance**: `trans()` fonksiyonu cache'lenmiş olmalı
4. **Test**: Her değişiklikten sonra manuel test yapılmalı
5. **Enum'lar**: Enum'lardaki `getLabels()` metodları lang dosyalarından çekilecek, ancak mevcut kullanımlar bozulmamalı

## 7. Test Checklist

- [ ] Tüm resource'ların navigation label'ları doğru görünüyor mu?
- [ ] Tüm form field'ları doğru label'lara sahip mi?
- [ ] Tüm table column'ları doğru label'lara sahip mi?
- [ ] Tüm infolist entry'leri doğru label'lara sahip mi?
- [ ] Tüm section başlıkları doğru mu?
- [ ] Tüm action label'ları doğru mu?
- [ ] Tüm notification mesajları doğru mu?
- [ ] Enum label'ları doğru görünüyor mu?
- [ ] Placeholder'lar doğru mu?
- [ ] Helper text'ler doğru mu?

## 8. İlerleme Takibi

1. Helper class/trait oluşturma
2. Enum'ları güncelleme (14 adet)
3. Resource'ları güncelleme (16 adet)
4. Form dosyalarını güncelleme (16 adet)
5. Table dosyalarını güncelleme (16 adet)
6. Infolist dosyalarını güncelleme (mevcut olanlar)
7. Page dosyalarını güncelleme (2 adet)
8. Notification mesajlarını güncelleme
9. Action label'larını güncelleme
10. Test ve doğrulama

