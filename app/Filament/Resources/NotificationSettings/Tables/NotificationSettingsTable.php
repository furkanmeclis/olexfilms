<?php

namespace App\Filament\Resources\NotificationSettings\Tables;

use App\Enums\NotificationEventEnum;
use App\Enums\NotificationPriorityEnum;
use App\Enums\NotificationStatusEnum;
use App\Enums\UserRoleEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class NotificationSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event')
                    ->label('Event')
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof NotificationEventEnum ? $state->value : $state;
                        return NotificationEventEnum::getLabels()[$value] ?? $value;
                    })
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Rol')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        UserRoleEnum::SUPER_ADMIN->value => 'Süper Admin',
                        UserRoleEnum::CENTER_STAFF->value => 'Merkez Çalışanı',
                        UserRoleEnum::DEALER_OWNER->value => 'Bayi Sahibi',
                        UserRoleEnum::DEALER_STAFF->value => 'Bayi Çalışanı',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        UserRoleEnum::SUPER_ADMIN->value => 'danger',
                        UserRoleEnum::CENTER_STAFF->value => 'info',
                        UserRoleEnum::DEALER_OWNER->value => 'warning',
                        UserRoleEnum::DEALER_STAFF->value => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('Öncelik')
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof NotificationPriorityEnum ? $state->value : $state;
                        return NotificationPriorityEnum::getLabels()[$value] ?? $value;
                    })
                    ->badge()
                    ->color(function ($state): string {
                        $value = $state instanceof NotificationPriorityEnum ? $state->value : $state;
                        return match ($value) {
                            NotificationPriorityEnum::CRITICAL->value => 'danger',
                            NotificationPriorityEnum::HIGH->value => 'warning',
                            NotificationPriorityEnum::MEDIUM->value => 'info',
                            NotificationPriorityEnum::LOW->value => 'success',
                            default => 'gray',
                        };
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof NotificationStatusEnum ? $state->value : $state;
                        return NotificationStatusEnum::getLabels()[$value] ?? $value;
                    })
                    ->badge()
                    ->color(function ($state): string {
                        $value = $state instanceof NotificationStatusEnum ? $state->value : $state;
                        return match ($value) {
                            NotificationStatusEnum::ACTIVE->value => 'success',
                            NotificationStatusEnum::INACTIVE->value => 'gray',
                            default => 'gray',
                        };
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('message_template')
                    ->label('Mesaj Şablonu')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->message_template)
                    ->wrap()
                    ->toggleable(),

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
                SelectFilter::make('event')
                    ->label('Event')
                    ->options(NotificationEventEnum::getLabels())
                    ->multiple(),

                SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        UserRoleEnum::SUPER_ADMIN->value => 'Süper Admin',
                        UserRoleEnum::CENTER_STAFF->value => 'Merkez Çalışanı',
                        UserRoleEnum::DEALER_OWNER->value => 'Bayi Sahibi',
                        UserRoleEnum::DEALER_STAFF->value => 'Bayi Çalışanı',
                    ])
                    ->multiple(),

                SelectFilter::make('priority')
                    ->label('Öncelik')
                    ->options(NotificationPriorityEnum::getLabels())
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(NotificationStatusEnum::getLabels())
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Aktif Yap')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => NotificationStatusEnum::ACTIVE->value]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Pasif Yap')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => NotificationStatusEnum::INACTIVE->value]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
