<?php

namespace App\Filament\Widgets\Dealer;

use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DealerServiceStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Servis İstatistikleri';

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

        if (! $dealerId) {
            return [];
        }

        return Cache::remember("dealer_service_stats_{$dealerId}", 300, function () use ($dealerId) {
            $todayServices = Service::where('dealer_id', $dealerId)
                ->whereDate('created_at', today())
                ->count();

            $pendingServices = Service::where('dealer_id', $dealerId)
                ->where('status', ServiceStatusEnum::PENDING->value)
                ->count();

            $processingServices = Service::where('dealer_id', $dealerId)
                ->where('status', ServiceStatusEnum::PROCESSING->value)
                ->count();

            $readyServices = Service::where('dealer_id', $dealerId)
                ->where('status', ServiceStatusEnum::READY->value)
                ->count();

            $completedThisMonth = Service::where('dealer_id', $dealerId)
                ->where('status', ServiceStatusEnum::COMPLETED->value)
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year)
                ->count();

            return [
                Stat::make('Bugün Oluşturulan', $todayServices)
                    ->description('Bugün oluşturulan servis sayısı')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info'),

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
