<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Sipariş No')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('dealer.name')
                    ->label('Bayi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => OrderStatusEnum::getLabels()[$state->value] ?? $state->value)
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('items_sum_quantity')
                    ->label('Toplam Adet')
                    ->sum('items', 'quantity')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Toplam Tutar')
                    ->money('USD')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),

                TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Oluşturan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(OrderStatusEnum::getLabels()),

                SelectFilter::make('dealer_id')
                    ->label('Bayi')
                    ->relationship('dealer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
            ]);
    }
}
