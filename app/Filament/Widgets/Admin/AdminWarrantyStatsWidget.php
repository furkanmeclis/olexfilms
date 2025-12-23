<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\UserRoleEnum;
use App\Models\Warranty;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminWarrantyStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Garanti İstatistikleri';

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
        return Cache::remember('admin_warranty_stats', 300, function () {
            $activeWarranties = Warranty::active()->count();
            $expiringSoon = Warranty::expiringSoon(30)->count();
            $expired = Warranty::expired()->count();
            $totalWarranties = Warranty::count();

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

                Stat::make('Toplam Garanti', $totalWarranties)
                    ->description('Tüm garanti kayıtları')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('primary'),
            ];
        });
    }
}
