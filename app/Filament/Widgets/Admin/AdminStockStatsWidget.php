<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Product;
use App\Models\StockItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminStockStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Stok İstatistikleri';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]) ?? false;
    }

    protected function getStats(): array
    {
        return Cache::remember('admin_stock_stats', 300, function () {
            $totalStock = StockItem::count();
            $availableStock = StockItem::where('status', StockStatusEnum::AVAILABLE->value)->count();
            $reservedStock = StockItem::where('status', StockStatusEnum::RESERVED->value)->count();
            $usedStock = StockItem::where('status', StockStatusEnum::USED->value)->count();

            // Kritik stok seviyesi: Her ürün için stok sayısını kontrol et
            $criticalStock = DB::table('stock_items')
                ->join('products', 'stock_items.product_id', '=', 'products.id')
                ->where('stock_items.status', StockStatusEnum::AVAILABLE->value)
                ->select('products.id', 'products.name', DB::raw('COUNT(stock_items.id) as stock_count'))
                ->groupBy('products.id', 'products.name')
                ->having('stock_count', '<', 5) // 5'ten az stok varsa kritik
                ->count();

            return [
                Stat::make('Toplam Stok Adedi', $totalStock)
                    ->description('Tüm stok durumları')
                    ->descriptionIcon('heroicon-m-archive-box')
                    ->color('primary'),

                Stat::make('Müsait Stok', $availableStock)
                    ->description('Kullanıma hazır stok')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Rezerve Stok', $reservedStock)
                    ->description('Rezerve edilmiş stok')
                    ->descriptionIcon('heroicon-m-lock-closed')
                    ->color('warning'),

                Stat::make('Kullanılan Stok', $usedStock)
                    ->description('Kullanılmış stok')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('gray'),

                Stat::make('Kritik Stok Seviyesi', $criticalStock)
                    ->description('5 adetten az stoku olan ürünler')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color($criticalStock > 0 ? 'danger' : 'success'),
            ];
        });
    }
}

