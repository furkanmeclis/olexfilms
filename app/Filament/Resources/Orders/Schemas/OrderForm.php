<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\UserRoleEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);

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
                            ->visible(fn () => $isSuperAdmin || !$user?->dealer_id),

                        TextInput::make('status')
                            ->label('Durum')
                            ->disabled()
                            ->default('pending')
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->columns(2),

                Section::make('Ürünler')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('Ürünler')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Ürün')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->columnSpanFull(),

                                TextInput::make('quantity')
                                    ->label('Adet')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->columnSpanFull(),

                                TextInput::make('unit_price')
                                    ->label('Birim Fiyat')
                                    ->numeric()
                                    ->prefix('$')
                                    ->visible(fn () => $isSuperAdmin)
                                    ->hidden(fn () => !$isSuperAdmin)
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
                    ->visible(fn ($record) => $record !== null && in_array($record->status->value, ['processing', 'shipped', 'delivered'])),

                Section::make('Özet')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Toplam Tutar')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn () => $isSuperAdmin),
                    ])
                    ->visible(fn ($record) => $record !== null && $isSuperAdmin),
            ]);
    }
}
