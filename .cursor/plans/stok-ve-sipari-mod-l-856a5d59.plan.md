<!-- 856a5d59-f893-44b7-a807-de3733db5e2a ab7bc591-1b89-480d-8b50-c223686032fc -->
# Stok Yönetimi ve Sipariş/Lojistik Modülü Planı

Bu plan RFC-003 spesifikasyonuna göre stok yönetimi ve sipariş modülünü implement eder.

## 1. Enum'ların Oluşturulması

[app/Enums/StockLocationEnum.php](app/Enums/StockLocationEnum.php) - Stok konumları (`center`, `dealer`, `service`, `trash`)
[app/Enums/StockStatusEnum.php](app/Enums/StockStatusEnum.php) - Stok durumları (`available`, `reserved`, `used`)
[app/Enums/OrderStatusEnum.php](app/Enums/OrderStatusEnum.php) - Sipariş durumları (`pending`, `processing`, `shipped`, `delivered`, `cancelled`)
[app/Enums/StockMovementActionEnum.php](app/Enums/StockMovementActionEnum.php) - Hareket aksiyonları (`imported`, `transferred_to_dealer`, `received`, `used_in_service`)

Her enum Türkçe label metodları içerecek (CarPartEnum örneğindeki gibi).

## 2. Veritabanı Migration'ları

[database/migrations/XXXX_create_stock_items_table.php](database/migrations) - `stock_items` tablosu:

- `product_id` (FK), `dealer_id` (nullable FK), `sku` (string), `barcode` (unique string)
- `location` (enum), `status` (enum), timestamps

[database/migrations/XXXX_create_orders_table.php](database/migrations) - `orders` tablosu:

- `dealer_id` (FK), `created_by` (FK), `status` (enum)
- `cargo_company`, `tracking_number`, `notes`, `total_amount` (admin only - nullable)
- timestamps

[database/migrations/XXXX_create_order_items_table.php](database/migrations) - `order_items` tablosu:

- `order_id` (FK), `product_id` (FK), `quantity` (integer), `unit_price` (decimal, nullable)

[database/migrations/XXXX_create_order_item_stock_table.php](database/migrations) - `order_item_stock` tablosu:

- `order_item_id` (FK), `stock_item_id` (FK)

[database/migrations/XXXX_create_stock_movements_table.php](database/migrations) - `stock_movements` tablosu:

- `stock_item_id` (FK), `user_id` (FK), `action` (string/enum), `description` (text)
- `created_at` (timestamp)

## 3. Model'ler ve İlişkiler

[app/Models/StockItem.php](app/Models/StockItem.php):

- Relationships: `belongsTo(Product)`, `belongsTo(Dealer)`, `hasMany(StockMovement)`, `belongsToMany(OrderItem)`
- Casts: `location` -> enum, `status` -> enum
- Scopes: `available()`, `reserved()`, `atLocation()`

[app/Models/Order.php](app/Models/Order.php):

- Relationships: `belongsTo(Dealer)`, `belongsTo(User, 'created_by')`, `hasMany(OrderItem)`
- Casts: `status` -> enum, `total_amount` -> decimal
- Scopes: `pending()`, `shipped()`, etc.

[app/Models/OrderItem.php](app/Models/OrderItem.php):

- Relationships: `belongsTo(Order)`, `belongsTo(Product)`, `belongsToMany(StockItem)`
- Casts: `unit_price` -> decimal

[app/Models/StockMovement.php](app/Models/StockMovement.php):

- Relationships: `belongsTo(StockItem)`, `belongsTo(User)`

Product model'ine `hasMany(StockItem)` ilişkisi eklenecek.
Dealer model'ine `hasMany(Order)` ve `hasMany(StockItem)` ilişkileri eklenecek.

## 4. Policy'ler

[app/Policies/StockItemPolicy.php](app/Policies/StockItemPolicy.php):

- Admin: Tam yetki
- Bayi: Sadece kendi `dealer_id`'si eşit ve `status != used` olanları görüntüleyebilir (read-only)

[app/Policies/OrderPolicy.php](app/Policies/OrderPolicy.php):

- Admin/Center Staff: Tüm siparişleri görüntüleyebilir, düzenleyebilir
- Bayi: Sadece kendi siparişlerini görüntüleyebilir ve oluşturabilir
- Fiyat alanları bayi için gizli

## 5. Filament Resources - StockItemResource

[app/Filament/Resources/StockItems/StockItemResource.php](app/Filament/Resources/StockItems):

- Navigation: "Stok Envanteri"
- ViewAny scope: Bayi için `dealer_id` filtresi

[app/Filament/Resources/StockItems/Pages/ListStockItems.php](app/Filament/Resources/StockItems/Pages):

- Header Action: "Hızlı Stok Girişi" (Modal)
- Form: Product Select + Textarea (barkodlar)
- Logic: Barkodları parse et, her biri için StockItem oluştur, StockMovement log at

[app/Filament/Resources/StockItems/Tables/StockItemsTable.php](app/Filament/Resources/StockItems/Tables):

- Columns: Barkod, Ürün Adı, Konum, Durum, Bayi
- Filters: Konum, Durum, Bayi, Ürün

[app/Filament/Resources/StockItems/Pages/ViewStockItem.php](app/Filament/Resources/StockItems/Pages):

- Infolist: Tüm bilgiler + hareket logları (StockMovement relation)

## 6. Filament Resources - OrderResource

[app/Filament/Resources/Orders/OrderResource.php](app/Filament/Resources/Orders):

- Navigation: "Siparişler"

[app/Filament/Resources/Orders/Pages/CreateOrder.php](app/Filament/Resources/Orders/Pages):

- Bayi için: Repeater (Product + Quantity)
- Fiyat alanları gizli (`hidden()`)
- `created_by` otomatik set edilir

[app/Filament/Resources/Orders/Pages/ViewOrder.php](app/Filament/Resources/Orders/Pages):

- Header Actions: 
- "Hazırlama Yap" (sadece Admin/Center Staff, sadece `pending`/`processing` için)
- Modal: Her `order_item` için mevcut stokları listele (Select/CheckboxList)
- Logic: Seçilen barkodları `order_item_stock`'a ekle, `stock_items.status = reserved` yap
- "Kargoya Ver" (sadece Admin/Center Staff, `processing` için)
- Form: Kargo firması, takip numarası
- Logic: `status = shipped` yap
- "Teslim Al" (sadece Bayi, `shipped` için)
- Logic: Siparişe bağlı tüm stock_items'ı güncelle (`dealer_id`, `location = dealer`, `status = available`), `status = delivered`, log at

[app/Filament/Resources/Orders/Schemas/OrderForm.php](app/Filament/Resources/Orders/Schemas):

- Section'lar: Temel Bilgiler, Sipariş Kalemleri (Repeater), Kargo Bilgileri, Özet (admin only - total_amount)

[app/Filament/Resources/Orders/Tables/OrdersTable.php](app/Filament/Resources/Orders/Tables):

- Columns: Sipariş No, Bayi, Durum, Tarih
- Scope: Bayi için kendi siparişleri

## 7. İş Mantığı ve Doğrulamalar

- Stok çakışması önleme: Hazırlama modalında sadece `location = center` ve `status = available` olan stoklar gösterilecek
- Scope filtreleri: Policy'lerde ve query scope'larında dealer kontrolü
- Fiyat gizleme: Form'larda `visible(fn() => auth()->user()->hasRole('super_admin'))`
- Transaction kullanımı: Kritik işlemlerde (hazırlama, teslim alma) DB transaction

## 8. Stok Hareket Loglama

Her kritik işlemde `StockMovement` kaydı oluşturulacak:

- Hızlı stok girişi: `action = imported`
- Hazırlama: `action = transferred_to_dealer` (description: "Sipariş #X ile Bayi Y'ye yollandı")
- Teslim alma: `action = received`

## 9. Filament Best Practices

- Section kullanımı: Form'larda `Filament\Schemas\Components\Section`
- Infolist'lerde ViewPage için Section kullanımı
- Türkçe label'lar
- ViewPage her resource için
- Table'da ViewAction ekleme

### To-dos

- [ ] Enum'ları oluştur: StockLocationEnum, StockStatusEnum, OrderStatusEnum, StockMovementActionEnum (Türkçe label metodları ile)
- [x] 5 adet migration oluştur: stock_items, orders, order_items, order_item_stock, stock_movements (enum'ları, foreign key'leri, unique index'leri dahil)
- [x] Model'leri oluştur: StockItem, Order, OrderItem, StockMovement (ilişkiler, cast'ler, scope'lar ile birlikte). Mevcut Product ve Dealer model'lerine ilişkiler ekle
- [x] Policy'leri oluştur: StockItemPolicy (admin tam yetki, bayi read-only kendi stokları), OrderPolicy (admin tam yetki, bayi kendi siparişleri). AppServiceProvider'da register et
- [x] StockItemResource'u oluştur: Resource, List/View pages, Table, Infolist. Header'da 'Hızlı Stok Girişi' action (modal ile barkod toplu giriş)
- [x] OrderResource'u oluştur: Resource, Create/View/Edit pages, Form (Repeater ile sipariş kalemleri), Table. ViewPage'de 'Hazırlama', 'Kargoya Ver', 'Teslim Al' action'ları
- [x] İş mantığını implement et: Stok çakışması önleme (hazırlama modalında sadece available stoklar), transaction kullanımı, dealer scope filtreleri, fiyat gizleme (admin only)
- [x] Stok hareket loglamayı implement et: Her kritik işlemde (hızlı giriş, hazırlama, teslim alma) StockMovement kaydı oluştur. ViewStockItem'da hareket geçmişi göster