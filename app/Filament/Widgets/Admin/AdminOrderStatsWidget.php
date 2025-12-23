<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminOrderStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Sipariş İstatistikleri';

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
        return Cache::remember('admin_order_stats', 300, function () {
            $pendingOrders = Order::where('status', OrderStatusEnum::PENDING->value)->count();
            $processingOrders = Order::where('status', OrderStatusEnum::PROCESSING->value)->count();
            $shippedOrders = Order::where('status', OrderStatusEnum::SHIPPED->value)->count();

            $deliveredThisMonth = Order::where('status', OrderStatusEnum::DELIVERED->value)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Toplam sipariş tutarı (eğer total_amount kolonu varsa)
            $totalAmountThisMonth = Order::whereMonth('orders.created_at', now()->month)
                ->whereYear('orders.created_at', now()->year)
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(DB::raw('SUM(order_items.quantity * products.price) as total'))
                ->value('total') ?? 0;

            return [
                Stat::make('Bekleyen Siparişler', $pendingOrders)
                    ->description('Onay bekleyen siparişler')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),

                Stat::make('Hazırlanan Siparişler', $processingOrders)
                    ->description('Hazırlanmakta olan siparişler')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color('info'),

                Stat::make('Kargodaki Siparişler', $shippedOrders)
                    ->description('Kargoya verilen siparişler')
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('primary'),

                Stat::make('Bu Ay Teslim Edilen', $deliveredThisMonth)
                    ->description('Bu ay teslim edilen siparişler')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success'),

                Stat::make('Bu Ay Toplam Tutar', number_format($totalAmountThisMonth, 2).' $')
                    ->description('Bu ayki siparişlerin toplam tutarı')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),
            ];
        });
    }
}
