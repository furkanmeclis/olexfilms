<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\UserRoleEnum;
use App\Models\Dealer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class AdminDealerPerformanceChartWidget extends ChartWidget
{
    protected static ?int $sort = 7;

    protected ?string $heading = 'Bayi Performansı';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]) ?? false;
    }

    protected function getData(): array
    {
        return Cache::remember('admin_dealer_performance', 300, function () {
            // En aktif 10 bayiyi servis sayısına göre al
            $topDealers = Dealer::query()
                ->withCount('services')
                ->orderBy('services_count', 'desc')
                ->limit(10)
                ->get();

            $labels = $topDealers->pluck('name')->toArray();
            $serviceCounts = $topDealers->pluck('services_count')->toArray();

            return [
                'datasets' => [
                    [
                        'label' => 'Servis Sayısı',
                        'data' => $serviceCounts,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'borderWidth' => 1,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
