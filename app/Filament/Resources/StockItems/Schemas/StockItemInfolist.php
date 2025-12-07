<?php

namespace App\Filament\Resources\StockItems\Schemas;

use App\Enums\StockLocationEnum;
use App\Enums\StockStatusEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        TextEntry::make('barcode')
                            ->label('Barkod'),

                        TextEntry::make('product.name')
                            ->label('Ürün Adı'),

                        TextEntry::make('sku')
                            ->label('Stok Kodu'),

                        TextEntry::make('location')
                            ->label('Konum')
                            ->formatStateUsing(fn ($state) => StockLocationEnum::getLabels()[$state->value] ?? $state->value)
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Durum')
                            ->formatStateUsing(fn ($state) => StockStatusEnum::getLabels()[$state->value] ?? $state->value)
                            ->badge()
                            ->color(fn ($state) => match ($state->value) {
                                'available' => 'success',
                                'reserved' => 'warning',
                                'used' => 'gray',
                                default => 'gray',
                            }),

                        TextEntry::make('dealer.name')
                            ->label('Bayi')
                            ->placeholder('Merkez'),

                        TextEntry::make('created_at')
                            ->label('Oluşturulma Tarihi')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Hareket Geçmişi')
                    ->schema([
                        RepeatableEntry::make('movements')
                            ->label('')
                            ->schema([
                                TextEntry::make('action')
                                    ->label('Aksiyon')
                                    ->formatStateUsing(fn ($state) => \App\Enums\StockMovementActionEnum::getLabels()[$state->value] ?? $state->value)
                                    ->badge(),
                                TextEntry::make('description')
                                    ->label('Açıklama')
                                    ->columnSpan(2),
                                TextEntry::make('user.name')
                                    ->label('Kullanıcı'),
                                TextEntry::make('created_at')
                                    ->label('Tarih')
                                    ->dateTime('d.m.Y H:i'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->movements->count() > 0),
            ]);
    }
}
