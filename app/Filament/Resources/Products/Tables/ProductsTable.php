<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        $isSuperAdmin = auth()->user()?->hasRole('super_admin') ?? false;

        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('sku')
                    ->label('Stok Kodu')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('USD')
                    ->sortable()
                    ->visible(fn () => $isSuperAdmin),

                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                EditAction::make()
                    ->label('Düzenle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Sil'),
                    ForceDeleteBulkAction::make()
                        ->label('Kalıcı Sil'),
                    RestoreBulkAction::make()
                        ->label('Geri Yükle'),
                ]),
            ]);
    }
}
