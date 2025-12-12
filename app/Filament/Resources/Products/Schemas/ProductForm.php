<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\UserRoleEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);

        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Ürün Adı')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('sku')
                            ->label('Stok Kodu')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Açıklama')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Açıklama')
                            ->columnSpanFull(),
                    ]),

                Section::make('Fiyat ve Garanti')
                    ->schema([
                        TextInput::make('warranty_duration')
                            ->label('Garanti Süresi')
                            ->numeric()
                            ->suffix('Ay')
                            ->minValue(0)
                            ->nullable(),

                        TextInput::make('micron_thickness')
                            ->label('Mikron Kalınlığı')
                            ->numeric()
                            ->suffix('μm')
                            ->minValue(0)
                            ->nullable()
                            ->helperText('Mikrometre cinsinden kalınlık değeri'),

                        TextInput::make('price')
                            ->label('Fiyat (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->visible(fn () => Auth::user()?->hasRole(UserRoleEnum::SUPER_ADMIN->value) ?? false),
                    ])
                    ->columns(2),

                Section::make('Görsel')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Ürün Görseli')
                            ->image()
                            ->directory('products/images')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
