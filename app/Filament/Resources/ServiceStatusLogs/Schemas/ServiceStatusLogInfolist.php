<?php

namespace App\Filament\Resources\ServiceStatusLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceStatusLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Log Bilgileri')
                    ->schema([
                        TextEntry::make('service.service_no')
                            ->label('Servis No')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-wrench-screwdriver'),

                        TextEntry::make('fromDealer.name')
                            ->label('Uygulayan Bayi')
                            ->badge()
                            ->color('info')
                            ->placeholder('Merkez')
                            ->icon('heroicon-o-arrow-left'),

                        TextEntry::make('toDealer.name')
                            ->label('Gidilen Şube')
                            ->badge()
                            ->color('primary')
                            ->placeholder('Merkez')
                            ->icon('heroicon-o-arrow-right'),

                        TextEntry::make('user.name')
                            ->label('Ekleyen Kullanıcı')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('created_at')
                            ->label('Tarih')
                            ->dateTime('d.m.Y H:i')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-information-circle'),

                Section::make('Notlar')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Log Notları')
                            ->placeholder('Not yok')
                            ->html()
                            ->columnSpanFull()
                            ->icon('heroicon-o-document-text'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->notes))
                    ->icon('heroicon-o-document'),

                Section::make('Servis Detayları')
                    ->schema([
                        TextEntry::make('service.customer.name')
                            ->label('Müşteri')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('service.carBrand.name')
                            ->label('Marka')
                            ->icon('heroicon-o-tag'),

                        TextEntry::make('service.carModel.name')
                            ->label('Model')
                            ->icon('heroicon-o-cog-6-tooth'),

                        TextEntry::make('service.plate')
                            ->label('Plaka')
                            ->badge()
                            ->color('warning')
                            ->placeholder('Girilmemiş')
                            ->icon('heroicon-o-identification'),

                        TextEntry::make('service.status')
                            ->label('Servis Durumu')
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->badge()
                            ->color(fn ($state) => match ($state->value) {
                                'draft' => 'gray',
                                'pending' => 'warning',
                                'processing' => 'info',
                                'ready' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->icon('heroicon-o-check-circle'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->icon('heroicon-o-clipboard-document-list'),
            ]);
    }
}
