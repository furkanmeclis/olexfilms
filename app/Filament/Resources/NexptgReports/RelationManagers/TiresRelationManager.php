<?php

namespace App\Filament\Resources\NexptgReports\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TiresRelationManager extends RelationManager
{
    protected static string $relationship = 'tires';

    protected static ?string $title = 'Lastik Bilgileri';

    protected static ?string $modelLabel = 'Lastik';

    protected static ?string $pluralModelLabel = 'Lastikler';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('width')
                    ->label('Genişlik')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('profile')
                    ->label('Profil')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('diameter')
                    ->label('Çap')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('maker')
                    ->label('Marka')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('season')
                    ->label('Mevsim')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('section')
                    ->label('Konum')
                    ->maxLength(255)
                    ->nullable(),

                TextInput::make('value1')
                    ->label('Diş Derinliği 1 (mm)')
                    ->numeric()
                    ->nullable()
                    ->step(0.01),

                TextInput::make('value2')
                    ->label('Diş Derinliği 2 (mm)')
                    ->numeric()
                    ->nullable()
                    ->step(0.01),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('section')
            ->columns([
                Tables\Columns\TextColumn::make('section')
                    ->label('Konum')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('width')
                    ->label('Genişlik')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('profile')
                    ->label('Profil')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('diameter')
                    ->label('Çap')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('maker')
                    ->label('Marka')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('season')
                    ->label('Mevsim')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value1')
                    ->label('Diş Derinliği 1 (mm)')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('value2')
                    ->label('Diş Derinliği 2 (mm)')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.',
                    )
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('season')
                    ->label('Mevsim')
                    ->options([
                        'Summer' => 'Yaz',
                        'Winter' => 'Kış',
                        'All Season' => 'Dört Mevsim',
                    ]),

                Tables\Filters\SelectFilter::make('maker')
                    ->label('Marka')
                    ->options(function () {
                        return \App\Models\NexptgReportTire::distinct()
                            ->whereNotNull('maker')
                            ->pluck('maker', 'maker')
                            ->toArray();
                    }),
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
            ->defaultSort('section', 'asc');
    }
}
