<?php

namespace App\Imports;

use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Events\Orders\OrderItemCreated;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockItemImport extends EnhancedDefaultImport
{
    /**
     * Import edilen StockItem'ları tutar (Order oluşturma için)
     */
    protected array $importedStockItems = [];

    /**
     * Atlanan satırları tutar (rapor için)
     */
    protected array $skippedRows = [];

    /**
     * Başarılı import sayısı
     */
    protected int $successCount = 0;

    /**
     * Güncellenen StockItem sayısı
     */
    protected int $updatedCount = 0;

    /**
     * Oluşturulan StockItem sayısı
     */
    protected int $createdCount = 0;

    /**
     * Collection işlenmeden önce çağrılır
     */
    protected function beforeCollection(Collection $collection): void
    {
        // Header validation - en az bir ürün ve bir bayi kolonu olmalı
        $productHeaders = ['ÜRÜN KODU', 'ÜRÜN', 'ÜRÜN SKU'];
        $dealerHeaders = ['BAYİ KODU', 'BAYİ'];
        
        $hasProductHeader = false;
        $hasDealerHeader = false;
        
        if ($collection->isNotEmpty()) {
            $firstRow = $collection->first();
            if (is_array($firstRow)) {
                $headers = array_keys($firstRow);
                $hasProductHeader = !empty(array_intersect($productHeaders, $headers));
                $hasDealerHeader = !empty(array_intersect($dealerHeaders, $headers));
            }
        }
        
        if (!$hasProductHeader) {
            $this->stopImportWithError('Excel dosyasında ÜRÜN KODU, ÜRÜN SKU veya ÜRÜN kolonlarından en az biri bulunmalıdır.');
        }
        
        if (!$hasDealerHeader) {
            $this->stopImportWithError('Excel dosyasında BAYİ KODU veya BAYİ kolonlarından en az biri bulunmalıdır.');
        }

        // Collection'ı temizle ve hazırla
        $this->importedStockItems = [];
        $this->skippedRows = [];
        $this->successCount = 0;
        $this->updatedCount = 0;
        $this->createdCount = 0;
    }

    /**
     * Her satır için model oluşturulmadan önce çağrılır
     */
    public function model(array $row): ?StockItem
    {
        // ÜRÜN KODU (barcode) kontrolü - StockItem için barcode zorunlu
        $barcode = trim($row['ÜRÜN KODU'] ?? '');
        if (empty($barcode)) {
            $this->skippedRows[] = [
                'row' => $row,
                'reason' => 'ÜRÜN KODU (barcode) boş - StockItem için barcode zorunludur',
            ];
            return null;
        }

        // Ürün eşleştirmesi - ÜRÜN KODU, ÜRÜN SKU veya ÜRÜN'den en az biri olmalı
        $hasProductInfo = !empty(trim($row['ÜRÜN KODU'] ?? '')) 
            || !empty(trim($row['ÜRÜN SKU'] ?? '')) 
            || !empty(trim($row['ÜRÜN'] ?? ''));
        
        if (!$hasProductInfo) {
            $this->skippedRows[] = [
                'row' => $row,
                'reason' => 'ÜRÜN KODU, ÜRÜN SKU veya ÜRÜN alanlarından en az biri dolu olmalıdır',
            ];
            return null;
        }

        $product = $this->findProduct($row);
        if (!$product) {
            $this->skippedRows[] = [
                'row' => $row,
                'reason' => 'Ürün bulunamadı (ÜRÜN KODU, ÜRÜN SKU veya ÜRÜN ile eşleştirilemedi)',
            ];
            return null;
        }

        // Bayi eşleştirmesi - BAYİ KODU veya BAYİ'den en az biri olmalı
        $hasDealerInfo = !empty(trim($row['BAYİ KODU'] ?? '')) 
            || !empty(trim($row['BAYİ'] ?? ''));
        
        if (!$hasDealerInfo) {
            $this->skippedRows[] = [
                'row' => $row,
                'reason' => 'BAYİ KODU veya BAYİ alanlarından en az biri dolu olmalıdır',
            ];
            return null;
        }

        $dealer = $this->findDealer($row);
        if (!$dealer) {
            $this->skippedRows[] = [
                'row' => $row,
                'reason' => 'Bayi bulunamadı (BAYİ KODU veya BAYİ ile eşleştirilemedi)',
            ];
            return null;
        }

        // Mevcut StockItem'ı kontrol et
        $stockItem = StockItem::where('barcode', $barcode)->first();

        $user = Auth::user();

        if ($stockItem) {
            // Mevcut StockItem'ı güncelle
            $stockItem->update([
                'product_id' => $product->id,
                'dealer_id' => $dealer->id,
                'sku' => $product->sku,
                'location' => StockLocationEnum::CENTER->value,
                'status' => StockStatusEnum::AVAILABLE->value,
            ]);

            // StockMovement logu oluştur
            StockMovement::create([
                'stock_item_id' => $stockItem->id,
                'user_id' => $user?->id,
                'action' => StockMovementActionEnum::IMPORTED->value,
                'description' => "Excel import ile güncellendi - Ürün: {$product->name}, Bayi: {$dealer->name}",
                'created_at' => now(),
            ]);

            $this->updatedCount++;
        } else {
            // Yeni StockItem oluştur
            $stockItem = StockItem::create([
                'product_id' => $product->id,
                'dealer_id' => $dealer->id,
                'sku' => $product->sku,
                'barcode' => $barcode,
                'location' => StockLocationEnum::CENTER->value,
                'status' => StockStatusEnum::AVAILABLE->value,
            ]);

            // StockMovement logu oluştur
            StockMovement::create([
                'stock_item_id' => $stockItem->id,
                'user_id' => $user?->id,
                'action' => StockMovementActionEnum::IMPORTED->value,
                'description' => "Excel import ile oluşturuldu - Ürün: {$product->name}, Bayi: {$dealer->name}",
                'created_at' => now(),
            ]);

            $this->createdCount++;
        }

        // Order oluşturma için sakla
        $this->importedStockItems[] = [
            'stock_item' => $stockItem,
            'dealer_id' => $dealer->id,
            'product_id' => $product->id,
        ];

        $this->successCount++;

        return $stockItem;
    }

    /**
     * Collection işlendikten sonra çağrılır
     */
    protected function afterCollection(Collection $collection): void
    {
        // Import edilen StockItem'lar varsa Order oluştur
        if (!empty($this->importedStockItems)) {
            $this->createOrders();
        }

        // Özet mesajı oluştur
        $message = sprintf(
            'Import tamamlandı. Başarılı: %d (Oluşturulan: %d, Güncellenen: %d), Atlanan: %d',
            $this->successCount,
            $this->createdCount,
            $this->updatedCount,
            count($this->skippedRows)
        );

        if (count($this->skippedRows) > 0) {
            $this->stopImportWithWarning($message);
        } else {
            $this->stopImportWithSuccess($message);
        }
    }

    /**
     * Ürün bulma mantığı
     */
    protected function findProduct(array $row): ?Product
    {
        // Önce ÜRÜN SKU ile ara (varsa)
        if (!empty($row['ÜRÜN SKU'] ?? '')) {
            $product = Product::where('sku', trim($row['ÜRÜN SKU']))->first();
            if ($product) {
                return $product;
            }
        }

        // ÜRÜN adı ile ara
        if (!empty($row['ÜRÜN'] ?? '')) {
            $product = Product::where('name', trim($row['ÜRÜN']))->first();
            if ($product) {
                return $product;
            }
        }

        // Varsayılan ürün kullan (customImportData'dan)
        if (!empty($this->customImportData['default_product_id'] ?? null)) {
            $product = Product::find($this->customImportData['default_product_id']);
            if ($product) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Bayi bulma mantığı
     */
    protected function findDealer(array $row): ?Dealer
    {
        // Önce BAYİ KODU ile ara (varsa)
        if (!empty($row['BAYİ KODU'] ?? '')) {
            $dealer = Dealer::where('dealer_code', trim($row['BAYİ KODU']))->first();
            if ($dealer) {
                return $dealer;
            }
        }

        // BAYİ adı ile ara
        if (!empty($row['BAYİ'] ?? '')) {
            $dealer = Dealer::where('name', trim($row['BAYİ']))->first();
            if ($dealer) {
                return $dealer;
            }
        }

        // Varsayılan bayi kullan (customImportData'dan)
        if (!empty($this->customImportData['default_dealer_id'] ?? null)) {
            $dealer = Dealer::find($this->customImportData['default_dealer_id']);
            if ($dealer) {
                return $dealer;
            }
        }

        return null;
    }

    /**
     * Import edilen StockItem'lar için Order oluştur
     */
    protected function createOrders(): void
    {
        // Bayi bazında grupla
        $groupedByDealer = collect($this->importedStockItems)->groupBy('dealer_id');

        DB::transaction(function () use ($groupedByDealer) {
            foreach ($groupedByDealer as $dealerId => $items) {
                $dealer = Dealer::find($dealerId);
                if (!$dealer) {
                    continue;
                }

                // Order oluştur (DELIVERED status ile)
                $order = \App\Models\Order::create([
                    'dealer_id' => $dealerId,
                    'created_by' => Auth::id(),
                    'status' => \App\Enums\OrderStatusEnum::DELIVERED->value,
                    'notes' => 'Excel import ile otomatik oluşturuldu',
                ]);

                // Product bazında grupla
                $groupedByProduct = collect($items)->groupBy('product_id');

                foreach ($groupedByProduct as $productId => $productItems) {
                    // StockItem ID'lerini al
                    $stockItemIds = collect($productItems)->pluck('stock_item.id')->toArray();

                    // OrderItem oluştur
                    $orderItem = \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'quantity' => count($productItems),
                    ]);

                    // StockItem'ları attach et
                    $orderItem->stockItems()->attach($stockItemIds);

                    // OrderItem'ı refresh et ki stockItems yüklü olsun
                    $orderItem->refresh();
                    $orderItem->load('stockItems');

                    // Order'ı refresh et ki status güncel olsun
                    $order->refresh();
                    $order->load('items.stockItems');

                    // OrderItemCreated event'i zaten OrderItemObserver tarafından fırlatıldı
                    // Ancak StockItem'lar attach edilmeden önce fırlatılmış olabilir
                    // Bu yüzden event'i tekrar fırlatıyoruz
                    event(new OrderItemCreated($orderItem));
                }
            }
        });
    }

    /**
     * Import edilen StockItem'ları döndür (test için)
     */
    public function getImportedStockItems(): array
    {
        return $this->importedStockItems;
    }

    /**
     * Atlanan satırları döndür
     */
    public function getSkippedRows(): array
    {
        return $this->skippedRows;
    }
}

