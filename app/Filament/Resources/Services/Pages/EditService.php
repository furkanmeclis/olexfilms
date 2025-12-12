<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeValidate(array $data): array
    {
        // Backend validation: Hizmet numarası unique olmalı
        if (isset($data['service_no']) && !empty($data['service_no'])) {
            $serviceExists = Service::where('service_no', $data['service_no'])
                ->where('id', '!=', $this->record->id)
                ->exists();

            if ($serviceExists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'service_no' => 'Bu hizmet numarası zaten başka bir serviste kullanılıyor.',
                ]);
            }
        }

        return $data;
    }

    public function validateServiceNumber(string $serviceNo): array
    {
        if (empty($serviceNo)) {
            return [
                'valid' => false,
                'message' => 'Hizmet numarası boş olamaz.',
            ];
        }

        // Edit sayfasında mevcut kaydı ignore et - UNIQUE kontrolü
        $serviceExists = Service::where('service_no', $serviceNo)
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($serviceExists) {
            return [
                'valid' => false,
                'message' => 'Bu hizmet numarası zaten başka bir serviste kullanılıyor.',
            ];
        }

        // Hizmet numarasının bir stok item'ın barcode'u olup olmadığını kontrol et (opsiyonel)
        $stockItem = StockItem::where('barcode', $serviceNo)->first();

        // Eğer stok item varsa, kontrol et
        if ($stockItem) {
            // Eğer stok item zaten bu serviste kullanılıyorsa, geçerli kabul et
            $existingServiceItem = ServiceItem::where('service_id', $this->record->id)
                ->where('stock_item_id', $stockItem->id)
                ->first();

            if ($existingServiceItem) {
                return [
                    'valid' => true,
                    'message' => 'Hizmet numarası doğrulandı. Stok kalemi: ' . $stockItem->product->name,
                    'stock_item_id' => $stockItem->id,
                ];
            }

            // Stok item'ın başka bir serviste kullanılıp kullanılmadığını kontrol et
            if ($stockItem->status->value === StockStatusEnum::USED->value) {
                $otherServiceItem = ServiceItem::where('stock_item_id', $stockItem->id)
                    ->where('service_id', '!=', $this->record->id)
                    ->first();

                if ($otherServiceItem) {
                    return [
                        'valid' => false,
                        'message' => 'Bu stok kalemi zaten başka bir serviste kullanılıyor.',
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'Hizmet numarası doğrulandı. Stok kalemi: ' . $stockItem->product->name,
                'stock_item_id' => $stockItem->id,
            ];
        }

        // Stok envanterinde yoksa, sadece unique kontrolü yapıldı, geçerli
        return [
            'valid' => true,
            'message' => 'Hizmet numarası doğrulandı.',
        ];
    }

    protected function afterSave(): void
    {
        $service = $this->record;
        $user = Auth::user();
        $formData = $this->form->getState();

        // Hizmet numarası bir stok item'ın barcode'u ise, o stok item'ı servise bağla
        if (isset($formData['service_no']) && !empty($formData['service_no'])) {
            $stockItem = StockItem::where('barcode', $formData['service_no'])->first();

            if ($stockItem) {
                $serviceNo = $formData['service_no'];
                DB::transaction(function () use ($service, $stockItem, $user, $serviceNo) {
                    // Mevcut ServiceItem'ı kontrol et
                    $existingServiceItem = ServiceItem::where('service_id', $service->id)
                        ->where('stock_item_id', $stockItem->id)
                        ->first();

                    if (!$existingServiceItem) {
                        // Eski hizmet numarasına bağlı stok item'ı bul ve kaldır
                        $oldStockItem = StockItem::whereHas('serviceItems', function ($query) use ($service) {
                            $query->where('service_id', $service->id);
                        })->where('barcode', '!=', $serviceNo)->first();

                        if ($oldStockItem) {
                            // Eski ServiceItem'ı kaldır
                            ServiceItem::where('service_id', $service->id)
                                ->where('stock_item_id', $oldStockItem->id)
                                ->delete();

                            // Eski stok item'ı tekrar AVAILABLE yap (eğer DEALER lokasyonundaysa)
                            if ($oldStockItem->location->value === StockLocationEnum::DEALER->value) {
                                $oldStockItem->update([
                                    'location' => StockLocationEnum::DEALER->value,
                                    'status' => StockStatusEnum::AVAILABLE->value,
                                ]);
                            }
                        }

                        // Yeni ServiceItem oluştur
                        ServiceItem::create([
                            'service_id' => $service->id,
                            'stock_item_id' => $stockItem->id,
                            'usage_type' => \App\Enums\ServiceItemUsageTypeEnum::USED->value,
                        ]);

                        // Stok item'ın location'ını SERVICE yap ve status'unu USED yap
                        $stockItem->update([
                            'location' => StockLocationEnum::SERVICE->value,
                            'status' => StockStatusEnum::USED->value,
                        ]);

                        // StockMovement kaydı oluştur
                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'user_id' => $user->id,
                            'action' => StockMovementActionEnum::USED_IN_SERVICE->value,
                            'description' => "Hizmet #{$service->id} için kullanıldı (Hizmet No: {$serviceNo})",
                            'created_at' => now(),
                        ]);
                    }
                });
            }
        }
    }
}
