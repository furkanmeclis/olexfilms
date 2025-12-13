<?php

namespace App\Filament\Resources\NexptgReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NexptgReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('external_id')
                    ->label('Harici ID')
                    ->numeric()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Rapor Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand')
                    ->label('Marka')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('vin')
                    ->label('Şasi No')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('device_serial_number')
                    ->label('Cihaz Seri No')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('date')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('measurements_count')
                    ->label('Ölçüm Sayısı')
                    ->counts('measurements')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('apiUser.user.name')
                    ->label('Okuma Yapan Kullanıcı')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belirtilmemiş')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
