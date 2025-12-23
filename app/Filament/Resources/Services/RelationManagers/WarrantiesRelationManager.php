<?php

namespace App\Filament\Resources\Services\RelationManagers;

use App\Filament\Resources\Warranties\WarrantyResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WarrantiesRelationManager extends RelationManager
{
    protected static string $relationship = 'warranties';

    protected static ?string $title = 'Garantiler';

    protected static ?string $modelLabel = 'Garanti';

    protected static ?string $pluralModelLabel = 'Garantiler';

    public function form(Schema $schema): Schema
    {
        // Garantiler sadece görüntüleme için, form disabled
        return $schema
            ->components([
                // Form disabled - garantiler otomatik oluşturuluyor
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('stockItem.barcode')
                    ->label('Barkod')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('stockItem.product.name')
                    ->label('Ürün')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Başlangıç')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Bitiş')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Kalan Gün')
                    ->formatStateUsing(fn ($state) => $state !== null
                        ? ($state > 0 ? "{$state} gün" : 'Süresi dolmuş')
                        : 'Bilinmiyor')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state <= 0 => 'danger',
                        $state <= 30 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('is_active')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $state
                        ? ($record->is_expired ? 'Süresi Dolmuş' : 'Aktif')
                        : 'Pasif')
                    ->color(fn ($state, $record) => match (true) {
                        ! $state => 'gray',
                        $record->is_expired => 'danger',
                        default => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Garantiler otomatik oluşturuluyor, create action yok
            ])
            ->actions([
                ViewAction::make()
                    ->label('Görüntüle')
                    ->url(fn ($record) => WarrantyResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                // Garantiler silinemez
            ])
            ->defaultSort('end_date', 'asc');
    }
}
