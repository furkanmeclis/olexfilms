<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\CarPartEnum;
use App\Enums\CustomerTypeEnum;
use App\Enums\ServiceItemUsageTypeEnum;
use App\Enums\ServiceStatusEnum;
use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Forms\Components\CarPartPicker;
use App\Filament\Forms\Components\ServiceNumberInput;
use App\Filament\Forms\Components\StockItemPicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

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

    public static function getCustomerAndCarStep(?ServiceStatusEnum $currentStatus = null): array
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));
        $currentYear = (int) date('Y');
        $years = range($currentYear, 1975);
        $isLocked = $currentStatus === ServiceStatusEnum::COMPLETED 
            || $currentStatus === ServiceStatusEnum::CANCELLED;

        [$cityData, $cityDistrictMap] = self::getCityData();

        return [
            Section::make('Müşteri Seçimi')
                ->schema([
                    // Admin için önce bayi seçimi
                    Select::make('dealer_id')
                        ->label('Bayi')
                        ->relationship('dealer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled($isLocked)
                        ->live()
                        ->columnSpanFull()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('customer_id', null))
                        ->visible(fn () => $isAdmin),

                    // Bayi çalışanları için hidden dealer_id
                    Hidden::make('dealer_id')
                        ->default(fn () => $user->dealer_id ?? null)
                        ->visible(fn () => !$isAdmin),

                    // Müşteri seçimi - admin için bayi bazlı filtreleme
                    Select::make('customer_id')
                        ->label('Müşteri')
                        ->relationship(
                            'customer',
                            'name',
                            function ($query, $get) use ($isAdmin, $user) {
                                $dealerId = $isAdmin ? $get('dealer_id') : ($user?->dealer_id);
                                
                                if ($dealerId) {
                                    $query->where('dealer_id', $dealerId);
                                } else {
                                    // Eğer dealer_id yoksa hiçbir şey gösterme
                                    $query->whereRaw('1 = 0');
                                }
                                
                                return $query;
                            }
                        )
                        ->getSearchResultsUsing(function (string $search, $get) use ($isAdmin, $user) {
                            $dealerId = $isAdmin ? $get('dealer_id') : ($user?->dealer_id);
                            
                            if (!$dealerId) {
                                return [];
                            }
                            
                            return \App\Models\Customer::query()
                                ->where('dealer_id', $dealerId)
                                ->where('name', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($customer) => [$customer->getKey() => $customer->name]);
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => \App\Models\Customer::find($value)?->name)
                        ->searchable()
                        ->preload(fn () => !$isAdmin) // Admin için preload kullanma, çünkü dealer_id seçilene kadar boş olacak
                        ->required()
                        ->disabled(fn ($get) => ($isAdmin && !$get('dealer_id')) || $isLocked)
                        ->reactive()
                        ->live()
                        ->createOptionForm([
                            // Bayi ID - herkes için hidden, default olarak seçilen bayi gelir
                            Hidden::make('dealer_id')
                                ->default(function () use ($isAdmin, $user) {
                                    // Bayi çalışanları için kullanıcının dealer_id'sini al
                                    if (!$isAdmin) {
                                        return $user->dealer_id ?? null;
                                    }
                                    // Admin için createOptionUsing callback'inde set edilecek
                                    return null;
                                }),

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

                            Hidden::make('created_by')
                                ->default(fn () => Auth::id()),
                        ])
                        ->createOptionUsing(function (array $data, $livewire) use ($isAdmin) {
                            // Admin ise parent form state'inden dealer_id'yi al ve set et
                            if ($isAdmin) {
                                $formData = $livewire->form->getState();
                                $parentDealerId = $formData['dealer_id'] ?? null;
                                if ($parentDealerId) {
                                    $data['dealer_id'] = $parentDealerId;
                                }
                            }
                            
                            $customer = \App\Models\Customer::create($data);
                            
                            // Yeni oluşturulan müşteriyi döndür
                            return $customer->getKey();
                        })
                        ->columnSpanFull(),

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
                        ->disabled($isLocked)
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('car_model_id', null)),

                    Select::make('car_model_id')
                        ->label('Model')
                        ->relationship('carModel', 'name', fn ($query, $get) => $query->where('brand_id', $get('car_brand_id')))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn ($get) => !$get('car_brand_id') || $isLocked)
                        ->reactive(),

                    Select::make('year')
                        ->label('Yıl')
                        ->options(array_combine($years, $years))
                        ->required()
                        ->disabled($isLocked)
                        ->default($currentYear),

                    TextInput::make('vin')
                        ->label('Şasi No')
                        ->disabled($isLocked)
                        ->maxLength(255),

                    TextInput::make('plate')
                        ->label('Plaka')
                        ->required()
                        ->disabled($isLocked)
                        ->maxLength(255),

                    TextInput::make('km')
                        ->label('Kilometre')
                        ->numeric()
                        ->disabled($isLocked)
                        ->minValue(0),

                    TextInput::make('package')
                        ->label('Paket')
                        ->disabled($isLocked)
                        ->maxLength(255),
                ])
                ->columns(2),
        ];
    }

    public static function getStockStep(?ServiceStatusEnum $currentStatus = null): array
    {
        $isLocked = $currentStatus === ServiceStatusEnum::COMPLETED 
            || $currentStatus === ServiceStatusEnum::CANCELLED
            || $currentStatus === ServiceStatusEnum::READY;
        
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
                        ->disabled($isLocked)
                        ->addable(!$isLocked)
                        ->deletable(!$isLocked)
                        ->live()
                        ->schema([
                            StockItemPicker::make('stock_item_id')
                                ->label('Stok Ürünü')
                                ->required()
                                ->disabled($isLocked)
                                ->live()
                                ->key(function (Get $get): string {
                                    // applied_parts değiştiğinde component'i yeniden render et
                                    $appliedParts = $get('../../applied_parts') 
                                        ?? $get('../../../applied_parts') 
                                        ?? $get('applied_parts') 
                                        ?? [];
                                    
                                    if (is_string($appliedParts)) {
                                        $appliedParts = json_decode($appliedParts, true) ?? [];
                                    }
                                    
                                    $partsKey = is_array($appliedParts) ? implode(',', $appliedParts) : '';
                                    $dealerId = $get('../../dealer_id') 
                                        ?? $get('../../../dealer_id') 
                                        ?? $get('dealer_id') 
                                        ?? 'none';
                                    
                                    return 'stock-picker-' . $dealerId . '-' . md5($partsKey);
                                })
                                ->appliedParts(function (Get $get): array {
                                    // #region agent log
                                    $logPath = base_path('.cursor/debug.log');
                                    $logData = [
                                        'sessionId' => 'debug-session',
                                        'runId' => 'run1',
                                        'hypothesisId' => 'C',
                                        'location' => 'ServiceForm::getStockStep::appliedParts',
                                        'message' => 'appliedParts closure called',
                                        'data' => [],
                                        'timestamp' => (int)(microtime(true) * 1000),
                                    ];
                                    file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
                                    // #endregion
                                    
                                    // Component içinde otomatik path çözümleme yapılıyor
                                    // Burada sadece Get instance'ı geçiyoruz, component kendi içinde çözümleyecek
                                    // Ancak direkt erişim de deneyebiliriz
                                    try {
                                        $appliedParts = $get('applied_parts') 
                                            ?? $get('../applied_parts') 
                                            ?? $get('../../applied_parts') 
                                            ?? $get('../../../applied_parts') 
                                            ?? [];
                                        
                                        // #region agent log
                                        $logData2 = [
                                            'sessionId' => 'debug-session',
                                            'runId' => 'run1',
                                            'hypothesisId' => 'C',
                                            'location' => 'ServiceForm::getStockStep::appliedParts',
                                            'message' => 'appliedParts retrieved',
                                            'data' => ['appliedParts' => $appliedParts, 'type' => gettype($appliedParts)],
                                            'timestamp' => (int)(microtime(true) * 1000),
                                        ];
                                        file_put_contents($logPath, json_encode($logData2) . "\n", FILE_APPEND);
                                        // #endregion
                                        
                                        if (is_string($appliedParts)) {
                                            $appliedParts = json_decode($appliedParts, true) ?? [];
                                        }
                                        
                                        // #region agent log
                                        $logData3 = [
                                            'sessionId' => 'debug-session',
                                            'runId' => 'run1',
                                            'hypothesisId' => 'C',
                                            'location' => 'ServiceForm::getStockStep::appliedParts',
                                            'message' => 'appliedParts final',
                                            'data' => ['appliedParts' => $appliedParts, 'is_array' => is_array($appliedParts), 'count' => is_array($appliedParts) ? count($appliedParts) : 0],
                                            'timestamp' => (int)(microtime(true) * 1000),
                                        ];
                                        file_put_contents($logPath, json_encode($logData3) . "\n", FILE_APPEND);
                                        // #endregion
                                        
                                        return is_array($appliedParts) ? $appliedParts : [];
                                    } catch (\Exception $e) {
                                        // #region agent log
                                        $logData4 = [
                                            'sessionId' => 'debug-session',
                                            'runId' => 'run1',
                                            'hypothesisId' => 'C',
                                            'location' => 'ServiceForm::getStockStep::appliedParts',
                                            'message' => 'appliedParts exception',
                                            'data' => ['error' => $e->getMessage()],
                                            'timestamp' => (int)(microtime(true) * 1000),
                                        ];
                                        file_put_contents($logPath, json_encode($logData4) . "\n", FILE_APPEND);
                                        // #endregion
                                        return [];
                                    }
                                })
                                ->dealerId(function (Get $get) {
                                    // #region agent log
                                    $logPath = base_path('.cursor/debug.log');
                                    $logData = [
                                        'sessionId' => 'debug-session',
                                        'runId' => 'run1',
                                        'hypothesisId' => 'D',
                                        'location' => 'ServiceForm::getStockStep::dealerId',
                                        'message' => 'dealerId closure called',
                                        'data' => [],
                                        'timestamp' => (int)(microtime(true) * 1000),
                                    ];
                                    file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
                                    // #endregion
                                    
                                    $user = Auth::user();
                                    if($user && $user->hasAnyRole([UserRoleEnum::SUPER_ADMIN->value, UserRoleEnum::CENTER_STAFF->value])) {
                                        // Admin için wizard'dan dealer_id al - KRİTİK: Doğru dealer_id'yi almalı
                                        try {
                                            // Wizard root'tan dealer_id'yi al
                                            $dealerId = $get('../../../dealer_id') 
                                                ?? $get('../../dealer_id') 
                                                ?? $get('../dealer_id') 
                                                ?? $get('dealer_id');
                                            
                                            // #region agent log
                                            $logData2 = [
                                                'sessionId' => 'debug-session',
                                                'runId' => 'run1',
                                                'hypothesisId' => 'D',
                                                'location' => 'ServiceForm::getStockStep::dealerId',
                                                'message' => 'dealerId from paths',
                                                'data' => ['dealerId' => $dealerId, 'user_is_admin' => true],
                                                'timestamp' => (int)(microtime(true) * 1000),
                                            ];
                                            file_put_contents($logPath, json_encode($logData2) . "\n", FILE_APPEND);
                                            // #endregion
                                            
                                            // Eğer hala null ise, form state'inden al
                                            if (!$dealerId) {
                                                // Livewire form state'ine erişmeyi dene
                                                $livewire = app('livewire')->current();
                                                if ($livewire && method_exists($livewire, 'get')) {
                                                    $formData = $livewire->form->getState();
                                                    $dealerId = $formData['dealer_id'] ?? null;
                                                    
                                                    // #region agent log
                                                    $logData3 = [
                                                        'sessionId' => 'debug-session',
                                                        'runId' => 'run1',
                                                        'hypothesisId' => 'D',
                                                        'location' => 'ServiceForm::getStockStep::dealerId',
                                                        'message' => 'dealerId from livewire state',
                                                        'data' => ['dealerId' => $dealerId, 'formData_keys' => array_keys($formData ?? [])],
                                                        'timestamp' => (int)(microtime(true) * 1000),
                                                    ];
                                                    file_put_contents($logPath, json_encode($logData3) . "\n", FILE_APPEND);
                                                    // #endregion
                                                }
                                            }
                                            
                                            // #region agent log
                                            $logData4 = [
                                                'sessionId' => 'debug-session',
                                                'runId' => 'run1',
                                                'hypothesisId' => 'D',
                                                'location' => 'ServiceForm::getStockStep::dealerId',
                                                'message' => 'dealerId final (admin)',
                                                'data' => ['dealerId' => $dealerId],
                                                'timestamp' => (int)(microtime(true) * 1000),
                                            ];
                                            file_put_contents($logPath, json_encode($logData4) . "\n", FILE_APPEND);
                                            // #endregion
                                            
                                            return $dealerId ? (int) $dealerId : null;
                                        } catch (\Exception $e) {
                                            // #region agent log
                                            $logData5 = [
                                                'sessionId' => 'debug-session',
                                                'runId' => 'run1',
                                                'hypothesisId' => 'D',
                                                'location' => 'ServiceForm::getStockStep::dealerId',
                                                'message' => 'dealerId exception (admin)',
                                                'data' => ['error' => $e->getMessage()],
                                                'timestamp' => (int)(microtime(true) * 1000),
                                            ];
                                            file_put_contents($logPath, json_encode($logData5) . "\n", FILE_APPEND);
                                            // #endregion
                                            return null;
                                        }
                                    } else {
                                        // Bayi çalışanı için kullanıcının dealer_id'sini kullan
                                        $dealerId = $user?->dealer?->id;
                                        
                                        // #region agent log
                                        $logData6 = [
                                            'sessionId' => 'debug-session',
                                            'runId' => 'run1',
                                            'hypothesisId' => 'D',
                                            'location' => 'ServiceForm::getStockStep::dealerId',
                                            'message' => 'dealerId final (dealer staff)',
                                            'data' => ['dealerId' => $dealerId, 'user_id' => $user?->id],
                                            'timestamp' => (int)(microtime(true) * 1000),
                                        ];
                                        file_put_contents($logPath, json_encode($logData6) . "\n", FILE_APPEND);
                                        // #endregion
                                        
                                        return $dealerId;
                                    }
                                })
                                ->columnSpanFull(),

                            Hidden::make('usage_type')
                                ->default(ServiceItemUsageTypeEnum::FULL->value),

                            TextInput::make('notes')
                                ->label('Notlar')
                                ->maxLength(255)
                                ->disabled($isLocked)
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel('Stok Ekle')
                        ->reorderable(false),
                ]),
        ];
    }

    public static function getAppliedPartsStep(?ServiceStatusEnum $currentStatus = null): array
    {
        $isLocked = $currentStatus === ServiceStatusEnum::COMPLETED 
            || $currentStatus === ServiceStatusEnum::CANCELLED
            || $currentStatus === ServiceStatusEnum::READY;
        
        return [
            Section::make('Kaplama Alanları')
                ->schema([
                    CarPartPicker::make('applied_parts')
                        ->label('Uygulanan Parçalar')
                        ->required()
                        ->disabled($isLocked)
                        ->live()
                        ->helperText($isLocked ? 'Tamamlanan, iptal edilen veya hazır durumundaki hizmetlerde kaplama alanları değiştirilemez.' : 'Araç parçalarını görsel olarak seçin'),
                ]),
        ];
    }

    public static function getStatusAndNotesStep(?ServiceStatusEnum $currentStatus = null): array
    {
        $isLocked = $currentStatus === ServiceStatusEnum::COMPLETED 
            || $currentStatus === ServiceStatusEnum::CANCELLED;
        $options = [
            ServiceStatusEnum::DRAFT->value => ServiceStatusEnum::DRAFT->getLabel(),
            ServiceStatusEnum::PENDING->value => ServiceStatusEnum::PENDING->getLabel(),
            ServiceStatusEnum::PROCESSING->value => ServiceStatusEnum::PROCESSING->getLabel(),
        ];
        if ($isLocked) {
            $options[ServiceStatusEnum::READY->value] = ServiceStatusEnum::READY->getLabel();
            $options[ServiceStatusEnum::COMPLETED->value] = ServiceStatusEnum::COMPLETED->getLabel();
            $options[ServiceStatusEnum::CANCELLED->value] = ServiceStatusEnum::CANCELLED->getLabel();
        }
        return [
            Section::make('Durum ve Notlar')
            ->columns(1)
                ->schema([
                    ServiceNumberInput::make('service_no')
                        ->label('Hizmet Numarası')
                        ->required()
                        ->disabled($isLocked)
                        ->helperText($isLocked ? 'Tamamlanan veya iptal edilen hizmetlerde hizmet numarası değiştirilemez.' : 'Hizmet numarasını girin veya QR kod okutun (backend\'de unique kontrolü yapılacak)'),

                    Radio::make('status')
                        ->label('Durum') 
                        ->options($options)
                        ->default($currentStatus?->value ?? ServiceStatusEnum::PROCESSING->value)
                        ->inline()
                        ->required()
                        ->disabled($isLocked)
                        ->helperText($isLocked ? 'Tamamlanan veya iptal edilen hizmetlerde durum değiştirilemez.' : null),

                    Textarea::make('notes')
                        ->label('Notlar')
                        ->rows(3)
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function getGalleryStep(?ServiceStatusEnum $currentStatus = null): array
    {
        $isLocked = $currentStatus === ServiceStatusEnum::COMPLETED 
            || $currentStatus === ServiceStatusEnum::CANCELLED;
        
        return [
            Section::make('Galeri')
                ->schema([
                    Repeater::make('images')
                        ->relationship('images')
                        ->label('Galeri Görselleri')
                        ->disabled($isLocked)
                        ->addable(!$isLocked)
                        ->deletable(!$isLocked)
                        ->reorderable(!$isLocked)
                        ->orderColumn('order')
                        ->schema([
                            FileUpload::make('image_path')
                                ->label('Görsel')
                                ->image()
                                ->disk(config('filesystems.default'))
                                ->directory('services/gallery')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('title')
                                ->label('Başlık')
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Görsel Ekle')
                        ->columns(2),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        // Mevcut kaydın durumunu al (edit sayfasında)
        $record = $schema->getRecord();
        $currentStatus = $record?->status ?? null;
        
        // Normal form için tüm step'leri birleştir (Edit sayfası için)
        // Mevcut durumu her step metoduna geç
        return $schema
            ->components([
                ...self::getCustomerAndCarStep($currentStatus),
                ...self::getAppliedPartsStep($currentStatus),
                ...self::getStockStep($currentStatus),
                ...self::getStatusAndNotesStep($currentStatus),
            ]);
    }
}
