<?php

namespace App\Filament\Resources\NexptgReports\RelationManagers;

use App\Enums\NexptgPartTypeEnum;
use App\Enums\NexptgPlaceIdEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MeasurementsRelationManager extends RelationManager
{
    protected static string $relationship = 'measurements';

    protected static ?string $title = 'Ölçüm Sonuçları';

    protected static ?string $modelLabel = 'Ölçüm';

    protected static ?string $pluralModelLabel = 'Ölçümler';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_inside')
                    ->label('İç Ölçüm')
                    ->required(),

                Select::make('place_id')
                    ->label('Konum')
                    ->options(NexptgPlaceIdEnum::getLabels())
                    ->required()
                    ->searchable(),

                Select::make('part_type')
                    ->label('Parça Tipi')
                    ->options(NexptgPartTypeEnum::getLabels())
                    ->required()
                    ->searchable(),

                TextInput::make('value')
                    ->label('Değer (μm)')
                    ->numeric()
                    ->nullable()
                    ->step(0.01),

                TextInput::make('interpretation')
                    ->label('Yorumlama')
                    ->numeric()
                    ->nullable(),

                TextInput::make('substrate_type')
                    ->label('Altlık Tipi')
                    ->maxLength(255)
                    ->nullable(),

                DateTimePicker::make('timestamp')
                    ->label('Zaman Damgası')
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false)
                    ->nullable(),

                TextInput::make('position')
                    ->label('Pozisyon')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('part_type')
            ->columns([
                Tables\Columns\TextColumn::make('is_inside')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'İç' : 'Dış')
                    ->color(fn ($state) => $state ? 'info' : 'success')
                    ->icon(fn ($state) => $state ? 'heroicon-m-arrow-down-circle' : 'heroicon-m-arrow-up-circle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('place_id')
                    ->label('Konum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => NexptgPlaceIdEnum::getLabels()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('part_type')
                    ->label('Parça Tipi')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof NexptgPartTypeEnum) {
                            return NexptgPartTypeEnum::getLabels()[$state->value] ?? $state->value;
                        }
                        if (is_string($state) && isset(NexptgPartTypeEnum::getLabels()[$state])) {
                            return NexptgPartTypeEnum::getLabels()[$state];
                        }
                        return $state ?? '-';
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Değer (μm)')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('interpretation')
                    ->label('Yorumlama')
                    ->sortable(),

                Tables\Columns\TextColumn::make('substrate_type')
                    ->label('Altlık Tipi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('timestamp')
                    ->label('Zaman Damgası')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Pozisyon')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_inside')
                    ->label('Tip')
                    ->options([
                        false => 'Dış Ölçümler',
                        true => 'İç Ölçümler',
                    ]),

                Tables\Filters\SelectFilter::make('place_id')
                    ->label('Konum')
                    ->options(NexptgPlaceIdEnum::getLabels()),

                Tables\Filters\SelectFilter::make('part_type')
                    ->label('Parça Tipi')
                    ->options(NexptgPartTypeEnum::getLabels()),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc');
    }
}

