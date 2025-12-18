<?php

namespace App\Filament\Widgets\Dealer;

use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DealerStockStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Stok İstatistikleri';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole([
            UserRoleEnum::DEALER_OWNER->value,
            UserRoleEnum::DEALER_STAFF->value,
        ]) ?? false;
    }

    protected function getStats(): array
    {
        $dealerId = auth()->user()->dealer_id;

        if (!$dealerId) {
            return [];
        }

        return Cache::remember("dealer_stock_stats_{$dealerId}", 300, function () use ($dealerId) {
            $availableStock = StockItem::where('dealer_id', $dealerId)
                ->where('status', StockStatusEnum::AVAILABLE->value)
                ->count();

            $reservedStock = StockItem::where('dealer_id', $dealerId)
                ->where('status', StockStatusEnum::RESERVED->value)
                ->count();

            // Kritik stok: 5'ten az müsait stoku olan ürünler
            $criticalStock = StockItem::where('dealer_id', $dealerId)
                ->where('status', StockStatusEnum::AVAILABLE->value)
                ->select('product_id')
                ->groupBy('product_id')
                ->havingRaw('COUNT(*) < 5')
                ->count();

            $todayMovements = StockMovement::whereHas('stockItem', function ($query) use ($dealerId) {
                $query->where('dealer_id', $dealerId);
            })
                ->whereDate('created_at', today())
                ->count();

            return [
                Stat::make('Müsait Stok', $availableStock)
                    ->description('Kullanıma hazır stok adedi')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Rezerve Stok', $reservedStock)
                    ->description('Rezerve edilmiş stok')
                    ->descriptionIcon('heroicon-m-lock-closed')
                    ->color('warning'),

                Stat::make('Kritik Stok Seviyesi', $criticalStock)
                    ->description('5 adetten az stoku olan ürünler')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color($criticalStock > 0 ? 'danger' : 'success'),

                Stat::make('Bugünkü Stok Hareketleri', $todayMovements)
                    ->description('Bugün yapılan stok hareketleri')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color('info'),
            ];
        });
    }
}

