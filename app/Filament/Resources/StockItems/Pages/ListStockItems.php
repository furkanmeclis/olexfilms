<?php

namespace App\Filament\Resources\StockItems\Pages;

use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\StockItems\StockItemResource;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListStockItems extends ListRecords
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        $actions = [];

        // Sadece admin ve merkez çalışanları hızlı stok girişi yapabilir
        if ($user && $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            $actions[] = Action::make('quickStockEntry')
                ->label('Hızlı Stok Girişi')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Hızlı Stok Girişi')
                ->schema([
                    Select::make('product_id')
                        ->label('Ürün')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Textarea::make('barcodes')
                        ->label('Barkodlar')
                        ->helperText('Her satıra bir barkod girin veya virgülle ayırın')
                        ->required()
                        ->rows(10),
                ])
                ->action(function (array $data) {
                    $product = Product::findOrFail($data['product_id']);
                    $barcodesText = $data['barcodes'];
                    
                    // Barkodları parse et (virgül veya yeni satır ile ayrılmış)
                    $barcodes = preg_split('/[,\n\r]+/', $barcodesText);
                    $barcodes = array_map('trim', $barcodes);
                    $barcodes = array_filter($barcodes); // Boş değerleri temizle

                    $user = Auth::user();
                    $createdCount = 0;
                    $skippedCount = 0;

                    DB::transaction(function () use ($product, $barcodes, $user, &$createdCount, &$skippedCount) {
                        foreach ($barcodes as $barcode) {
                            // Barkod zaten varsa atla
                            if (StockItem::where('barcode', $barcode)->exists()) {
                                $skippedCount++;
                                continue;
                            }

                            // Yeni stok kalemi oluştur
                            $stockItem = StockItem::create([
                                'product_id' => $product->id,
                                'dealer_id' => null, // Merkez
                                'sku' => $product->sku,
                                'barcode' => $barcode,
                                'location' => StockLocationEnum::CENTER->value,
                                'status' => StockStatusEnum::AVAILABLE->value,
                            ]);

                            // Hareket logu oluştur
                            StockMovement::create([
                                'stock_item_id' => $stockItem->id,
                                'user_id' => $user->id,
                                'action' => StockMovementActionEnum::IMPORTED->value,
                                'description' => "Ürün: {$product->name} - Stok girişi yapıldı",
                                'created_at' => now(),
                            ]);

                            $createdCount++;
                        }
                    });

                    if ($createdCount > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('Başarılı')
                            ->body("{$createdCount} adet stok kalemi eklendi." . ($skippedCount > 0 ? " {$skippedCount} adet barkod zaten mevcut olduğu için atlandı." : ''))
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Uyarı')
                            ->body('Hiçbir stok kalemi eklenmedi. Tüm barkodlar zaten mevcut olabilir.')
                            ->warning()
                            ->send();
                    }
                });
        }

        return $actions;
    }
}
