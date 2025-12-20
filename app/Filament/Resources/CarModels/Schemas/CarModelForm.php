<?php

namespace App\Filament\Resources\CarModels\Schemas;

use App\Models\CarBrand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;

class CarModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Model Bilgileri')
                    ->schema([
                        Select::make('brand_id')
                            ->label('Marka')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Model Adı')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('external_id')
                            ->label('Dış ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Modification Bilgileri')
                    ->schema([
                        TextInput::make('powertrain')
                            ->label('Güç Aktarımı')
                            ->maxLength(255),

                        TextInput::make('yearstart')
                            ->label('Başlangıç Yılı')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2100),

                        TextInput::make('yearstop')
                            ->label('Bitiş Yılı')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2100),

                        TextInput::make('coupe')
                            ->label('Gövde Tipi')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
