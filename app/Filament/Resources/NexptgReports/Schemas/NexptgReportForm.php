<?php

namespace App\Filament\Resources\NexptgReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NexptgReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rapor Bilgileri')
                    ->schema([
                        TextInput::make('external_id')
                            ->label('Harici ID')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('name')
                            ->label('Rapor Adı')
                            ->required()
                            ->maxLength(255),

                        DateTimePicker::make('date')
                            ->label('Tarih')
                            ->required()
                            ->displayFormat('d.m.Y H:i')
                            ->seconds(false),

                        DateTimePicker::make('calibration_date')
                            ->label('Kalibrasyon Tarihi')
                            ->displayFormat('d.m.Y H:i')
                            ->seconds(false)
                            ->nullable(),

                        TextInput::make('device_serial_number')
                            ->label('Cihaz Seri Numarası')
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Araç Bilgileri')
                    ->schema([
                        TextInput::make('brand')
                            ->label('Marka')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('model')
                            ->label('Model')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('year')
                            ->label('Yıl')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('type_of_body')
                            ->label('Kasa Tipi')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('vin')
                            ->label('Şasi Numarası')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('fuel_type')
                            ->label('Yakıt Tipi')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('capacity')
                            ->label('Motor Hacmi')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('power')
                            ->label('Güç')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('unit_of_measure')
                            ->label('Ölçü Birimi')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(3),

                Section::make('Notlar')
                    ->schema([
                        Textarea::make('comment')
                            ->label('Yorum')
                            ->rows(3)
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
            ]);
    }
}
