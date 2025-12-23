<?php

namespace App\Filament\Resources\ServiceStatusLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceStatusLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.service_no')
                    ->label('Servis No')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-wrench-screwdriver'),

                TextColumn::make('fromDealer.name')
                    ->label('Uygulayan Bayi')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Merkez')
                    ->icon('heroicon-o-arrow-left'),

                TextColumn::make('toDealer.name')
                    ->label('Gidilen Şube')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Merkez')
                    ->icon('heroicon-o-arrow-right'),

                TextColumn::make('user.name')
                    ->label('Ekleyen Kullanıcı')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('notes')
                    ->label('Notlar')
                    ->html()
                    ->limit(50)
                    ->tooltip(fn ($record) => strip_tags($record->notes ?? ''))
                    ->placeholder('Not yok'),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
