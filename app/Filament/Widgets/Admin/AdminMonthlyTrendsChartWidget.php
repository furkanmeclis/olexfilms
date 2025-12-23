<?php

namespace App\Filament\Widgets\Admin;

use App\Enums\UserRoleEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class AdminMonthlyTrendsChartWidget extends ChartWidget
{
    protected static ?int $sort = 8;

    protected ?string $heading = 'Aylık Trendler';

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
        return Cache::remember('admin_monthly_trends', 300, function () {
            // Son 12 ayın verilerini al
            $months = [];
            $serviceCounts = [];
            $orderCounts = [];
            $customerCounts = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthName = $date->format('M Y');
                $months[] = $monthName;

                $serviceCounts[] = Service::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();

                $orderCounts[] = Order::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();

                $customerCounts[] = Customer::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Servis Sayısı',
                        'data' => $serviceCounts,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Sipariş Sayısı',
                        'data' => $orderCounts,
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Müşteri Kayıtları',
                        'data' => $customerCounts,
                        'borderColor' => 'rgb(245, 158, 11)',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'tension' => 0.4,
                    ],
                ],
                'labels' => $months,
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
