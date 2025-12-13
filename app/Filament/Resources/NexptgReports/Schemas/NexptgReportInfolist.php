<?php

namespace App\Filament\Resources\NexptgReports\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NexptgReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rapor Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('external_id')
                            ->label('Harici ID'),

                        Infolists\Components\TextEntry::make('name')
                            ->label('Rapor Adı')
                            ->size('lg')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('date')
                            ->label('Tarih')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('calibration_date')
                            ->label('Kalibrasyon Tarihi')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('device_serial_number')
                            ->label('Cihaz Seri Numarası')
                            ->icon('heroicon-m-device-phone-mobile'),
                    ])
                    ->columns(2),

                Section::make('Araç Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('brand')
                            ->label('Marka')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('model')
                            ->label('Model')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('year')
                            ->label('Yıl')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('type_of_body')
                            ->label('Kasa Tipi')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('vin')
                            ->label('Şasi Numarası')
                            ->placeholder('Belirtilmemiş')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('fuel_type')
                            ->label('Yakıt Tipi')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('capacity')
                            ->label('Motor Hacmi')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('power')
                            ->label('Güç')
                            ->placeholder('Belirtilmemiş'),

                        Infolists\Components\TextEntry::make('unit_of_measure')
                            ->label('Ölçü Birimi')
                            ->placeholder('Belirtilmemiş'),
                    ])
                    ->columns(3),

                Section::make('İstatistikler')
                    ->schema([
                        Infolists\Components\TextEntry::make('measurements_count')
                            ->label('Toplam Ölçüm Sayısı')
                            ->state(fn ($record) => $record->measurements()->count())
                            ->icon('heroicon-m-chart-bar'),

                        Infolists\Components\TextEntry::make('external_measurements_count')
                            ->label('Dış Ölçümler')
                            ->state(fn ($record) => $record->measurements()->where('is_inside', false)->count())
                            ->icon('heroicon-m-arrow-up-circle'),

                        Infolists\Components\TextEntry::make('internal_measurements_count')
                            ->label('İç Ölçümler')
                            ->state(fn ($record) => $record->measurements()->where('is_inside', true)->count())
                            ->icon('heroicon-m-arrow-down-circle'),

                        Infolists\Components\TextEntry::make('tires_count')
                            ->label('Lastik Sayısı')
                            ->state(fn ($record) => $record->tires()->count())
                            ->icon('heroicon-m-circle-stack'),
                    ])
                    ->columns(4),

                Section::make('Notlar')
                    ->schema([
                        Infolists\Components\TextEntry::make('comment')
                            ->label('Yorum')
                            ->placeholder('Yorum eklenmemiş')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->comment)),

                Section::make('API Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('apiUser.username')
                            ->label('API Kullanıcı Adı')
                            ->placeholder('Belirtilmemiş')
                            ->icon('heroicon-m-key'),

                        Infolists\Components\TextEntry::make('apiUser.user.name')
                            ->label('Bağlı Kullanıcı')
                            ->placeholder('Belirtilmemiş')
                            ->icon('heroicon-m-user'),

                        Infolists\Components\TextEntry::make('apiUser.is_active')
                            ->label('API Kullanıcı Durumu')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->placeholder('Belirtilmemiş'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->apiUser !== null)
                    ->collapsible(),

                Section::make('Zaman Damgaları')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Oluşturulma')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Güncellenme')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-m-arrow-path'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}

