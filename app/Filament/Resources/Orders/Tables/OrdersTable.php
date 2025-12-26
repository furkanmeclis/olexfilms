<?php

namespace App\Filament\Resources\Orders\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\OrderStatusEnum;
use App\Enums\UserRoleEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Sipariş No')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('dealer.name')
                    ->label('Bayi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn ($state) => OrderStatusEnum::getLabels()[$state->value] ?? $state->value)
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('items_sum_quantity')
                    ->label('Toplam Adet')
                    ->sum('items', 'quantity')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Oluşturan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(OrderStatusEnum::getLabels()),

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
                ViewAction::make()
                    ->label('Görüntüle'),
                Action::make('deliverOrder')
                    ->label('Teslim Et')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sipariş Teslim Ediliyor')
                    ->modalDescription(function ($record) {
                        return "Siparişi {$record->dealer->name} bayisine teslim etmek istediğinizden emin misiniz?";
                    })
                    ->visible(function ($record) {
                        $user = Auth::user();

                        return $user && $user->hasAnyRole([
                            UserRoleEnum::SUPER_ADMIN->value,
                            UserRoleEnum::CENTER_STAFF->value,
                        ]) && $record->status->value === 'shipped';
                    })
                    ->action(function ($record) {
                        // Sipariş statüsünü delivered yap
                        // Observer stok durumunu ve movement loglarını güncelleyecek
                        $record->update(['status' => OrderStatusEnum::DELIVERED->value]);

                        Notification::make()
                            ->title('Başarılı')
                            ->body("Sipariş {$record->dealer->name} bayisine teslim edildi ve stoklar envanterine eklendi.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
                ]),
            ]);
    }
}
