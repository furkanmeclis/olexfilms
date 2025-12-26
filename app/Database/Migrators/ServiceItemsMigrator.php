<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceItemsMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'service_items';
    }

    protected function getOldTableName(): string
    {
        return 'service_products';
    }

    protected function readOldData(): \Generator
    {
        // Service bilgilerini önce yükle (service_no için)
        $serviceNoCache = [];
        $oldServices = $this->oldDb->table('services')
            ->select('id', 'service_no')
            ->get();
        
        foreach ($oldServices as $oldService) {
            $serviceNoCache[$oldService->id] = $oldService->service_no;
        }

        $query = $this->oldDb()
            ->select('id', 'service_id', 'product_id', 'product_code', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            // Service no'yu cache'den ekle
            $rowArray['old_service_no'] = $serviceNoCache[$rowArray['service_id']] ?? null;
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Service ID mapping - önce mapping'den bak
        $serviceMapping = $this->getPreviousMapping('services');
        $newServiceId = $serviceMapping[$oldData['service_id']] ?? null;

        // Eğer mapping'de yoksa, service_no ile yeni veritabanında ara
        if ($newServiceId === null && !empty($oldData['old_service_no'])) {
            $service = DB::table('services')
                ->where('service_no', $oldData['old_service_no'])
                ->first();
            
            if ($service) {
                $newServiceId = $service->id;
                // Mapping'e ekle (sonraki kayıtlar için)
                $serviceMapping[$oldData['service_id']] = $newServiceId;
            }
        }

        // Hala bulunamadıysa, eski veritabanından service_no'yu al ve tekrar ara
        if ($newServiceId === null) {
            $oldService = $this->oldDb->table('services')
                ->where('id', $oldData['service_id'])
                ->first();
            
            if ($oldService && !empty($oldService->service_no)) {
                $service = DB::table('services')
                    ->where('service_no', $oldService->service_no)
                    ->first();
                
                if ($service) {
                    $newServiceId = $service->id;
                }
            }
        }

        if ($newServiceId === null) {
            $errorMsg = "Service ID {$oldData['service_id']} (service_no: {$oldData['old_service_no']}) bulunamadı - Service migrate edilmemiş olabilir";
            $this->command->warn("ServiceItem ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // Stock item mapping (product_code -> barcode -> stock_item)
        $stockItemId = null;
        if (!empty($oldData['product_code'])) {
            // Önce barcode ile ara
            $stockItem = DB::table('stock_items')
                ->where('barcode', $oldData['product_code'])
                ->first();
            
            if ($stockItem) {
                $stockItemId = $stockItem->id;
            } else {
                // Eğer barcode ile bulunamazsa, eski product_id ile product'ı bul, sonra stock_item'ı ara
                if (!empty($oldData['product_id'])) {
                    $oldProduct = $this->oldDb->table('products')
                        ->where('id', $oldData['product_id'])
                        ->first();
                    
                    if ($oldProduct && !empty($oldProduct->sku)) {
                        // Yeni veritabanında product'ı bul
                        $newProduct = DB::table('products')
                            ->where('sku', $oldProduct->sku)
                            ->first();
                        
                        if ($newProduct) {
                            // Bu product'a ait herhangi bir stock_item bul (barcode olmadan)
                            $stockItem = DB::table('stock_items')
                                ->where('product_id', $newProduct->id)
                                ->where('status', 'available')
                                ->first();
                            
                            if ($stockItem) {
                                $stockItemId = $stockItem->id;
                            }
                        }
                    }
                }
            }
        }

        // Stock item bulunamadıysa, kayıt oluşturulamaz (zorunlu alan)
        if ($stockItemId === null) {
            $errorMsg = "Stock item bulunamadı (barcode: {$oldData['product_code']}, product_id: {$oldData['product_id']})";
            $this->command->warn("ServiceItem ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // Usage type default: 'full' (eski sistemde kullanılan parçalar genelde tamamı kullanılmış)
        $usageType = 'full';

        return [
            'old_id' => $oldData['old_id'],
            'service_id' => $newServiceId,
            'stock_item_id' => $stockItemId,
            'usage_type' => $usageType,
            'notes' => null,
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        $serviceId = $newData['service_id'] ?? null;
        $stockItemId = $newData['stock_item_id'] ?? null;
        unset($newData['old_id']);

        $id = DB::table('service_items')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        // Warranty kaydı oluştur
        if ($id && $serviceId && $stockItemId) {
            $this->createWarranty($serviceId, $stockItemId);
        }

        return $id;
    }

    /**
     * Service item için warranty kaydı oluştur
     */
    protected function createWarranty(int $serviceId, int $stockItemId): void
    {
        try {
            // Stock item'dan product'ı bul
            $stockItem = DB::table('stock_items')
                ->where('id', $stockItemId)
                ->first();

            if (! $stockItem || ! $stockItem->product_id) {
                $this->command->warn("Warranty oluşturulamadı: Stock item ID {$stockItemId} için product bulunamadı");
                return;
            }

            // Product'ın warranty_duration'ını al
            $product = DB::table('products')
                ->where('id', $stockItem->product_id)
                ->first();

            if (! $product) {
                $this->command->warn("Warranty oluşturulamadı: Product ID {$stockItem->product_id} bulunamadı");
                return;
            }

            // Warranty duration yoksa veya 0 ise, warranty kaydı oluşturma
            $warrantyDuration = $product->warranty_duration ?? 0;
            if ($warrantyDuration <= 0) {
                // Warranty duration yok, warranty kaydı oluşturulmayacak
                return;
            }

            // Service bilgilerini al (completed_at varsa onu kullan, yoksa created_at)
            $service = DB::table('services')
                ->where('id', $serviceId)
                ->first();

            if (! $service) {
                $this->command->warn("Warranty oluşturulamadı: Service ID {$serviceId} bulunamadı");
                return;
            }

            // Start date: Service'in completed_at'i varsa onu kullan, yoksa created_at
            $startDate = $service->completed_at ?? $service->created_at;
            if (! $startDate) {
                $startDate = now();
            }

            $startDateCarbon = Carbon::parse($startDate)->startOfDay();
            
            // End date: start_date + warranty_duration (ay cinsinden)
            $endDateCarbon = $startDateCarbon->copy()->addMonths($warrantyDuration);

            // Warranty kaydı oluştur
            DB::table('warranties')->insert([
                'service_id' => $serviceId,
                'stock_item_id' => $stockItemId,
                'start_date' => $startDateCarbon->format('Y-m-d'),
                'end_date' => $endDateCarbon->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->command->warn("Warranty oluşturulurken hata: Service ID {$serviceId}, Stock Item ID {$stockItemId} - {$e->getMessage()}");
        }
    }
}

