<?php

namespace App\Filament\Resources\CarBrands\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarBrandInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Marka Bilgileri')
                    ->schema([
                        ImageEntry::make('logo')
                            ->label('Logo')
                            ->circular()
                            ->defaultImageUrl(url('/images/placeholder.png'))
                            ->columnSpanFull(),

                        TextEntry::make('name')
                            ->label('Marka Adı'),

                        TextEntry::make('external_id')
                            ->label('Dış ID'),

                        TextEntry::make('is_active')
                            ->label('Aktif')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),

                        TextEntry::make('last_update')
                            ->label('Son Güncelleme')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Güncellenmedi'),

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
