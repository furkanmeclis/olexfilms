<?php

namespace App\Filament\Resources\CarModels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CarModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Model Bilgileri')
                    ->schema([
                        TextEntry::make('brand.name')
                            ->label('Marka'),

                        TextEntry::make('name')
                            ->label('Model Adı'),

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
