<?php

namespace App\Filament\Resources\Users\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad Soyad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),

                TextColumn::make('dealer.name')
                    ->label('Bayi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->searchable(),

                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('E-posta Doğrulandı')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Dışa Aktar'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                EditAction::make()
                    ->label('Düzenle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
                    DeleteBulkAction::make()
                        ->label('Sil'),
                ]),
            ]);
    }
}
