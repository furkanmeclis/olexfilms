<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerTypeEnum;
use App\Enums\UserRoleEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));

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
                Section::make('Müşteri Bilgileri')
                    ->schema([
                        Select::make('type')
                            ->label('Tip')
                            ->options(CustomerTypeEnum::getLabels())
                            ->required()
                            ->default(CustomerTypeEnum::INDIVIDUAL->value)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('tc_no', null) && $set('tax_no', null) && $set('tax_office', null)),

                        TextInput::make('name')
                            ->label('Ad Soyad / Firma Adı')
                            ->required()
                            ->maxLength(255),

                        PhoneInput::make('phone')
                            ->label('Telefon')
                            ->required()
                            ->defaultCountry('TR')
                            ->validateFor('TR')
                            ->locale('tr'),

                        TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->maxLength(255),

                        // Bireysel için TC Kimlik No
                        TextInput::make('tc_no')
                            ->label('TC Kimlik No')
                            ->numeric()
                            ->minLength(11)
                            ->maxLength(11)
                            ->visible(fn ($get) => $get('type') === CustomerTypeEnum::INDIVIDUAL->value)
                            ->required(fn ($get) => $get('type') === CustomerTypeEnum::INDIVIDUAL->value),

                        // Kurumsal için Vergi No ve Vergi Dairesi
                        TextInput::make('tax_no')
                            ->label('Vergi No')
                            ->numeric()
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('type') === CustomerTypeEnum::CORPORATE->value)
                            ->required(fn ($get) => $get('type') === CustomerTypeEnum::CORPORATE->value),

                        TextInput::make('tax_office')
                            ->label('Vergi Dairesi')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('type') === CustomerTypeEnum::CORPORATE->value)
                            ->required(fn ($get) => $get('type') === CustomerTypeEnum::CORPORATE->value),
                    ])
                    ->columns(2),

                Section::make('Adres Bilgileri')
                    ->schema([
                        Select::make('city')
                            ->label('İl')
                            ->options($cityData)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('district', null)),

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
                            ->disabled(fn ($get) => ! $get('city'))
                            ->reactive(),

                        Textarea::make('address')
                            ->label('Adres Detayı')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Bayi Bilgisi')
                    ->schema([
                        $isAdmin
                            ? Select::make('dealer_id')
                                ->label('Bayi')
                                ->relationship('dealer', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                            : Hidden::make('dealer_id')
                                ->default(fn () => $user->dealer_id),

                        Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ])
                    ->columns(2)
                    ->visible(fn () => $isAdmin),

                Section::make('FCM Token')
                    ->schema([
                        TextInput::make('fcm_token')
                            ->label('FCM Token')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => $isAdmin),
            ]);
    }
}
