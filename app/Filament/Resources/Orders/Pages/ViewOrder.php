<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\StockItem;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $order = $this->record;
        $actions = [];

        // Hazırlama Yap (sadece Admin/Center Staff, sadece pending/processing için)
        if ($user && $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]) && in_array($order->status->value, ['pending', 'processing'])) {
            $actions[] = Action::make('prepareOrder')
                ->label('Hazırlama Yap')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->modalHeading('Sipariş Hazırlama')
                ->schema(function () use ($order) {
                    $schema = [];
                    foreach ($order->items as $item) {
                        $availableStock = StockItem::where('product_id', $item->product_id)
                            ->where('location', StockLocationEnum::CENTER->value)
                            ->where('status', StockStatusEnum::AVAILABLE->value)
                            ->get();

                        if ($availableStock->count() > 0) {
                            $schema[] = CheckboxList::make("order_item_{$item->id}")
                                ->label("{$item->product->name} (İstenen: {$item->quantity} adet)")
                                ->options($availableStock->pluck('barcode', 'id')->toArray())
                                ->required()
                                ->descriptions(
                                    $availableStock->mapWithKeys(fn ($stock) => [
                                        $stock->id => "SKU: {$stock->sku}",
                                    ])->toArray()
                                )
                                ->helperText("Mevcut stok: {$availableStock->count()} adet");
                        } else {
                            $schema[] = \Filament\Forms\Components\Placeholder::make("no_stock_{$item->id}")
                                ->label("{$item->product->name}")
                                ->content("⚠️ Bu ürün için merkezde müsait stok yok!");
                        }
                    }
                    return $schema;
                })
                ->action(function (array $data) use ($order, $user) {
                    DB::transaction(function () use ($data, $order) {
                        foreach ($order->items as $item) {
                            $key = "order_item_{$item->id}";
                            if (!isset($data[$key]) || empty($data[$key])) {
                                continue;
                            }

                            $selectedStockIds = $data[$key];

                            // Seçilen stokları order_item_stock'a ekle
                            // Observer stok durumunu güncelleyecek
                            $item->stockItems()->attach($selectedStockIds);
                        }

                        // Sipariş statüsünü processing yap
                        // Observer stok durumunu ve movement loglarını güncelleyecek
                        $order->update(['status' => OrderStatusEnum::PROCESSING->value]);
                    });

                    \Filament\Notifications\Notification::make()
                        ->title('Başarılı')
                        ->body('Sipariş hazırlandı ve stoklar rezerve edildi.')
                        ->success()
                        ->send();
                });
        }

        // Kargoya Ver (sadece Admin/Center Staff, processing için)
        if ($user && $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]) && $order->status->value === 'processing') {
            $actions[] = Action::make('shipOrder')
                ->label('Kargoya Ver')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->modalHeading('Kargo Bilgileri')
                ->schema([
                    TextInput::make('cargo_company')
                        ->label('Kargo Firması')
                        ->maxLength(255)
                        ->required(),

                    TextInput::make('tracking_number')
                        ->label('Takip Numarası')
                        ->maxLength(255)
                        ->required(),
                ])
                ->action(function (array $data) use ($order) {
                    $order->update([
                        'status' => OrderStatusEnum::SHIPPED->value,
                        'cargo_company' => $data['cargo_company'],
                        'tracking_number' => $data['tracking_number'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Başarılı')
                        ->body('Sipariş kargoya verildi.')
                        ->success()
                        ->send();
                });
        }

        // Teslim Et (Admin/Center Staff, shipped için)
        if ($user && $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]) && $order->status->value === 'shipped') {
            $actions[] = Action::make('deliverOrder')
                ->label('Teslim Et')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sipariş Teslim Ediliyor')
                ->modalDescription("Siparişi {$order->dealer->name} bayisine teslim etmek istediğinizden emin misiniz?")
                ->action(function () use ($order) {
                    // Sipariş statüsünü delivered yap
                    // Observer stok durumunu ve movement loglarını güncelleyecek
                    $order->update(['status' => OrderStatusEnum::DELIVERED->value]);

                    \Filament\Notifications\Notification::make()
                        ->title('Başarılı')
                        ->body("Sipariş {$order->dealer->name} bayisine teslim edildi ve stoklar envanterine eklendi.")
                        ->success()
                        ->send();
                });
        }

        // Teslim Al (sadece Bayi, shipped için)
        if ($user && $user->dealer_id && !$user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]) && $order->status->value === 'shipped' && $order->dealer_id === $user->dealer_id) {
            $actions[] = Action::make('receiveOrder')
                ->label('Teslim Al')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sipariş Teslim Alınıyor')
                ->modalDescription('Siparişi teslim almak istediğinizden emin misiniz?')
                ->action(function () use ($order) {
                    // Sipariş statüsünü delivered yap
                    // Observer stok durumunu ve movement loglarını güncelleyecek
                    $order->update(['status' => OrderStatusEnum::DELIVERED->value]);

                    \Filament\Notifications\Notification::make()
                        ->title('Başarılı')
                        ->body('Sipariş teslim alındı ve stoklar envanterinize eklendi.')
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }
}
