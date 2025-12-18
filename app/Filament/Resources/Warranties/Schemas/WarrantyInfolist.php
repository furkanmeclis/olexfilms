<?php

namespace App\Filament\Resources\Warranties\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarrantyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        TextEntry::make('service.service_no')
                            ->label('Hizmet Numarası')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->url(fn ($record) => $record->service 
                                ? \App\Filament\Resources\Services\ServiceResource::getUrl('view', ['record' => $record->service])
                                : null),

                        TextEntry::make('stockItem.barcode')
                            ->label('Barkod')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-qr-code'),

                        TextEntry::make('stockItem.product.name')
                            ->label('Ürün Adı')
                            ->weight('bold')
                            ->icon('heroicon-o-cube'),

                        TextEntry::make('is_active')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state, $record) => $state 
                                ? ($record->is_expired ? 'Süresi Dolmuş' : 'Aktif')
                                : 'Pasif')
                            ->color(fn ($state, $record) => match (true) {
                                !$state => 'gray',
                                $record->is_expired => 'danger',
                                default => 'success',
                            })
                            ->icon(fn ($state, $record) => match (true) {
                                !$state => 'heroicon-o-x-circle',
                                $record->is_expired => 'heroicon-o-clock',
                                default => 'heroicon-o-check-circle',
                            }),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-information-circle'),

                Section::make('Garanti Detayları')
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Başlangıç Tarihi')
                            ->date('d.m.Y')
                            ->icon('heroicon-o-calendar')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('end_date')
                            ->label('Bitiş Tarihi')
                            ->date('d.m.Y')
                            ->icon('heroicon-o-calendar')
                            ->badge()
                            ->color(fn ($record) => $record->is_expired ? 'danger' : 'primary'),

                        TextEntry::make('days_remaining')
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
                            })
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('is_active')
                            ->label('Aktif Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-shield-check'),

                Section::make('İlişkili Kayıtlar')
                    ->schema([
                        TextEntry::make('service.customer.name')
                            ->label('Müşteri')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('service.dealer.name')
                            ->label('Bayi')
                            ->badge()
                            ->color('primary')
                            ->placeholder('Merkez')
                            ->icon('heroicon-o-building-storefront'),

                        TextEntry::make('stockItem.sku')
                            ->label('Stok Kodu')
                            ->badge()
                            ->color('gray')
                            ->icon('heroicon-o-tag'),

                        TextEntry::make('stockItem.product.warranty_duration')
                            ->label('Ürün Garanti Süresi')
                            ->formatStateUsing(fn ($state) => $state ? "{$state} Ay" : 'Belirtilmemiş')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->icon('heroicon-o-link'),

                Section::make('Tarihçe')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Oluşturulma Tarihi')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-plus-circle')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('updated_at')
                            ->label('Son Güncelleme')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-arrow-path')
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(true)
                    ->icon('heroicon-o-clock'),
            ]);
    }
}

