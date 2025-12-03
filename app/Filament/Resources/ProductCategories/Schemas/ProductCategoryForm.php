<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Enums\CarPartEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategori Bilgileri')
                    ->schema([
                        TextInput::make('name')
                            ->label('Kategori Adı')
                            ->required()
                            ->maxLength(255),

                        CheckboxList::make('available_parts')
                            ->label('Uygulanabilir Parçalar')
                            ->options(CarPartEnum::getLabels())
                            ->columns(2)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }
}
