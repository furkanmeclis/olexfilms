<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Product;
use App\Models\StockItem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdminOrCenterStaff = $user && $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);

        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        Select::make('dealer_id')
                            ->label('Bayi')
                            ->relationship('dealer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->visible(fn () => $isAdminOrCenterStaff || !$user?->dealer_id),

                        Select::make('status')
                            ->label('Durum')
                            ->options(OrderStatusEnum::getLabels())
                            ->default(OrderStatusEnum::PENDING->value)
                            ->required()
                            ->visible(fn () => $isAdminOrCenterStaff),

                        TextInput::make('status')
                            ->label('Durum')
                            ->disabled()
                            ->formatStateUsing(function ($state) {
                                if (is_string($state)) {
                                    return OrderStatusEnum::getLabels()[$state] ?? $state;
                                }
                                if ($state instanceof OrderStatusEnum) {
                                    return OrderStatusEnum::getLabels()[$state->value] ?? $state->value;
                                }
                                return $state;
                            })
                            ->visible(fn ($record) => $record !== null && !$isAdminOrCenterStaff),
                    ])
                    ->columns(2),

                Section::make('Ürünler')
                    ->schema([
                        Repeater::make('items')
                            ->label('Ürünler')
                            ->schema([
                                Hidden::make('id'),

                                Select::make('product_id')
                                    ->label('Ürün')
                                    ->options(function () {
                                        return Product::where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search) => Product::where('is_active', true)
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('sku', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn ($product) => [$product->id => $product->name])
                                        ->toArray())
                                    ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->name)
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('stock_items', []))
                                    ->columnSpanFull(),

                                TextInput::make('quantity')
                                    ->label('Adet')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->helperText(function (Get $get) {
                                        $productId = $get('product_id');
                                        $quantity = $get('quantity') ?? 1;
                                        
                                        if (!$productId) {
                                            return 'Önce ürün seçin';
                                        }

                                        $availableStock = StockItem::where('product_id', $productId)
                                            ->where('location', StockLocationEnum::CENTER->value)
                                            ->where('status', StockStatusEnum::AVAILABLE->value)
                                            ->count();

                                        $selectedStockCount = count($get('stock_items') ?? []);
                                        
                                        if ($selectedStockCount > 0) {
                                            if ($selectedStockCount !== $quantity) {
                                                return "⚠️ Seçilen stok sayısı ({$selectedStockCount}) adet ile eşleşmiyor. Mevcut stok: {$availableStock} adet";
                                            }
                                            return "✓ {$selectedStockCount} stok seçildi. Mevcut stok: {$availableStock} adet";
                                        }

                                        return "Mevcut stok: {$availableStock} adet (En az {$quantity} stok seçmelisiniz)";
                                    })
                                    ->columnSpanFull(),

                                Select::make('stock_items')
                                    ->label('Stok Kodları')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated(true)
                                    ->options(function (Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return [];
                                        }

                                        // Müsait stoklar (CENTER lokasyonunda)
                                        $availableStock = StockItem::where('product_id', $productId)
                                            ->where('location', StockLocationEnum::CENTER->value)
                                            ->where('status', StockStatusEnum::AVAILABLE->value)
                                            ->get();

                                        // Edit modunda: Bu order item'a atanmış stokları da ekle
                                        $assignedStock = collect();
                                        $orderItemId = $get('id');
                                        if ($orderItemId) {
                                            $orderItem = \App\Models\OrderItem::find($orderItemId);
                                            if ($orderItem) {
                                                $assignedStock = $orderItem->stockItems()
                                                    ->where('product_id', $productId)
                                                    ->get();
                                            }
                                        }

                                        // Tüm stokları birleştir (duplicate'leri önle)
                                        $allStock = $availableStock->merge($assignedStock)->unique('id');

                                        return $allStock->mapWithKeys(function ($stock) {
                                            $statusLabel = match($stock->status->value) {
                                                StockStatusEnum::RESERVED->value => ' (Rezerve)',
                                                StockStatusEnum::USED->value => ' (Kullanıldı)',
                                                default => '',
                                            };
                                            $locationLabel = match($stock->location->value) {
                                                StockLocationEnum::DEALER->value => ' - Bayi',
                                                StockLocationEnum::SERVICE->value => ' - Servis',
                                                default => '',
                                            };
                                            $label = "{$stock->barcode} - SKU: {$stock->sku}{$statusLabel}{$locationLabel}";
                                            return [$stock->id => $label];
                                        })->toArray();
                                    })
                                    ->getSearchResultsUsing(function (string $search, Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return [];
                                        }

                                        // Müsait stoklar (CENTER lokasyonunda)
                                        $availableStock = StockItem::where('product_id', $productId)
                                            ->where('location', StockLocationEnum::CENTER->value)
                                            ->where('status', StockStatusEnum::AVAILABLE->value)
                                            ->where(function ($query) use ($search) {
                                                $query->where('barcode', 'like', "%{$search}%")
                                                    ->orWhere('sku', 'like', "%{$search}%");
                                            })
                                            ->limit(50)
                                            ->get();

                                        // Edit modunda: Bu order item'a atanmış stokları da ekle
                                        $assignedStock = collect();
                                        $orderItemId = $get('id');
                                        if ($orderItemId) {
                                            $orderItem = \App\Models\OrderItem::find($orderItemId);
                                            if ($orderItem) {
                                                $assignedStock = $orderItem->stockItems()
                                                    ->where('product_id', $productId)
                                                    ->where(function ($query) use ($search) {
                                                        $query->where('barcode', 'like', "%{$search}%")
                                                            ->orWhere('sku', 'like', "%{$search}%");
                                                    })
                                                    ->get();
                                            }
                                        }

                                        // Tüm stokları birleştir
                                        $allStock = $availableStock->merge($assignedStock)->unique('id');

                                        return $allStock->mapWithKeys(function ($stock) {
                                            $statusLabel = match($stock->status->value) {
                                                StockStatusEnum::RESERVED->value => ' (Rezerve)',
                                                StockStatusEnum::USED->value => ' (Kullanıldı)',
                                                default => '',
                                            };
                                            $locationLabel = match($stock->location->value) {
                                                StockLocationEnum::DEALER->value => ' - Bayi',
                                                StockLocationEnum::SERVICE->value => ' - Servis',
                                                default => '',
                                            };
                                            $label = "{$stock->barcode} - SKU: {$stock->sku}{$statusLabel}{$locationLabel}";
                                            return [$stock->id => $label];
                                        })->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $stock = StockItem::find($value);
                                        if (!$stock) {
                                            return null;
                                        }
                                        $statusLabel = match($stock->status->value) {
                                            StockStatusEnum::RESERVED->value => ' (Rezerve)',
                                            StockStatusEnum::USED->value => ' (Kullanıldı)',
                                            default => '',
                                        };
                                        $locationLabel = match($stock->location->value) {
                                            StockLocationEnum::DEALER->value => ' - Bayi',
                                            StockLocationEnum::SERVICE->value => ' - Servis',
                                            default => '',
                                        };
                                        return "{$stock->barcode} - SKU: {$stock->sku}{$statusLabel}{$locationLabel}";
                                    })
                                    ->default(function (Get $get) {
                                        // Edit modunda, mevcut stok atamalarını default olarak göster
                                        $orderItemId = $get('id');
                                        if ($orderItemId) {
                                            $orderItem = \App\Models\OrderItem::find($orderItemId);
                                            if ($orderItem) {
                                                return $orderItem->stockItems->pluck('id')->toArray();
                                            }
                                        }
                                        return [];
                                    })
                                    ->helperText(function (Get $get) {
                                        $productId = $get('product_id');
                                        $quantity = $get('quantity') ?? 1;
                                        
                                        if (!$productId) {
                                            return 'Önce ürün seçin';
                                        }

                                        $availableStock = StockItem::where('product_id', $productId)
                                            ->where('location', StockLocationEnum::CENTER->value)
                                            ->where('status', StockStatusEnum::AVAILABLE->value)
                                            ->count();

                                        if ($availableStock < $quantity) {
                                            return "⚠️ Yetersiz stok! Mevcut: {$availableStock}, İstenen: {$quantity}";
                                        }

                                        return "Mevcut stok: {$availableStock} adet. En az {$quantity} stok seçmelisiniz.";
                                    })
                                    ->validationMessages([
                                        'required' => 'En az bir stok seçmelisiniz.',
                                    ])
                                    ->visible(fn () => $isAdminOrCenterStaff)
                                    ->columnSpanFull(),

                                Placeholder::make('stock_info')
                                    ->label('Stok Bilgisi')
                                    ->content(function (Get $get, $record) use ($isAdminOrCenterStaff) {
                                        if ($isAdminOrCenterStaff) {
                                            return '';
                                        }

                                        return 'Stok ataması yapılamaz (sadece admin/merkez çalışanları yapabilir)';
                                    })
                                    ->visible(fn () => !$isAdminOrCenterStaff)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Ürün Ekle')
                            ->reorderable(false)
                            ->required(),
                    ]),

                Section::make('Kargo Bilgileri')
                    ->schema([
                        TextInput::make('cargo_company')
                            ->label('Kargo Firması')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('tracking_number')
                            ->label('Takip Numarası')
                            ->maxLength(255)
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('Notlar')
                            ->rows(3)
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->visible(function ($record) {
                        if ($record === null) {
                            return false;
                        }
                        $status = $record->status;
                        $statusValue = $status instanceof OrderStatusEnum ? $status->value : (is_string($status) ? $status : 'pending');
                        return in_array($statusValue, ['processing', 'shipped', 'delivered']);
                    }),
            ]);
    }
}
