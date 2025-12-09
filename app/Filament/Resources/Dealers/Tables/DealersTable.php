<?php

namespace App\Filament\Resources\Dealers\Tables;

use App\Filament\Exports\DealerExporter;
use App\Models\Dealer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DealersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                TextColumn::make('dealer_code')
                    ->label('Bayi Kodu')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Bayi Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),

                TextColumn::make('city')
                    ->label('İl')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('district')
                    ->label('İlçe')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('address')
                    ->label('Adres')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->address),

                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_active')
                    ->label('Aktif Bayiler')
                    ->query(fn ($query) => $query->where('is_active', true)),

                Filter::make('is_inactive')
                    ->label('Pasif Bayiler')
                    ->query(fn ($query) => $query->where('is_active', false)),

                SelectFilter::make('city')
                    ->label('İl')
                    ->options(function () {
                        // Veritabanından mevcut illeri al
                        return Dealer::whereNotNull('city')
                            ->distinct()
                            ->pluck('city', 'city')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('district')
                    ->label('İlçe')
                    ->options(function () {
                        // Veritabanından mevcut ilçeleri al
                        return Dealer::whereNotNull('district')
                            ->distinct()
                            ->pluck('district', 'district')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                EditAction::make()
                    ->label('Düzenle'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(DealerExporter::class)
                    ->label('Dışa Aktar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(DealerExporter::class)
                        ->label('Seçilenleri Dışa Aktar'),
                    DeleteBulkAction::make()
                        ->label('Sil'),
                ]),
            ]);
    }
}
