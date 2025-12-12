<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Order;
use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                        $user = Auth::user();
                        
                        DB::transaction(function () use ($record, $user) {
                            // Siparişe bağlı tüm stock_items'ı bul
                            $stockItems = StockItem::whereHas('orderItems', function ($query) use ($record) {
                                $query->whereHas('order', function ($q) use ($record) {
                                    $q->where('id', $record->id);
                                });
                            })->get();

                            foreach ($stockItems as $stockItem) {
                                // StockItem'ı güncelle
                                $stockItem->update([
                                    'dealer_id' => $record->dealer_id,
                                    'location' => StockLocationEnum::DEALER->value,
                                    'status' => StockStatusEnum::AVAILABLE->value,
                                ]);

                                // Hareket logu oluştur
                                StockMovement::create([
                                    'stock_item_id' => $stockItem->id,
                                    'user_id' => $user->id,
                                    'action' => StockMovementActionEnum::RECEIVED->value,
                                    'description' => "Sipariş #{$record->id} {$record->dealer->name} bayisine teslim edildi",
                                    'created_at' => now(),
                                ]);
                            }

                            // Sipariş statüsünü delivered yap
                            $record->update(['status' => OrderStatusEnum::DELIVERED->value]);
                        });

                        Notification::make()
                            ->title('Başarılı')
                            ->body("Sipariş {$record->dealer->name} bayisine teslim edildi ve stoklar envanterine eklendi.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
