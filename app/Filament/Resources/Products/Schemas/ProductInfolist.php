<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\UserRoleEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);

        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        ImageEntry::make('image_path')
                            ->label('Ürün Görseli')
                            ->circular()
                            ->defaultImageUrl(url('/images/placeholder.png'))
                            ->columnSpanFull(),

                        TextEntry::make('name')
                            ->label('Ürün Adı'),

                        TextEntry::make('sku')
                            ->label('Stok Kodu'),

                        TextEntry::make('category.name')
                            ->label('Kategori'),

                        TextEntry::make('is_active')
                            ->label('Aktif')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                    ])
                    ->columns(2),

                Section::make('Açıklama')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Açıklama')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Fiyat ve Garanti')
                    ->schema([
                        TextEntry::make('warranty_duration')
                            ->label('Garanti Süresi')
                            ->formatStateUsing(fn ($state) => $state ? "{$state} Ay" : 'Belirtilmemiş'),

                        TextEntry::make('price')
                            ->label('Fiyat')
                            ->money('USD')
                            ->visible(fn () => $isSuperAdmin),
                    ])
                    ->columns(2),

                Section::make('Zaman Bilgileri')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Oluşturulma')
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Güncellenme')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
