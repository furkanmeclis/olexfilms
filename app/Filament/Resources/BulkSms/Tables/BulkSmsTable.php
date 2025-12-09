<?php

namespace App\Filament\Resources\BulkSms\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BulkSmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('target_type')
                    ->label('Hedef Tipi')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'all' => 'Tümüne Gönder',
                        'customers' => 'Sadece Müşteriler',
                        'dealers' => 'Sadece Bayiler',
                        'both' => 'Müşteriler ve Bayiler',
                        'custom' => 'Özel Seçim',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('total_recipients')
                    ->label('Toplam Alıcı')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sent_count')
                    ->label('Gönderilen')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('failed_count')
                    ->label('Başarısız')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Taslak',
                        'sending' => 'Gönderiliyor',
                        'completed' => 'Tamamlandı',
                        'failed' => 'Başarısız',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'sending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('createdBy.name')
                    ->label('Oluşturan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'draft' => 'Taslak',
                        'sending' => 'Gönderiliyor',
                        'completed' => 'Tamamlandı',
                        'failed' => 'Başarısız',
                    ]),

                SelectFilter::make('target_type')
                    ->label('Hedef Tipi')
                    ->options([
                        'all' => 'Tümüne Gönder',
                        'customers' => 'Sadece Müşteriler',
                        'dealers' => 'Sadece Bayiler',
                        'both' => 'Müşteriler ve Bayiler',
                        'custom' => 'Özel Seçim',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
