<?php

namespace App\Filament\Resources\Dealers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\File;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class DealerForm
{
    public static function configure(Schema $schema): Schema
    {
        // İl-İlçe JSON verisini yükle
        $cityData = [];
        $cityDistrictMap = [];
        
        try {
            $jsonPath = storage_path('il-ilce.json');
            if (File::exists($jsonPath)) {
                $jsonData = json_decode(File::get($jsonPath), true);
                if (isset($jsonData['data'])) {
                    foreach ($jsonData['data'] as $city) {
                        $cityName = $city['il_adi'];
                        $cityData[$cityName] = $cityName;
                        
                        if (isset($city['ilceler']) && is_array($city['ilceler'])) {
                            $districts = [];
                            foreach ($city['ilceler'] as $district) {
                                $districts[$district['ilce_adi']] = $district['ilce_adi'];
                            }
                            $cityDistrictMap[$cityName] = $districts;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // JSON yüklenemezse boş kalır
        }

        return $schema
            ->components([
                Section::make('Bayi Bilgileri')
                    ->schema([
                        TextInput::make('dealer_code')
                            ->label('Bayi Kodu')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null)
                            ->default(fn ($record) => $record?->dealer_code),

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

                        PhoneInput::make('phone')
                            ->label('Telefon')
                            ->required()
                            ->defaultCountry('TR')
                            ->validateFor('TR')
                            ->locale('tr'),

                        Textarea::make('address')
                            ->label('Adres')
                            ->required()
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Konum Bilgileri')
                    ->schema([
                        Select::make('city')
                            ->label('İl')
                            ->options($cityData)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('district', null))
                            ->nullable(),

                        Select::make('district')
                            ->label('İlçe')
                            ->options(function ($get) use ($cityDistrictMap) {
                                $city = $get('city');
                                if ($city && isset($cityDistrictMap[$city])) {
                                    return $cityDistrictMap[$city];
                                }
                                return [];
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($get) => !$get('city'))
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Sosyal Medya')
                    ->schema([
                        TextInput::make('website_url')
                            ->label('Web Sitesi')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url()
                            ->prefixIcon('heroicon-o-link')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->prefixIcon('heroicon-o-link')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('twitter_url')
                            ->label('Twitter/X')
                            ->url()
                            ->prefixIcon('heroicon-o-x-mark')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url()
                            ->prefixIcon('heroicon-o-link')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Logo')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('dealers/logos')
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
