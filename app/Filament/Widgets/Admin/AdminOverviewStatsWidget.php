<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\UserRoleEnum;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Service;
use App\Models\StockItem;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Genel İstatistikler';

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
        return Cache::remember('admin_overview_stats', 300, function () {
            $totalDealers = Dealer::count();
            $activeDealers = Dealer::where('is_active', true)->count();
            $inactiveDealers = $totalDealers - $activeDealers;

            $totalUsers = User::count();
            $superAdmins = User::role(UserRoleEnum::SUPER_ADMIN->value)->count();
            $centerStaff = User::role(UserRoleEnum::CENTER_STAFF->value)->count();
            $dealerOwners = User::role(UserRoleEnum::DEALER_OWNER->value)->count();
            $dealerStaff = User::role(UserRoleEnum::DEALER_STAFF->value)->count();

            $totalCustomers = Customer::count();
            $totalServices = Service::count();
            $todayServices = Service::whereDate('created_at', today())->count();
            $thisMonthServices = Service::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $totalOrders = Order::count();
            $pendingOrders = Order::where('status', 'pending')->count();

            $totalStock = StockItem::count();
            $availableStock = StockItem::where('status', 'available')->count();
            $reservedStock = StockItem::where('status', 'reserved')->count();
            $usedStock = StockItem::where('status', 'used')->count();

            return [
                Stat::make('Toplam Bayi', $totalDealers)
                    ->description("{$activeDealers} aktif, {$inactiveDealers} pasif")
                    ->descriptionIcon('heroicon-m-building-storefront')
                    ->color('primary'),

                Stat::make('Toplam Kullanıcı', $totalUsers)
                    ->description("Süper Admin: {$superAdmins}, Merkez: {$centerStaff}, Bayi Sahibi: {$dealerOwners}, Bayi Çalışanı: {$dealerStaff}")
                    ->descriptionIcon('heroicon-m-users')
                    ->color('info'),

                Stat::make('Toplam Müşteri', $totalCustomers)
                    ->description('Tüm bayiler')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),

                Stat::make('Toplam Servis', $totalServices)
                    ->description("Bugün: {$todayServices}, Bu ay: {$thisMonthServices}")
                    ->descriptionIcon('heroicon-m-wrench-screwdriver')
                    ->color('warning'),

                Stat::make('Toplam Sipariş', $totalOrders)
                    ->description("{$pendingOrders} bekleyen")
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('primary'),

                Stat::make('Toplam Stok', $totalStock)
                    ->description("Müsait: {$availableStock}, Rezerve: {$reservedStock}, Kullanıldı: {$usedStock}")
                    ->descriptionIcon('heroicon-m-archive-box')
                    ->color('success'),
            ];
        });
    }
}

