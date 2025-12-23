<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminServiceStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Servis İstatistikleri';

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
        return Cache::remember('admin_service_stats', 300, function () {
            $todayServices = Service::whereDate('created_at', today())->count();
            $thisMonthServices = Service::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $pendingServices = Service::where('status', ServiceStatusEnum::PENDING->value)->count();
            $processingServices = Service::where('status', ServiceStatusEnum::PROCESSING->value)->count();
            $readyServices = Service::where('status', ServiceStatusEnum::READY->value)->count();

            $completedThisMonth = Service::where('status', ServiceStatusEnum::COMPLETED->value)
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->count();

            return [
                Stat::make('Bugün Oluşturulan', $todayServices)
                    ->description('Bugün oluşturulan servis sayısı')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info'),

                Stat::make('Bu Ay Oluşturulan', $thisMonthServices)
                    ->description('Bu ay oluşturulan servis sayısı')
                    ->descriptionIcon('heroicon-m-calendar')
                    ->color('primary'),

                Stat::make('Bekleyen Servisler', $pendingServices)
                    ->description('Onay bekleyen servisler')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),

                Stat::make('İşlemdeki Servisler', $processingServices)
                    ->description('Devam eden servisler')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color('info'),

                Stat::make('Hazır Servisler', $readyServices)
                    ->description('Teslim için hazır')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Bu Ay Tamamlanan', $completedThisMonth)
                    ->description('Bu ay tamamlanan servisler')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success'),
            ];
        });
    }
}
