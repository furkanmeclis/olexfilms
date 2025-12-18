<?php

namespace App\Filament\Widgets\Dealer;

use App\Enums\UserRoleEnum;
use App\Models\Service;
use App\Models\Warranty;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DealerWarrantyStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Garanti İstatistikleri';

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

        return Cache::remember("dealer_warranty_stats_{$dealerId}", 300, function () use ($dealerId) {
            // Bayiye ait servislerin garantilerini al
            $serviceIds = Service::where('dealer_id', $dealerId)->pluck('id');

            $activeWarranties = Warranty::whereIn('service_id', $serviceIds)
                ->active()
                ->count();

            $expiringSoon = Warranty::whereIn('service_id', $serviceIds)
                ->expiringSoon(30)
                ->count();

            $expired = Warranty::whereIn('service_id', $serviceIds)
                ->expired()
                ->count();

            return [
                Stat::make('Aktif Garanti', $activeWarranties)
                    ->description('Aktif garanti sayısı')
                    ->descriptionIcon('heroicon-m-shield-check')
                    ->color('success'),

                Stat::make('Yakında Bitecek', $expiringSoon)
                    ->description('30 gün içinde bitecek garantiler')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),

                Stat::make('Süresi Dolmuş', $expired)
                    ->description('Süresi dolmuş garantiler')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        });
    }
}

