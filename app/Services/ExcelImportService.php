<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    private ?Product $defaultProduct = null;
    private ?Dealer $defaultDealer = null;
    private int $successCount = 0;
    private int $errorCount = 0;
    private array $errors = [];

    public function __construct(?int $defaultProductId = null, ?int $defaultDealerId = null)
    {
        if ($defaultProductId) {
            $this->defaultProduct = Product::find($defaultProductId);
        }
        if ($defaultDealerId) {
            $this->defaultDealer = Dealer::find($defaultDealerId);
        }
    }

    /**
     * Excel verilerini parse et ve eşleştirmeleri yap (sadece preview için, import yapmaz)
     */
    public function parseAndMatch(Collection $rows): array
    {
        // İlk satır header olmalı
        $headerRow = $rows->first();
        if (empty($headerRow)) {
            return [
                'success' => false,
                'error' => 'Excel dosyası boş veya geçersiz.',
                'rows' => [],
            ];
        }

        // Header satırından kolon indekslerini bul
        $columns = $this->findColumns($headerRow);
        if (! $columns) {
            return [
                'success' => false,
                'error' => 'Excel dosyasında gerekli kolonlar bulunamadı (ÜRÜN KODU veya ÜRÜN zorunludur).',
                'rows' => [],
            ];
        }

        // Data satırlarını parse et
        $dataRows = $rows->skip(1);
        $parsedRows = [];
        foreach ($dataRows as $index => $row) {
            $rowIndex = $index + 2; // Excel satır numarası (header hariç)

            // Boş satırları atla
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $parsedRow = $this->parseRow($row, $rowIndex, $columns);
            if ($parsedRow) {
                // Ürün eşleştirmesi
                $product = $this->matchProduct($parsedRow);
                $parsedRow['matched_product'] = $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ] : null;
                $parsedRow['product_match_status'] = $product ? 'matched' : 'not_matched';

                // Bayi eşleştirmesi
                $dealer = $this->matchDealer($parsedRow);
                $parsedRow['matched_dealer'] = $dealer ? [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'dealer_code' => $dealer->dealer_code,
                ] : null;
                $parsedRow['dealer_match_status'] = $dealer ? 'matched' : 'not_matched';

                // Validation
                $barcode = trim((string) ($parsedRow['urun_kodu'] ?? ''));
                $parsedRow['validation_errors'] = [];
                if (empty($barcode)) {
                    $parsedRow['validation_errors'][] = 'Ürün kodu (barcode) zorunludur';
                }
                if (! $product) {
                    $parsedRow['validation_errors'][] = 'Ürün bulunamadı';
                }
                if (! $dealer) {
                    $parsedRow['validation_errors'][] = 'Bayi bulunamadı';
                }

                $parsedRows[] = $parsedRow;
            }
        }

        // İstatistikler
        $totalRows = count($parsedRows);
        $matchedProducts = collect($parsedRows)->filter(fn($row) => $row['product_match_status'] === 'matched')->count();
        $matchedDealers = collect($parsedRows)->filter(fn($row) => $row['dealer_match_status'] === 'matched')->count();
        $validRows = collect($parsedRows)->filter(fn($row) => empty($row['validation_errors']))->count();

        // Bayi bazında gruplama (sipariş sayısı için)
        $dealersCount = collect($parsedRows)
            ->filter(fn($row) => $row['dealer_match_status'] === 'matched')
            ->pluck('matched_dealer.id')
            ->unique()
            ->count();

        return [
            'success' => true,
            'columns' => $columns,
            'rows' => $parsedRows,
            'statistics' => [
                'total_rows' => $totalRows,
                'matched_products' => $matchedProducts,
                'matched_dealers' => $matchedDealers,
                'valid_rows' => $validRows,
                'estimated_orders' => $dealersCount,
            ],
        ];
    }

    /**
     * Preview'dan gelen matched data'yı işle (tekrar eşleştirme yapmaz)
     */
    public function processPreviewData(array $previewRows, int $userId): array
    {
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->errors = [];

        if (empty($previewRows)) {
            return [
                'success' => false,
                'success_count' => 0,
                'error_count' => 1,
                'errors' => ['Preview verisi boş.'],
                'orders_created' => 0,
                'orders' => [],
            ];
        }

        // StockItem'ları oluştur/güncelle (preview'dan gelen matched data ile)
        $stockItemsByDealer = [];
        foreach ($previewRows as $parsedRow) {
            $result = $this->processStockItem($parsedRow, $userId);
            if ($result['success']) {
                $dealerId = $result['dealer_id'] ?? null;
                if (! isset($stockItemsByDealer[$dealerId])) {
                    $stockItemsByDealer[$dealerId] = [];
                }
                $stockItemsByDealer[$dealerId][] = $result['stock_item'];
                $this->successCount++;
            } else {
                $this->errorCount++;
                $this->errors[] = $result['error'];
            }
        }

        // Order'ları oluştur
        $orders = [];
        foreach ($stockItemsByDealer as $dealerId => $stockItems) {
            if (empty($stockItems)) {
                continue;
            }

            $order = $this->createOrder($dealerId, $stockItems, $userId);
            if ($order) {
                // İlişkileri eager load et
                $order->load(['dealer', 'items.product']);
                $orders[] = $order;
            }
        }

        return [
            'success' => true,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
            'orders_created' => count($orders),
            'orders' => $orders,
        ];
    }

    /**
     * Excel verilerini işle ve StockItem'ları oluştur/güncelle
     */
    public function processExcelData(Collection $rows, int $userId): array
    {
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->errors = [];

        // İlk satır header olmalı
        $headerRow = $rows->first();
        if (empty($headerRow)) {
            return [
                'success' => false,
                'success_count' => 0,
                'error_count' => 1,
                'errors' => ['Excel dosyası boş veya geçersiz.'],
                'orders_created' => 0,
                'orders' => [],
            ];
        }

        // Header satırından kolon indekslerini bul
        $columns = $this->findColumns($headerRow);
        if (! $columns) {
            return [
                'success' => false,
                'success_count' => 0,
                'error_count' => 1,
                'errors' => ['Excel dosyasında gerekli kolonlar bulunamadı (ÜRÜN KODU veya ÜRÜN zorunludur).'],
                'orders_created' => 0,
                'orders' => [],
            ];
        }

        // Data satırlarını parse et
        $dataRows = $rows->skip(1);
        $parsedRows = [];
        foreach ($dataRows as $index => $row) {
            $rowIndex = $index + 2; // Excel satır numarası (header hariç)

            // Boş satırları atla
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $parsedRow = $this->parseRow($row, $rowIndex, $columns);
            if ($parsedRow) {
                $parsedRows[] = $parsedRow;
            }
        }

        // StockItem'ları oluştur/güncelle
        $stockItemsByDealer = [];
        foreach ($parsedRows as $parsedRow) {
            $result = $this->processStockItem($parsedRow, $userId);
            if ($result['success']) {
                $dealerId = $result['dealer_id'] ?? null;
                if (! isset($stockItemsByDealer[$dealerId])) {
                    $stockItemsByDealer[$dealerId] = [];
                }
                $stockItemsByDealer[$dealerId][] = $result['stock_item'];
                $this->successCount++;
            } else {
                $this->errorCount++;
                $this->errors[] = $result['error'];
            }
        }

        // Order'ları oluştur
        $orders = [];
        foreach ($stockItemsByDealer as $dealerId => $stockItems) {
            if (empty($stockItems)) {
                continue;
            }

            $order = $this->createOrder($dealerId, $stockItems, $userId);
            if ($order) {
                // İlişkileri eager load et
                $order->load(['dealer', 'items.product']);
                $orders[] = $order;
            }
        }

        return [
            'success' => true,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
            'orders_created' => count($orders),
            'orders' => $orders,
        ];
    }

    /**
     * Excel satırını parse et
     */
    private function parseRow(array $row, int $rowIndex, array $columns): ?array
    {
        return [
            'row_index' => $rowIndex,
            'tarih' => isset($columns['tarih']) ? ($row[$columns['tarih']] ?? null) : null,
            'kategori' => isset($columns['kategori']) ? ($row[$columns['kategori']] ?? null) : null,
            'marka' => isset($columns['marka']) ? ($row[$columns['marka']] ?? null) : null,
            'urun' => isset($columns['urun']) ? ($row[$columns['urun']] ?? null) : null,
            'urun_kodu' => isset($columns['urun_kodu']) ? ($row[$columns['urun_kodu']] ?? null) : null,
            'urun_sku' => isset($columns['urun_sku']) ? ($row[$columns['urun_sku']] ?? null) : null,
            'bayi' => isset($columns['bayi']) ? ($row[$columns['bayi']] ?? null) : null,
            'bayi_kodu' => isset($columns['bayi_kodu']) ? ($row[$columns['bayi_kodu']] ?? null) : null,
        ];
    }

    /**
     * Excel header satırından kolon indekslerini bul
     */
    private function findColumns(array $row): ?array
    {
        $columns = [];
        
        // Önce spesifik kolonları kontrol et (daha uzun isimler önce)
        // Bu sayede "bayi_kodu" "bayi"den önce, "ürün_kodu" "ürün"den önce eşleşir
        // Sıralama önemli: Daha spesifik olanlar önce kontrol edilmeli
        $possibleNames = [
            'bayi_kodu' => ['bayi_kodu', 'dealer_code', 'bayi kodu', 'dealer code'],
            'urun_kodu' => ['ürün_kodu', 'ürün kodu', 'urun kodu', 'product code', 'barcode', 'barkod'],
            'urun_sku' => ['ürün_sku', 'ürün sku', 'urun sku', 'product sku'],
            'tarih' => ['tarih', 'tari', 'date'],
            'kategori' => ['kategori', 'category'],
            'marka' => ['marka', 'brand'],
            'urun' => ['ürün', 'urun', 'product', 'product_name'],
            'sku' => ['sku'],
            'bayi' => ['bayi', 'dealer', 'bayi adı', 'dealer name'],
        ];

        foreach ($row as $index => $cell) {
            // Excel'den gelen değeri normalize et (trim, lowercase, boşlukları normalize et)
            $cellValue = mb_strtolower(trim((string) $cell));
            // Boşlukları tek boşluğa çevir ve alt çizgileri normalize et
            $cellValue = preg_replace('/\s+/', ' ', $cellValue);
            
            // Her kolon tipi için kontrol et (öncelik sırasına göre)
            foreach ($possibleNames as $key => $names) {
                // Eğer bu kolon zaten eşleşmişse atla
                if (isset($columns[$key])) {
                    continue;
                }
                
                foreach ($names as $name) {
                    // Normalize edilmiş name ile karşılaştır
                    $normalizedName = mb_strtolower(trim($name));
                    
                    // Tam eşleşme kontrol et
                    if ($cellValue === $normalizedName) {
                        $columns[$key] = $index;
                        break 2; // Bu cell için eşleşme bulundu, sonraki cell'e geç
                    }
                }
            }
        }

        // En az ürün kodu veya ürün adı olmalı
        if (! isset($columns['urun_kodu']) && ! isset($columns['urun'])) {
            return null;
        }

        return $columns;
    }

    /**
     * Satırın boş olup olmadığını kontrol et
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (! empty(trim((string) $cell))) {
                return false;
            }
        }
        return true;
    }

    /**
     * StockItem oluştur veya güncelle
     */
    private function processStockItem(array $parsedRow, int $userId): array
    {
        try {
            // Eğer preview'dan geliyorsa (matched_product ve matched_dealer varsa), direkt kullan
            $product = null;
            $dealer = null;

            if (isset($parsedRow['matched_product']) && $parsedRow['matched_product']) {
                // Preview'dan gelen eşleştirmeyi kullan
                $product = Product::find($parsedRow['matched_product']['id']);
            } else {
                // Normal eşleştirme yap
                $product = $this->matchProduct($parsedRow);
            }

            if (! $product) {
                $sku = $parsedRow['urun_sku'] ?? 'N/A';
                $name = $parsedRow['urun'] ?? 'N/A';
                return [
                    'success' => false,
                    'error' => "Satır {$parsedRow['row_index']}: Ürün bulunamadı (SKU: {$sku}, Ad: {$name})",
                ];
            }

            if (isset($parsedRow['matched_dealer']) && $parsedRow['matched_dealer']) {
                // Preview'dan gelen eşleştirmeyi kullan
                $dealer = Dealer::find($parsedRow['matched_dealer']['id']);
            } else {
                // Normal eşleştirme yap
                $dealer = $this->matchDealer($parsedRow);
            }

            if (! $dealer) {
                $code = $parsedRow['bayi_kodu'] ?? 'N/A';
                $dealerName = $parsedRow['bayi'] ?? 'N/A';
                return [
                    'success' => false,
                    'error' => "Satır {$parsedRow['row_index']}: Bayi bulunamadı (Kod: {$code}, Ad: {$dealerName})",
                ];
            }

            // Ürün kodu (barcode) zorunlu
            $barcode = trim((string) ($parsedRow['urun_kodu'] ?? ''));
            if (empty($barcode)) {
                return [
                    'success' => false,
                    'error' => "Satır {$parsedRow['row_index']}: Ürün kodu (barcode) zorunludur",
                ];
            }

            return DB::transaction(function () use ($parsedRow, $product, $dealer, $barcode, $userId) {
                // Mevcut StockItem'ı kontrol et
                $stockItem = StockItem::where('barcode', $barcode)->first();

                if ($stockItem) {
                    // Mevcut StockItem'ı merkeze transfer et (Order oluşturulduktan sonra dealer'a transfer edilecek)
                    $stockItem->transferOwnership(
                        null, // Merkeze transfer
                        $userId,
                        "Excel import ile merkeze transfer edildi (Satır {$parsedRow['row_index']})"
                    );
                    
                    // Status'ü AVAILABLE yap
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);
                } else {
                    // Yeni oluştur
                    $stockItem = StockItem::create([
                        'product_id' => $product->id,
                        'dealer_id' => null, // Order oluşturulduktan sonra set edilecek
                        'sku' => $product->sku,
                        'barcode' => $barcode,
                        'location' => StockLocationEnum::CENTER->value,
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);

                    // StockMovement logu oluştur
                    StockMovement::create([
                        'stock_item_id' => $stockItem->id,
                        'user_id' => $userId,
                        'action' => StockMovementActionEnum::IMPORTED->value,
                        'description' => "Excel import ile oluşturuldu (Satır {$parsedRow['row_index']})",
                        'created_at' => now(),
                    ]);
                }

                return [
                    'success' => true,
                    'stock_item' => $stockItem,
                    'dealer_id' => $dealer->id, // Dealer ID'yi döndür (gruplama için)
                ];
            });
        } catch (\Exception $e) {
            Log::error('Excel import hatası', [
                'row' => $parsedRow,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => "Satır {$parsedRow['row_index']}: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Ürün eşleştirmesi yap
     */
    private function matchProduct(array $parsedRow): ?Product
    {
        // 1. Önce SKU ile ara (urun_sku varsa ve boş değilse)
        if (! empty($parsedRow['urun_sku'])) {
            $sku = trim((string) $parsedRow['urun_sku']);
            if (! empty($sku)) {
                $product = Product::where('sku', $sku)->first();
                if ($product) {
                    return $product;
                }
            }
        }

        // 2. Ürün adı ile ara (urun varsa ve boş değilse)
        if (! empty($parsedRow['urun'])) {
            $name = trim((string) $parsedRow['urun']);
            if (! empty($name)) {
                $product = Product::where('name', $name)->first();
                if ($product) {
                    return $product;
                }
            }
        }

        // 3. Varsayılan ürünü kullan
        if ($this->defaultProduct) {
            return $this->defaultProduct;
        }

        return null;
    }

    /**
     * Bayi eşleştirmesi yap
     */
    private function matchDealer(array $parsedRow): ?Dealer
    {
        // 1. Önce bayi kodu ile ara (bayi_kodu varsa ve boş değilse)
        if (! empty($parsedRow['bayi_kodu'])) {
            $code = trim((string) $parsedRow['bayi_kodu']);
            if (! empty($code)) {
                $dealer = Dealer::where('dealer_code', $code)->first();
                if ($dealer) {
                    return $dealer;
                }
            }
        }

        // 2. Bayi adı ile ara (bayi varsa ve boş değilse)
        if (! empty($parsedRow['bayi'])) {
            $name = trim((string) $parsedRow['bayi']);
            if (! empty($name)) {
                $dealer = Dealer::where('name', $name)->first();
                if ($dealer) {
                    return $dealer;
                }
            }
        }

        // 3. Varsayılan bayiyi kullan
        if ($this->defaultDealer) {
            return $this->defaultDealer;
        }

        return null;
    }

    /**
     * Order oluştur
     */
    private function createOrder(?int $dealerId, array $stockItems, int $userId): ?Order
    {
        if (empty($stockItems)) {
            return null;
        }

        return DB::transaction(function () use ($dealerId, $stockItems, $userId) {
            // Order oluştur
            $order = Order::create([
                'dealer_id' => $dealerId,
                'created_by' => $userId,
                'status' => OrderStatusEnum::DELIVERED->value,
                'notes' => 'Excel import ile otomatik oluşturuldu',
            ]);

            // StockItem'ları product bazında grupla
            $stockItemsByProduct = collect($stockItems)->groupBy('product_id');

            foreach ($stockItemsByProduct as $productId => $productStockItems) {
                // OrderItem oluştur
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => count($productStockItems),
                ]);

                // StockItem'ları OrderItem'a attach et
                $stockItemIds = collect($productStockItems)->pluck('id')->toArray();
                $orderItem->stockItems()->attach($stockItemIds);

                // HandleOrderItemStockAssignment listener otomatik çalışır
                // Order status DELIVERED olduğu için StockItem'lar dealer'a transfer edilir
                foreach ($productStockItems as $stockItem) {
                    $stockItem->transferOwnership(
                        $dealerId,
                        $userId,
                        "Excel import ile dealer'a transfer edildi (Satır {$parsedRow['row_index']})"
                    );
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);
                }
            }

            // Order'ı refresh et ki items yüklü olsun
            $order->refresh();

            return $order;
        });
    }
}

