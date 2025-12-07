<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\CarPartEnum;
use App\Enums\CustomerTypeEnum;
use App\Enums\ServiceItemUsageTypeEnum;
use App\Enums\ServiceStatusEnum;
use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ServiceForm
{
    protected static function getCityData(): array
    {
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

        return [$cityData, $cityDistrictMap];
    }

    public static function getCustomerAndCarStep(): array
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));
        $currentYear = (int) date('Y');
        $years = range($currentYear, 1975);

        [$cityData, $cityDistrictMap] = self::getCityData();

        return [
            Section::make('Müşteri Seçimi')
                ->schema([
                    Select::make('customer_id')
                        ->label('Müşteri')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
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

                            TextInput::make('phone')
                                ->label('Telefon')
                                ->required()
                                ->tel()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label('E-posta')
                                ->email()
                                ->maxLength(255),

                            TextInput::make('tc_no')
                                ->label('TC Kimlik No')
                                ->numeric()
                                ->minLength(11)
                                ->maxLength(11)
                                ->visible(fn ($get) => $get('type') === CustomerTypeEnum::INDIVIDUAL->value)
                                ->required(fn ($get) => $get('type') === CustomerTypeEnum::INDIVIDUAL->value),

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
                                        ->disabled(fn ($get) => !$get('city'))
                                        ->reactive(),

                                    Textarea::make('address')
                                        ->label('Adres Detayı')
                                        ->rows(3)
                                        ->maxLength(65535)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),

                            Hidden::make('dealer_id')
                                ->default(fn () => $user->dealer_id ?? null),

                            Hidden::make('created_by')
                                ->default(fn () => Auth::id()),
                        ])
                        ->columnSpanFull(),

                    Hidden::make('dealer_id')
                        ->default(fn () => $user->dealer_id ?? null)
                        ->visible(fn () => !$isAdmin),

                    Select::make('dealer_id')
                        ->label('Bayi')
                        ->relationship('dealer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn () => $isAdmin),

                    Hidden::make('user_id')
                        ->default(fn () => Auth::id()),
                ])
                ->columns(2),

            Section::make('Araç Bilgileri')
                ->schema([
                    Select::make('car_brand_id')
                        ->label('Marka')
                        ->relationship('carBrand', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('car_model_id', null)),

                    Select::make('car_model_id')
                        ->label('Model')
                        ->relationship('carModel', 'name', fn ($query, $get) => $query->where('brand_id', $get('car_brand_id')))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn ($get) => !$get('car_brand_id'))
                        ->reactive(),

                    Select::make('year')
                        ->label('Yıl')
                        ->options(array_combine($years, $years))
                        ->required()
                        ->default($currentYear),

                    TextInput::make('vin')
                        ->label('Şasi No')
                        ->maxLength(255),

                    TextInput::make('plate')
                        ->label('Plaka')
                        ->maxLength(255),

                    TextInput::make('km')
                        ->label('Kilometre')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('package')
                        ->label('Paket')
                        ->maxLength(255)
                        ->required(),
                ])
                ->columns(2),
        ];
    }

    public static function getStockStep(): array
    {
        return [
            Section::make('Stok/Malzeme Seçimi')
                ->schema([
                    Text::make('Parçalı ürün girişleriniz yakında desteği gelecektir.')
                        ->color('info')
                        ->weight(FontWeight::Medium)
                        ->columnSpanFull(),

                    Repeater::make('service_items')
                        ->relationship('items')
                        ->label('Stok Ürünleri')
                        ->schema([
                            Select::make('stock_item_id')
                                ->label('Stok Ürünü')
                                ->relationship(
                                    'stockItem',
                                    'barcode',
                                    fn ($query) => $query
                                        ->where('status', StockStatusEnum::AVAILABLE->value)
                                        ->where('dealer_id', Auth::user()?->dealer_id)
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->barcode} - {$record->product->name}")
                                ->columnSpanFull(),

                            Hidden::make('usage_type')
                                ->default(ServiceItemUsageTypeEnum::FULL->value),

                            TextInput::make('notes')
                                ->label('Notlar')
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('Stok Ekle')
                        ->reorderable(false),
                ]),
        ];
    }

    public static function getAppliedPartsStep(): array
    {
        return [
            Section::make('Kaplama Alanları')
                ->schema([
                    CheckboxList::make('applied_parts')
                        ->label('Uygulanan Parçalar')
                        ->options(CarPartEnum::getLabels())
                        ->columns(2)
                        ->descriptions(function ($get) {
                            // Seçilen stok ürünlerinin kategorilerindeki available_parts'ı topla
                            $serviceItems = $get('service_items') ?? [];
                            $availableParts = [];

                            foreach ($serviceItems as $item) {
                                if (isset($item['stock_item_id'])) {
                                    $stockItem = \App\Models\StockItem::find($item['stock_item_id']);
                                    if ($stockItem && $stockItem->product && $stockItem->product->category) {
                                        $categoryParts = $stockItem->product->category->available_parts ?? [];
                                        $availableParts = array_merge($availableParts, $categoryParts);
                                    }
                                }
                            }

                            return array_unique($availableParts);
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function getStatusAndNotesStep(): array
    {
        return [
            Section::make('Durum ve Notlar')
                ->schema([
                    TextInput::make('service_no')
                        ->label('Hizmet Numarası')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('Örn: SRV-2024-001'),

                    Select::make('status')
                        ->label('Durum')
                        ->options(ServiceStatusEnum::getLabels())
                        ->required()
                        ->default(ServiceStatusEnum::DRAFT->value),

                    Textarea::make('notes')
                        ->label('Notlar')
                        ->rows(3)
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        // Normal form için tüm step'leri birleştir (Edit sayfası için)
        return $schema
            ->components([
                ...self::getCustomerAndCarStep(),
                ...self::getStockStep(),
                ...self::getAppliedPartsStep(),
                ...self::getStatusAndNotesStep(),
            ]);
    }
}
