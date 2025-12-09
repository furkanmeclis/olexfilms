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
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateService extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ServiceResource::class;

    protected function getSteps(): array
    {
        // Create sayfasında kayıt yok, bu yüzden null geç
        return [
            Step::make('Müşteri ve Araç Bilgileri')
                ->description('Müşteri seçimi ve araç bilgilerini girin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getCustomerAndCarStep(null)),

            Step::make('Kaplama Alanları')
                ->description('Uygulanacak parçaları seçin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getAppliedPartsStep(null)),

            Step::make('Stok/Malzeme Seçimi')
                ->description('Hizmet için kullanılacak stok ürünlerini seçin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getStockStep(null)),

            Step::make('Durum ve Notlar')
                ->description('Hizmet numarası, durum ve notları belirleyin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getStatusAndNotesStep(null)),
        ];
    }

    public function validateServiceNumber(string $serviceNo): array
    {
        if (empty($serviceNo)) {
            return [
                'valid' => false,
                'message' => 'Hizmet numarası boş olamaz.',
            ];
        }

        // Hizmet numarasının başka bir serviste kullanılıp kullanılmadığını kontrol et (UNIQUE kontrolü)
        $serviceExists = Service::where('service_no', $serviceNo)->exists();

        if ($serviceExists) {
            return [
                'valid' => false,
                'message' => 'Bu hizmet numarası zaten kullanılıyor.',
            ];
        }

        // Hizmet numarasının bir stok item'ın barcode'u olup olmadığını kontrol et (opsiyonel)
        $stockItem = StockItem::where('barcode', $serviceNo)->first();

        // Eğer stok item varsa, kullanılabilir durumda olup olmadığını kontrol et
        if ($stockItem) {
            // Stok item'ın başka bir serviste kullanılıp kullanılmadığını kontrol et
            if ($stockItem->status->value === StockStatusEnum::USED->value) {
                $otherServiceItem = ServiceItem::where('stock_item_id', $stockItem->id)->first();
                
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

    protected function afterCreate(): void
    {
        $service = $this->record;
        $user = Auth::user();
        $formData = $this->form->getState();

        // Hizmet numarası bir stok item'ın barcode'u ise, o stok item'ı servise bağla
        if (isset($formData['service_no']) && !empty($formData['service_no'])) {
            $stockItem = StockItem::where('barcode', $formData['service_no'])->first();

            if ($stockItem) {
                DB::transaction(function () use ($service, $stockItem, $user) {
                    // ServiceItem oluştur
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
                        'description' => "Hizmet #{$service->id} için kullanıldı (Hizmet No: {$service->service_no})",
                        'created_at' => now(),
                    ]);
                });
            }
        }
    }
}
