<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatusEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = auth()->user()?->hasRole('super_admin') ?? false;

        return $schema
            ->components([
                Section::make('Sipariş Bilgileri')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Sipariş No'),

                        TextEntry::make('dealer.name')
                            ->label('Bayi'),

                        TextEntry::make('status')
                            ->label('Durum')
                            ->formatStateUsing(fn ($state) => OrderStatusEnum::getLabels()[$state->value] ?? $state->value)
                            ->badge()
                            ->color(fn ($state) => match ($state->value) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('total_amount')
                            ->label('Toplam Tutar')
                            ->money('USD')
                            ->visible(fn () => $isSuperAdmin),

                        TextEntry::make('created_at')
                            ->label('Oluşturulma Tarihi')
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('creator.name')
                            ->label('Oluşturan'),
                    ])
                    ->columns(2),

                Section::make('Sipariş Kalemleri')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Ürün'),
                                TextEntry::make('quantity')
                                    ->label('Adet'),
                                TextEntry::make('unit_price')
                                    ->label('Birim Fiyat')
                                    ->money('USD')
                                    ->visible(fn () => $isSuperAdmin),
                                TextEntry::make('stockItems.barcode')
                                    ->label('Atanan Stoklar')
                                    ->badge()
                                    ->listWithLineBreaks()
                                    ->placeholder('Henüz atanmadı'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->items->count() > 0),

                Section::make('Kargo Bilgileri')
                    ->schema([
                        TextEntry::make('cargo_company')
                            ->label('Kargo Firması')
                            ->placeholder('Belirtilmedi'),

                        TextEntry::make('tracking_number')
                            ->label('Takip Numarası')
                            ->placeholder('Belirtilmedi'),

                        TextEntry::make('notes')
                            ->label('Notlar')
                            ->placeholder('Not yok')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => in_array($record->status->value, ['processing', 'shipped', 'delivered'])),
            ]);
    }
}
