<?php

namespace App\Filament\Resources\StockItems\Tables;

use App\Enums\StockLocationEnum;
use App\Enums\StockStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barcode')
                    ->label('Barkod')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('Stok Kodu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Konum')
                    ->formatStateUsing(fn ($state) => StockLocationEnum::getLabels()[$state->value] ?? $state->value)
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => StockStatusEnum::getLabels()[$state->value] ?? $state->value)
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'used' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('dealer.name')
                    ->label('Bayi')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Merkez'),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->label('Konum')
                    ->options(StockLocationEnum::getLabels()),

                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(StockStatusEnum::getLabels()),

                SelectFilter::make('dealer_id')
                    ->label('Bayi')
                    ->relationship('dealer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('product_id')
                    ->label('Ürün')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
            ]);
    }
}
