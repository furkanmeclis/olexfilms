<?php

namespace App\Filament\Widgets\Dealer;

use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DealerRecentActivitiesWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Son Aktiviteler';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole([
            UserRoleEnum::DEALER_OWNER->value,
            UserRoleEnum::DEALER_STAFF->value,
        ]) ?? false;
    }

    public function table(Table $table): Table
    {
        $dealerId = auth()->user()->dealer_id;

        if (!$dealerId) {
            return $table->query(fn () => Service::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(function () use ($dealerId): Builder {
                return Service::query()
                    ->where('dealer_id', $dealerId)
                    ->with(['customer', 'user'])
                    ->latest()
                    ->limit(10);
            })
            ->columns([
                TextColumn::make('service_no')
                    ->label('Servis No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => ServiceStatusEnum::getLabels()[$state->value] ?? $state->value)
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'ready' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Oluşturan')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Görüntüle')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Service $record) => ServiceResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->paginated(false);
    }
}




