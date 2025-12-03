<?php

namespace App\Filament\Resources\CarModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CarModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('brand.logo')
                    ->label('Marka Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                \Filament\Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marka')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Model Adı')
                    ->searchable()
                    ->sortable(),

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
