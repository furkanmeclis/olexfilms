<?php

namespace App\Filament\Resources\CarModels\Tables;

use App\Models\CarModel;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class CarModelsTable
{
    public static function configure(Table $table): Table
    {
        $canManageActiveStatus = auth()->user()?->hasAnyRole(['super_admin', 'center_staff']) ?? false;

        // Benzersiz powertrain değerlerini çek (cache ile sorgu yükünü azalt)
        $powertrains = Cache::remember(
            'car_models_powertrain_filter_options',
            60 * 5, // 5 dakika cache
            function (): array {
                return CarModel::query()
                    ->select('powertrain')
                    ->whereNotNull('powertrain')
                    ->distinct()
                    ->pluck('powertrain', 'powertrain')
                    ->toArray();
            }
        );

        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('brand.logo_url')
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
                \Filament\Tables\Columns\TextColumn::make('powertrain')
                    ->label('Motor Tipi')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('yearstart')
                    ->label('Yıl Başlangıç')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('yearstop')
                    ->label('Yıl Bitiş')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('coupe')
                    ->label('Kasa Tipi')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable()
                    ->visible(fn () => $canManageActiveStatus),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('powertrain')
                    ->label('Motor Tipi')
                    ->options($powertrains)
                    ->searchable()
                    ->placeholder('Tüm motor tipleri'),
                TrashedFilter::make(),

            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Dışa Aktar'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                EditAction::make()
                    ->label('Düzenle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
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
