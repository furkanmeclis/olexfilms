<?php

namespace App\Filament\Resources\Services\RelationManagers;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Stok Ürünleri';

    protected static ?string $modelLabel = 'Stok Ürünü';

    protected static ?string $pluralModelLabel = 'Stok Ürünleri';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('stock_item_id')
                    ->label('Stok Ürünü')
                    ->relationship('stockItem', 'barcode')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(),

                Select::make('usage_type')
                    ->label('Kullanım Tipi')
                    ->options(\App\Enums\ServiceItemUsageTypeEnum::getLabels())
                    ->required()
                    ->disabled(),

                TextInput::make('notes')
                    ->label('Notlar')
                    ->maxLength(255)
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stock_item_id')
            ->columns([
                Tables\Columns\TextColumn::make('stockItem.barcode')
                    ->label('Barkod')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stockItem.product.name')
                    ->label('Ürün')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_type')
                    ->label('Kullanım Tipi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->value === 'full' ? 'success' : 'warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notlar')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Dışa Aktar'),
            ])
            ->actions([
                // Sadece görüntüleme
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
                ]),
            ]);
    }
}
