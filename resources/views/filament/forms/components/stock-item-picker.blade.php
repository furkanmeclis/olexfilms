@php
    use App\Enums\CarPartEnum;
    use App\Enums\StockStatusEnum;
    use App\Models\StockItem;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Filament\Schemas\Components\Utilities\Get;

    $statePath = $getStatePath();
    $selectedStockItemId = $getState();
    $isDisabled = $isDisabled();

    // State erişimi - component metodlarını kullan
    $appliedParts = $field->getAppliedParts($get) ?? [];
    $dealerId = $field->getDealerId($get);

    // Eğer dealerId yoksa, kullanıcının dealer_id'sini kullan
$user = Auth::user();
if (!$dealerId) {
    $dealerId = $user?->dealer_id;
}

// Record'dan dealer_id al (edit sayfası için)
    $record = $field->getRecord();
    if (!$dealerId && $record) {
        $dealerId = $record->dealer_id ?? null;
    }

    // Stok ürünlerini filtrele - Optimize edilmiş query
    $stockItemsQuery = StockItem::query()
        ->where('status', StockStatusEnum::AVAILABLE->value)
        ->with(['product.category', 'product'])
        ->when(
            $dealerId,
            function ($q) use ($dealerId) {
                // Dealer ID varsa, SADECE o dealer'a ait stokları göster
            // Merkez stokları (dealer_id = NULL) dahil edilmemeli
            // Hem dealer kullanıcıları hem de admin (seçilen dealer için) için geçerli
            $q->where('dealer_id', $dealerId);
        },
        function ($q) {
            // Dealer ID yoksa, sadece NULL (merkez stoku) olanları göster
            // Bu durum normalde olmamalı çünkü dealer kullanıcılarının dealer_id'si olmalı
                // Admin ise mutlaka bir dealer seçmeli
                $q->whereNull('dealer_id');
            },
        );
    /*
    if (!empty($appliedParts) && is_array($appliedParts)) {
        $stockItemsQuery->whereHas('product.category', function ($q) use ($appliedParts) {
            $dbDriver = DB::getDriverName();

            if ($dbDriver === 'sqlite') {
                // SQLite için JSON array içinde arama
                // SQLite'da JSON array'ler string olarak saklanır
                // Format: "[\"part1\",\"part2\"]" - escape karakterleri ile saklanır ama LIKE sorgusu escape olmadan çalışır
                $q->where(function ($subQuery) use ($appliedParts) {
                    foreach ($appliedParts as $part) {
                        // JSON array formatında arama: part adını direkt aramak yeterli
                        // SQLite LIKE sorgusu escape karakterlerini dikkate almaz
                        $subQuery->orWhere('available_parts', 'like', '%' . $part . '%');
                    }
                });
            } else {
                $q->where(function ($subQuery) use ($appliedParts) {
                    foreach ($appliedParts as $part) {
                        $subQuery->orWhereJsonContains('available_parts', $part);
                    }
                });
            }
        });
    } else {
        // Eğer applied_parts seçilmemişse boş liste
        $stockItemsQuery->whereRaw('1 = 0');
    }
    */
    // Debug bilgileri hazırla (query execute edilmeden önce)
    $debugQuery = clone $stockItemsQuery;
    $debugInfo = [
        'appliedParts' => $appliedParts,
        'appliedPartsCount' => is_array($appliedParts) ? count($appliedParts) : 0,
        'dealerId' => $dealerId,
        'hasAppliedParts' => !empty($appliedParts) && is_array($appliedParts) && count($appliedParts) > 0,
        'querySql' => $debugQuery->toSql(),
        'queryBindings' => $debugQuery->getBindings(),
    ];

    $stockItems = $stockItemsQuery->get();
    $selectedStockItem = $selectedStockItemId
        ? StockItem::with(['product.category', 'product'])->find($selectedStockItemId)
        : null;

    // Stock items count'u debug info'ya ekle
    $debugInfo['stockItemsCount'] = $stockItems->count();

    $partLabels = CarPartEnum::getLabels();
    $fallbackImage = asset('images/default-product.png');

    // Stock items'ı map et ve available_parts_labels ekle
$mappedStockItems = $stockItems
    ->map(function ($item) use ($partLabels, $fallbackImage) {
        $availableParts = $item->product?->category?->available_parts ?? [];

        // Eğer string ise (JSON), decode et
        if (is_string($availableParts)) {
            $availableParts = json_decode($availableParts, true) ?? [];
        }

        // Eğer array değilse boş array yap
        if (!is_array($availableParts)) {
            $availableParts = [];
        }

        $availablePartsLabels = [];

        foreach ($availableParts as $part) {
            $availablePartsLabels[] = $partLabels[$part] ?? $part;
        }

        $productImagePath = $item->product?->image_path;
        $imagePath = $productImagePath
            ? (str_starts_with($productImagePath, 'storage/')
                ? asset($productImagePath)
                : asset('storage/' . $productImagePath))
            : $fallbackImage;

        return [
            'id' => $item->id,
            'barcode' => $item->barcode,
            'product_name' => $item->product?->name ?? '',
            'category_name' => $item->product?->category?->name ?? '',
            'image_path' => $imagePath,
            'available_parts' => $availableParts,
            'available_parts_labels' => $availablePartsLabels,
            ];
        })
        ->toArray();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        open: false,
        selectedId: @js($selectedStockItemId),
        isDisabled: @js($isDisabled),
        stockItems: @js($mappedStockItems) || [],
        appliedParts: @js($appliedParts) || [],
        searchQuery: '',
        barcodeInput: '',
        isScanning: false,
        scanner: null,
        isLoading: false,
        viewMode: 'list', // 'grid' | 'list'
    
        init() {
            // Livewire state değişikliklerini dinle
            $wire.watch('{{ $statePath }}', (value) => {
                this.selectedId = value;
            });
    
            // Wizard state değişikliklerini dinle (applied_parts veya dealer_id değiştiğinde)
            this.$watch('stockItems', () => {
                // Stock items güncellendiğinde modal'ı kapat
                if (this.open && this.stockItems.length === 0) {
                    this.open = false;
                }
            });
    
            // Wizard step değişikliklerini ve applied_parts değişikliklerini dinle
            const self = this;
            if (typeof $wire !== 'undefined' && $wire.watch) {
                try {
                    // Applied parts değişikliklerini dinle (wizard'da kritik)
                    $wire.watch('data.applied_parts', (newParts, oldParts) => {
                        // Applied parts değiştiyse ve yeni parts boş değilse
                        const newPartsStr = Array.isArray(newParts) ? JSON.stringify(newParts) : newParts;
                        const oldPartsStr = Array.isArray(oldParts) ? JSON.stringify(oldParts) : oldParts;
    
                        if (newPartsStr !== oldPartsStr && newParts && (Array.isArray(newParts) ? newParts.length > 0 : true)) {
                            // Component'i yeniden yükle (stok listesini güncellemek için)
                            setTimeout(() => {
                                $wire.$refresh();
                            }, 100);
                        }
                    });
    
                    // Dealer ID değişikliklerini dinle (admin için kritik)
                    $wire.watch('data.dealer_id', (newDealerId, oldDealerId) => {
                        // Dealer değiştiyse ve yeni dealer null değilse
                        if (newDealerId !== oldDealerId && newDealerId !== null) {
                            // Seçili stok'u temizle
                            self.selectedId = null;
                            $wire.set('{{ $statePath }}', null, false);
    
                            // Component'i yeniden yükle (stok listesini güncellemek için)
                            setTimeout(() => {
                                $wire.$refresh();
                            }, 100);
                        }
                    });
                } catch (e) {
                    // Watch başarısız olursa devam et
                }
            }
    
            // Wizard step değişikliklerini dinle (Alpine.js event)
            // Filament wizard step değiştiğinde event dispatch eder
            window.addEventListener('wizard-step-changed', () => {
                setTimeout(() => {
                    if (typeof $wire !== 'undefined') {
                        $wire.$refresh();
                    }
                }, 200);
            });
        },
    
        get filteredStockItems() {
            const items = this.stockItems || [];
            if (!this.searchQuery && !this.barcodeInput) {
                return items;
            }
    
            const query = (this.searchQuery || this.barcodeInput).toLowerCase();
            return items.filter(item => {
                if (!item) return false;
                return (
                    (item.product_name || '').toLowerCase().includes(query) ||
                    (item.barcode || '').toLowerCase().includes(query) ||
                    (item.category_name || '').toLowerCase().includes(query) ||
                    (item.available_parts_labels || []).some(label => (label || '').toLowerCase().includes(query))
                );
            });
        },
    
        toggleViewMode() {
            this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
        },
    
        getFilteredPartsLabels(item, maxVisible = 3) {
            if (!item || !item.available_parts || !item.available_parts_labels) {
                return { visible: [], remaining: 0 };
            }
    
            // Applied parts varsa, sadece onlara ait label'ları göster
            let filteredLabels = item.available_parts_labels;
            if (this.appliedParts && Array.isArray(this.appliedParts) && this.appliedParts.length > 0) {
                // available_parts (değerler) ile appliedParts'ı karşılaştır
                // Eşleşen index'lerdeki label'ları al
                filteredLabels = item.available_parts
                    .map((part, index) => {
                        if (this.appliedParts.includes(part)) {
                            return item.available_parts_labels[index];
                        }
                        return null;
                    })
                    .filter(label => label !== null);
            }
    
            return {
                visible: filteredLabels.slice(0, maxVisible),
                remaining: Math.max(0, filteredLabels.length - maxVisible),
                all: filteredLabels
            };
        },
    
        selectStockItem(id) {
            if (this.isDisabled) return;
            this.selectedId = id;
            $wire.set('{{ $statePath }}', id, false);
            this.open = false;
            this.searchQuery = '';
            this.barcodeInput = '';
        },
    
        getSelectedStockItem() {
            if (!this.selectedId) return null;
            const items = this.stockItems || [];
            return items.find(item => item && item.id === this.selectedId);
        },
    
        async startQRScan() {
            if (this.isDisabled) return;
            if (!('BarcodeDetector' in window) && typeof Html5Qrcode === 'undefined') {
                alert('QR kod okutma özelliği bu tarayıcıda desteklenmiyor. Lütfen modern bir tarayıcı kullanın.');
                return;
            }
    
            this.isScanning = true;
    
            try {
                // Kamera izni al
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
    
                // QR kod okutma başlat
                if (typeof Html5Qrcode !== 'undefined') {
                    const qrCodeScanner = new Html5Qrcode('qr-reader-stock');
    
                    await qrCodeScanner.start({ facingMode: 'environment' }, {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        (decodedText) => {
                            this.handleBarcode(decodedText);
                            qrCodeScanner.stop();
                            this.isScanning = false;
                        },
                        (errorMessage) => {
                            // Hata mesajını görmezden gel
                        }
                    );
    
                    this.scanner = qrCodeScanner;
                } else {
                    // BarcodeDetector API kullan
                    const video = document.createElement('video');
                    video.srcObject = stream;
                    video.play();
    
                    const detector = new BarcodeDetector({ formats: ['aztec', 'code_128', 'code_39', 'code_93', 'data_matrix', 'ean_13', 'ean_8', 'itf', 'pdf417', 'qr_code', 'upc_e'] });
    
                    const detectBarcode = async () => {
                        try {
                            const barcodes = await detector.detect(video);
                            if (barcodes.length > 0) {
                                this.handleBarcode(barcodes[0].rawValue);
                                stream.getTracks().forEach(track => track.stop());
                                this.isScanning = false;
                                return;
                            }
                        } catch (e) {
                            // Hata görmezden gel
                        }
    
                        if (this.isScanning) {
                            requestAnimationFrame(detectBarcode);
                        }
                    };
    
                    detectBarcode();
                }
            } catch (error) {
                alert('Kamera erişimi reddedildi veya bir hata oluştu.');
                this.isScanning = false;
            }
        },
    
        stopQRScan() {
            if (this.scanner) {
                this.scanner.stop();
                this.scanner = null;
            }
            this.isScanning = false;
        },
    
        handleBarcode(barcodeText) {
            // Barkod metnini temizle
            const barcode = barcodeText.trim();
            this.barcodeInput = barcode;
            this.searchQuery = barcode;
    
            // Eğer direkt eşleşen bir stok varsa seç
            const matchingItem = this.stockItems.find(item =>
                item && item.barcode && item.barcode.toLowerCase() === barcode.toLowerCase()
            );
    
            if (matchingItem) {
                this.selectStockItem(matchingItem.id);
            } else {
                // Eşleşme yoksa arama sonuçlarını göster
                this.open = true;
            }
        },
    
        searchByBarcode() {
            if (!this.barcodeInput.trim()) return;
            this.handleBarcode(this.barcodeInput);
        }
    }"
        @stock-item-picker:updated.window="
            // Component state güncellendiğinde stock items'ı yenile
            $wire.$refresh()
        "
        @stock-item-picker:dealer-changed.window="
            // Dealer ID değiştiğinde component'i yeniden yükle
            // Bu, admin için kritik - seçilen bayiye ait stoklar gösterilmeli
            selectedId = null;
            $wire.set('{{ $statePath }}', null, false);
            $wire.$refresh();
        ">
        <!-- Seçim Butonu -->
        <div class="space-y-2 sm:space-y-2">
            <div class="flex flex-col sm:flex-row gap-2">
                <button type="button" @click="!isDisabled && (open = true)"
                    :disabled="isDisabled || stockItems.length === 0"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 sm:px-4 py-2.5 sm:py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span x-text="selectedId ? 'Stok Değiştir' : 'Stok Seç'"></span>
                </button>

                <button type="button" @click="isScanning ? stopQRScan() : startQRScan()" :disabled="isDisabled"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 sm:px-4 py-2.5 sm:py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation"
                    title="Barkod/QR Okut">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2.01M19 8h2M5 20h2m-2-4h2m4-8h2m-2 4h2m-2 4h2">
                        </path>
                    </svg>
                    <span class="hidden sm:inline" x-text="isScanning ? 'Durdur' : 'QR Okut'"></span>
                    <span class="sm:hidden" x-text="isScanning ? 'Durdur' : 'QR'"></span>
                </button>
            </div>

            <!-- Barkod Arama -->
            <div class="flex gap-2">
                <input type="text" x-model="barcodeInput" @keyup.enter="searchByBarcode()"
                    placeholder="Barkod ile ara..." :disabled="isDisabled"
                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2.5 sm:py-2 text-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation">
                <button type="button" @click="searchByBarcode()" :disabled="isDisabled || !barcodeInput.trim()"
                    class="inline-flex items-center justify-center gap-1 sm:gap-2 rounded-lg bg-primary-600 px-3 sm:px-4 py-2.5 sm:py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">Ara</span>
                </button>
            </div>

            <!-- Seçili Stok Bilgisi -->
            <div x-show="selectedId && getSelectedStockItem()" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                class="rounded-lg border border-gray-200 bg-gray-50 p-3 sm:p-4">
                <div class="flex items-start gap-3 sm:gap-4">
                    <img :src="getSelectedStockItem()?.image_path || '{{ $fallbackImage }}'"
                        :alt="getSelectedStockItem()?.product_name"
                        class="h-16 w-16 sm:h-20 sm:w-20 rounded-lg object-cover shrink-0"
                        onerror="this.src='{{ $fallbackImage }}'">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-sm sm:text-base text-gray-900 mb-1 line-clamp-2"
                            x-text="getSelectedStockItem()?.product_name" :title="getSelectedStockItem()?.product_name">
                        </h4>
                        <p class="text-xs sm:text-sm text-gray-600 truncate"
                            x-text="'Barkod: ' + getSelectedStockItem()?.barcode"></p>
                        <p class="text-xs sm:text-sm text-gray-600 truncate"
                            x-text="'Kategori: ' + getSelectedStockItem()?.category_name">
                        </p>
                        <div class="mt-2 hidden" x-data="{ parts: getFilteredPartsLabels(getSelectedStockItem(), 5) }">
                            <p class="text-xs font-medium text-gray-700 mb-1">Uygulanabilir Alanlar:</p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="(part, index) in parts.visible" :key="index">
                                    <span
                                        class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 max-w-full truncate"
                                        :title="part" x-text="part"></span>
                                </template>
                                <span x-show="parts.remaining > 0"
                                    class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800"
                                    :title="parts.all?.slice(5).join(', ') || ''"
                                    x-text="'+' + parts.remaining + ' daha'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Uyarı Mesajları -->
            <div x-show="stockItems.length === 0"
                class="text-xs sm:text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-lg p-3 sm:p-3 space-y-2">
                <p class="font-medium">
                    Seçilen uygulama alanlarına uygun stok bulunamadı. Lütfen önce uygulama alanlarını seçin.
                </p>
                <details class="text-xs text-amber-700 hidden">
                    <summary class="cursor-pointer font-medium hover:text-amber-800 touch-manipulation">🔍 Debug
                        Bilgileri</summary>
                    <div
                        class="mt-2 space-y-1 font-mono bg-amber-100 p-2 rounded border border-amber-300 text-xs overflow-x-auto">
                        <div><strong>Uygulama Alanları:</strong>
                            <span x-text="JSON.stringify(@js($appliedParts) || [])"></span>
                            <span class="text-gray-600">({{ $debugInfo['appliedPartsCount'] }} adet)</span>
                        </div>
                        <div><strong>Bayi ID:</strong>
                            <span x-text="@js($dealerId) || 'null'"></span>
                        </div>
                        <div><strong>Stok Sayısı:</strong>
                            <span x-text="stockItems.length"></span>
                        </div>
                        <div><strong>Uygulama Alanları Seçili mi:</strong>
                            <span x-text="{{ $debugInfo['hasAppliedParts'] ? 'true' : 'false' }}"></span>
                        </div>
                        @if (config('app.debug'))
                            <div class="mt-2 pt-2 border-t border-amber-300">
                                <div><strong>SQL Sorgusu:</strong> <code
                                        class="text-xs break-all">{{ $debugInfo['querySql'] }}</code></div>
                                <div class="mt-1"><strong>Bindings:</strong> <code
                                        class="text-xs break-all">{{ json_encode($debugInfo['queryBindings']) }}</code>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open && !isDisabled" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="!isDisabled && (open = false)"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-2 sm:p-4" style="display: none;">
            <div @click.stop x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative w-full h-full sm:h-auto sm:max-h-[90vh] sm:max-w-4xl overflow-hidden rounded-none sm:rounded-lg bg-white shadow-xl flex flex-col">
                <!-- Modal Header -->
                <div class="border-b border-gray-200 px-4 sm:px-6 py-3 sm:py-4 shrink-0">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Stok Ürünü Seç</h3>
                        <div class="flex items-center gap-2">
                            <!-- View Toggle Button -->
                            <button @click="toggleViewMode()" type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                                :title="viewMode === 'grid' ? 'Liste Görünümü' : 'Grid Görünümü'">
                                <svg x-show="viewMode === 'grid'" class="h-5 w-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                <svg x-show="viewMode === 'list'" class="h-5 w-5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                    </path>
                                </svg>
                            </button>
                            <button @click="!isDisabled && (open = false)" :disabled="isDisabled" type="button"
                                class="text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Arama Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery"
                            placeholder="Ürün adı, barkod, kategori veya uygulama alanı ile ara..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="overflow-y-auto flex-1 p-4 sm:p-6">
                    <!-- Grid View -->
                    <div x-show="viewMode === 'grid'"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <template x-for="item in filteredStockItems" :key="item.id">
                            <div @click="!isDisabled && selectStockItem(item.id)"
                                class="group relative rounded-lg border-2 p-3 sm:p-4 transition-all cursor-pointer hover:border-primary-500 hover:shadow-md"
                                :class="isDisabled ? 'cursor-not-allowed opacity-50' : ''"
                                :class="selectedId === item.id ? 'border-primary-500 bg-primary-50' : 'border-gray-200 bg-white'">
                                <!-- Ürün Resmi -->
                                <div class="mb-3 aspect-square overflow-hidden rounded-lg bg-gray-100">
                                    <img :src="item.image_path" :alt="item.product_name"
                                        class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                        onerror="this.src='{{ $fallbackImage }}'">
                                </div>

                                <!-- Ürün Bilgileri -->
                                <h4 class="mb-1 font-semibold text-sm sm:text-base text-gray-900 line-clamp-2"
                                    x-text="item.product_name" :title="item.product_name"></h4>
                                <p class="mb-1 text-xs sm:text-sm text-gray-600 truncate"
                                    x-text="'Barkod: ' + item.barcode"></p>
                                <p class="mb-2 text-xs sm:text-sm text-gray-600 truncate"
                                    x-text="'Kategori: ' + item.category_name"></p>

                                <!-- Uygulanabilir Alanlar -->
                                <div class="mt-2 hidden">
                                    <p class="mb-1 text-xs font-medium text-gray-700">Uygulanabilir Alanlar:</p>
                                    <div class="flex flex-wrap gap-1" x-data="{ parts: getFilteredPartsLabels(item, 3) }">
                                        <template x-for="(part, index) in parts.visible"
                                            :key="`part-${item.id}-${index}`">
                                            <span
                                                class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 max-w-full truncate"
                                                :title="part" x-text="part"></span>
                                        </template>
                                        <span x-show="parts.remaining > 0"
                                            class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800"
                                            :title="parts.all?.slice(3).join(', ') || ''"
                                            x-text="'+' + parts.remaining + ' daha'"></span>
                                    </div>
                                </div>

                                <!-- Seçim İşareti -->
                                <div x-show="selectedId === item.id" x-transition
                                    class="absolute top-2 right-2 rounded-full bg-primary-500 p-1">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- List View -->
                    <div x-show="viewMode === 'list'" class="space-y-2 sm:space-y-3">
                        <template x-for="item in filteredStockItems" :key="item.id">
                            <div @click="!isDisabled && selectStockItem(item.id)"
                                class="group relative flex items-center gap-3 sm:gap-4 rounded-lg border-2 p-3 sm:p-4 transition-all cursor-pointer hover:border-primary-500 hover:shadow-md bg-white"
                                :class="isDisabled ? 'cursor-not-allowed opacity-50' : ''"
                                :class="selectedId === item.id ? 'border-primary-500 bg-primary-50' : 'border-gray-200'">
                                <!-- Ürün Resmi -->
                                <div class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 overflow-hidden rounded-lg bg-gray-100">
                                    <img :src="item.image_path" :alt="item.product_name"
                                        class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                        onerror="this.src='{{ $fallbackImage }}'">
                                </div>

                                <!-- Ürün Bilgileri -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-sm sm:text-base text-gray-900 mb-1 line-clamp-1"
                                        x-text="item.product_name" :title="item.product_name"></h4>
                                    <div
                                        class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm text-gray-600 mb-2">
                                        <p class="truncate" x-text="'Barkod: ' + item.barcode"></p>
                                        <span class="text-gray-300">•</span>
                                        <p class="truncate" x-text="'Kategori: ' + item.category_name"></p>
                                    </div>

                                    <!-- Uygulanabilir Alanlar -->
                                    <div class="flex flex-wrap gap-1 hidden" x-data="{ parts: getFilteredPartsLabels(item, 4) }">
                                        <template x-for="(part, index) in parts.visible"
                                            :key="`list-part-${item.id}-${index}`">
                                            <span
                                                class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 max-w-full truncate"
                                                :title="part" x-text="part"></span>
                                        </template>
                                        <span x-show="parts.remaining > 0"
                                            class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800"
                                            :title="parts.all?.slice(4).join(', ') || ''"
                                            x-text="'+' + parts.remaining + ' daha'"></span>
                                    </div>
                                </div>

                                <!-- Seçim İşareti -->
                                <div x-show="selectedId === item.id" x-transition class="shrink-0">
                                    <div class="rounded-full bg-primary-500 p-1.5">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="stockItems.length === 0" class="text-center text-gray-500 py-6 sm:py-8 space-y-2">
                        <p class="font-medium text-sm sm:text-base">
                            Seçilen uygulama alanlarına uygun stok bulunamadı.
                        </p>
                        <details class="text-xs text-gray-600 inline-block">
                            <summary class="cursor-pointer font-medium hover:text-gray-700 touch-manipulation">🔍 Debug
                                Bilgileri
                            </summary>
                            <div
                                class="mt-2 text-left space-y-1 font-mono bg-gray-100 p-2 rounded border border-gray-300 max-w-md mx-auto overflow-x-auto">
                                <div><strong>Uygulama Alanları:</strong>
                                    <span x-text="JSON.stringify(@js($appliedParts) || [])"></span>
                                    <span class="text-gray-500">({{ $debugInfo['appliedPartsCount'] }} adet)</span>
                                </div>
                                <div><strong>Bayi ID:</strong>
                                    <span x-text="@js($dealerId) || 'null'"></span>
                                </div>
                                <div><strong>Stok Sayısı:</strong>
                                    <span x-text="stockItems.length"></span>
                                </div>
                                <div><strong>Uygulama Alanları Seçili mi:</strong>
                                    <span x-text="{{ $debugInfo['hasAppliedParts'] ? 'true' : 'false' }}"></span>
                                </div>
                                @if (config('app.debug'))
                                    <div class="mt-2 pt-2 border-t border-gray-300">
                                        <div><strong>SQL Sorgusu:</strong> <code
                                                class="text-xs break-all">{{ $debugInfo['querySql'] }}</code></div>
                                        <div class="mt-1"><strong>Bindings:</strong> <code
                                                class="text-xs break-all">{{ json_encode($debugInfo['queryBindings']) }}</code>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </details>
                    </div>

                    <p x-show="stockItems.length > 0 && filteredStockItems.length === 0"
                        class="text-center text-sm sm:text-base text-gray-500 py-6 sm:py-8">
                        Arama kriterlerinize uygun stok bulunamadı.
                    </p>
                </div>
            </div>
        </div>

        <!-- QR Scanner -->
        <div x-show="isScanning" x-cloak
            class="fixed z-[60] inset-0 flex items-center justify-center bg-black/50 p-2 sm:p-4"
            style="display: none;">
            <div
                class="relative w-full max-w-md bg-white rounded-lg sm:rounded-lg shadow-xl p-4 sm:p-6 flex flex-col max-h-[95vh]">
                <div class="flex items-center justify-between mb-3 sm:mb-4 shrink-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Barkod/QR Okut</h3>
                    <button @click="stopQRScan()" type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors touch-manipulation">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="qr-reader-stock" class="w-full flex-1 min-h-0"></div>
                <p class="text-xs sm:text-sm text-gray-600 text-center mt-2 sm:mt-3 shrink-0">Barkod veya QR kodu
                    kameraya gösterin</p>
            </div>
        </div>
    </div>

    <!-- Html5Qrcode CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</x-dynamic-component>
