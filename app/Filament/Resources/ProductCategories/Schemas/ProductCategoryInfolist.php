<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Enums\CarPartEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategori Bilgileri')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Kategori Adı'),

                        TextEntry::make('available_parts')
                            ->label('Uygulanabilir Parçalar')
                            ->badge()
                            ->formatStateUsing(function ($state) {
                                if (! is_array($state) || empty($state)) {
                                    return 'Belirtilmemiş';
                                }
                                $labels = CarPartEnum::getLabels();
                                $formatted = array_map(fn ($part) => $labels[$part] ?? $part, $state);
                                return implode(', ', $formatted);
                            })
                            ->columnSpanFull(),

                        TextEntry::make('is_active')
                            ->label('Aktif')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),

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
