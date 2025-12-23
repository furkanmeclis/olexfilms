<?php

namespace App\Filament\Resources\CarBrands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarBrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Marka Bilgileri')
                    ->schema([
                        TextInput::make('name')
                            ->label('Marka Adı')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('external_id')
                            ->label('Dış ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk(config('filesystems.default'))
                            ->directory('car-brands/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->nullable()
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),

                        Toggle::make('show_name')
                            ->label('Marka Adını Göster')
                            ->default(true)
                            ->required(),

                        TextInput::make('logo_height')
                            ->label('Hizmet Ekranı Logo Yüksekliği')
                            ->numeric()
                            ->default(25)
                            ->suffix('px')
                            ->minValue(1)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
