<?php

namespace App\Filament\Widgets\Dealer;

use App\Enums\OrderStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DealerOrderStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Sipariş İstatistikleri';

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

        return Cache::remember("dealer_order_stats_{$dealerId}", 300, function () use ($dealerId) {
            $pendingOrders = Order::where('dealer_id', $dealerId)
                ->where('status', OrderStatusEnum::PENDING->value)
                ->count();

            $processingOrders = Order::where('dealer_id', $dealerId)
                ->where('status', OrderStatusEnum::PROCESSING->value)
                ->count();

            $shippedOrders = Order::where('dealer_id', $dealerId)
                ->where('status', OrderStatusEnum::SHIPPED->value)
                ->count();

            $deliveredThisMonth = Order::where('dealer_id', $dealerId)
                ->where('status', OrderStatusEnum::DELIVERED->value)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

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
            ];
        });
    }
}

