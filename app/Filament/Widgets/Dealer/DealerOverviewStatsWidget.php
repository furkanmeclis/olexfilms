<?php

namespace App\Filament\Widgets\Dealer;

use App\Enums\UserRoleEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\StockItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DealerOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Genel İstatistikler';

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

        return Cache::remember("dealer_overview_stats_{$dealerId}", 300, function () use ($dealerId) {
            $totalCustomers = Customer::where('dealer_id', $dealerId)->count();
            $totalServices = Service::where('dealer_id', $dealerId)->count();
            $totalOrders = Order::where('dealer_id', $dealerId)->count();
            $availableStock = StockItem::where('dealer_id', $dealerId)
                ->where('status', 'available')
                ->count();

            return [
                Stat::make('Toplam Müşteri', $totalCustomers)
                    ->description('Bayinize kayıtlı müşteriler')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),

                Stat::make('Toplam Servis', $totalServices)
                    ->description('Toplam servis sayısı')
                    ->descriptionIcon('heroicon-m-wrench-screwdriver')
                    ->color('warning'),

                Stat::make('Toplam Sipariş', $totalOrders)
                    ->description('Toplam sipariş sayısı')
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('primary'),

                Stat::make('Müsait Stok', $availableStock)
                    ->description('Kullanıma hazır stok adedi')
                    ->descriptionIcon('heroicon-m-archive-box')
                    ->color('success'),
            ];
        });
    }
}

