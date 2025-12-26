<?php

namespace App\Filament\Resources\NexptgApiUsers\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NexptgApiUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('API Kullanıcı Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('last_used_at')
                    ->label('Son Kullanım')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('Henüz kullanılmadı'),

                TextColumn::make('creator.name')
                    ->label('Oluşturan')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
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
