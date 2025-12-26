<?php

namespace App\Filament\Resources\Services\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));
        $isDealer = $user && ($user->hasRole(UserRoleEnum::DEALER_OWNER->value) || $user->hasRole(UserRoleEnum::DEALER_STAFF->value));

        return $table
            ->columns([
                TextColumn::make('service_no')
                    ->label('Hizmet No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('carBrand.name')
                    ->label('Marka')
                    ->sortable(),

                TextColumn::make('carModel.name')
                    ->label('Model')
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Yıl')
                    ->sortable(),

                TextColumn::make('plate')
                    ->label('Plaka')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => ServiceStatusEnum::getLabels()[$state->value] ?? $state->value)
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'ready' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Tamamlanma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(ServiceStatusEnum::getLabels()),

                SelectFilter::make('dealer_id')
                    ->label('Bayi')
                    ->relationship('dealer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Dışa Aktar'),
            ])
            ->recordActions([
                ViewAction::make(),

                // Hızlı Durum Güncelleme (Bayi için)
                Action::make('quickUpdateStatus')
                    ->label('Durum Güncelle')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn ($record) => $isDealer && in_array($record->status, [
                        ServiceStatusEnum::DRAFT,
                        ServiceStatusEnum::PENDING,
                        ServiceStatusEnum::PROCESSING,
                        ServiceStatusEnum::READY,
                    ]))
                    ->form([
                        Select::make('status')
                            ->label('Yeni Durum')
                            ->options(function ($record) use ($isAdmin) {
                                $allStatuses = ServiceStatusEnum::getLabels();

                                if ($isAdmin) {
                                    return $allStatuses;
                                }

                                // Bayi için sadece geçerli geçişler
                                $currentStatus = $record->status;
                                $allowedTransitions = match ($currentStatus) {
                                    ServiceStatusEnum::DRAFT => [ServiceStatusEnum::PENDING->value => $allStatuses[ServiceStatusEnum::PENDING->value]],
                                    ServiceStatusEnum::PENDING => [ServiceStatusEnum::PROCESSING->value => $allStatuses[ServiceStatusEnum::PROCESSING->value]],
                                    ServiceStatusEnum::PROCESSING => [ServiceStatusEnum::READY->value => $allStatuses[ServiceStatusEnum::READY->value]],
                                    ServiceStatusEnum::READY => [ServiceStatusEnum::COMPLETED->value => $allStatuses[ServiceStatusEnum::COMPLETED->value]],
                                    default => [],
                                };

                                return $allowedTransitions;
                            })
                            ->required()
                            ->default(fn ($record) => $record->status->value),
                    ])
                    ->action(function (array $data, $record) {
                        $newStatus = ServiceStatusEnum::from($data['status']);
                        $record->status = $newStatus;

                        if ($newStatus === ServiceStatusEnum::COMPLETED && ! $record->completed_at) {
                            $record->completed_at = now();
                        }

                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),

                EditAction::make()
                    ->visible(fn () => $isAdmin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
