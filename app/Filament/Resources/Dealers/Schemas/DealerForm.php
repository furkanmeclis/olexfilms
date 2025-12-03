<?php

namespace App\Filament\Resources\Dealers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bayi Bilgileri')
                    ->schema([
                        TextInput::make('name')
                            ->label('Bayi Adı')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Telefon')
                            ->required()
                            ->tel()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Adres')
                            ->required()
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Logo')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('dealers/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Durum')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
