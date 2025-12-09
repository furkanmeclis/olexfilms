<?php

namespace App\Filament\Widgets;

use App\Services\SmsCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $remainingSms = SmsCacheService::getRemainingSms();
        $totalSentToday = SmsCacheService::getTotalSentToday();
        $totalSentThisMonth = SmsCacheService::getTotalSentThisMonth();

        return [
            Stat::make('Kalan SMS Adedi', $remainingSms ?? 'Bilinmiyor')
                ->description('Toplam kalan SMS')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($remainingSms && $remainingSms < 100 ? 'danger' : 'success'),

           
            Stat::make('Bugün Gönderilen', $totalSentToday)
                ->description('Bu gün gönderilen SMS sayısı')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Bu Ay Gönderilen', $totalSentThisMonth)
                ->description('Bu ay gönderilen SMS sayısı')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
