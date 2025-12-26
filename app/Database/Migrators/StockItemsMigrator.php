<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class StockItemsMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'stock_items';
    }

    protected function getOldTableName(): string
    {
        return 'product_codes';
    }

    protected function readOldData(): \Generator
    {
        // Product bilgilerini önce yükle (sku için)
        $productCache = [];
        $oldProducts = $this->oldDb->table('products')
            ->select('id', 'sku')
            ->get();
        
        foreach ($oldProducts as $oldProduct) {
            $productCache[$oldProduct->id] = [
                'sku' => $oldProduct->sku ?? null,
            ];
        }

        // Dealer bilgilerini önce yükle (email için)
        $dealerCache = [];
        $oldDealerDetails = $this->oldDb->table('dealer_details')
            ->select('id', 'user_id', 'company_email')
            ->get();
        
        foreach ($oldDealerDetails as $dealerDetail) {
            $dealerCache[$dealerDetail->id] = [
                'email' => $dealerDetail->company_email ?? null,
            ];
        }

        $query = $this->oldDb()
            ->select('id', 'code', 'product_id', 'worker_id', 'order_id', 'dealer_id', 'location', 'used', 'used_at', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            // Product sku'yu cache'den ekle
            $rowArray['old_product_sku'] = $productCache[$rowArray['product_id']]['sku'] ?? null;
            // Dealer email'i cache'den ekle
            $oldDealerUserId = $this->safeIntCast($rowArray['dealer_id']);
            if ($oldDealerUserId) {
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldDealerUserId)
                    ->first();
                if ($dealerDetail) {
                    $rowArray['old_dealer_detail_id'] = $dealerDetail->id;
                    $rowArray['old_dealer_email'] = $dealerCache[$dealerDetail->id]['email'] ?? null;
                }
            }
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Product ID mapping - önce mapping'den bak
        $productMapping = $this->getPreviousMapping('products');
        $newProductId = $productMapping[$oldData['product_id']] ?? null;

        // Eğer mapping'de yoksa, sku ile yeni veritabanında ara
        if ($newProductId === null && !empty($oldData['old_product_sku'])) {
            $product = DB::table('products')
                ->where('sku', $oldData['old_product_sku'])
                ->first();
            
            if ($product) {
                $newProductId = $product->id;
            }
        }

        // Hala bulunamadıysa, eski veritabanından product sku'yu al ve tekrar ara
        if ($newProductId === null) {
            $oldProduct = $this->oldDb->table('products')
                ->where('id', $oldData['product_id'])
                ->first();
            
            if ($oldProduct && !empty($oldProduct->sku)) {
                $product = DB::table('products')
                    ->where('sku', $oldProduct->sku)
                    ->first();
                
                if ($product) {
                    $newProductId = $product->id;
                }
            }
        }

        if ($newProductId === null) {
            $this->command->warn("StockItem ID {$oldData['id']}: Product ID {$oldData['product_id']} (sku: {$oldData['old_product_sku']}) bulunamadı, atlanıyor.");
            return null;
        }

        // Dealer ID mapping (users -> dealers) - önce mapping'den bak
        $dealerId = null;
        if (!empty($oldData['dealer_id'])) {
            $oldDealerUserId = $this->safeIntCast($oldData['dealer_id']);
            if ($oldDealerUserId) {
                // dealer_details'ta user_id ile dealer bul
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldDealerUserId)
                    ->first();
                
                if ($dealerDetail) {
                    $dealerMapping = $this->getPreviousMapping('dealers');
                    $dealerId = $dealerMapping[$dealerDetail->id] ?? null;

                    // Eğer mapping'de yoksa, email ile yeni veritabanında ara
                    if ($dealerId === null && !empty($oldData['old_dealer_email'])) {
                        $dealer = DB::table('dealers')
                            ->where('email', $oldData['old_dealer_email'])
                            ->first();
                        
                        if ($dealer) {
                            $dealerId = $dealer->id;
                        }
                    }
                }
            }
        }

        // Location mapping: 'dealer' -> 'dealer', 'central' -> 'center'
        $location = $oldData['location'] === 'dealer' ? 'dealer' : 'center';

        // Status mapping: used=0 -> 'available', used=1 -> 'used'
        $status = $oldData['used'] ? 'used' : 'available';

        // SKU oluştur (product'tan al)
        $product = DB::table('products')
            ->where('id', $newProductId)
            ->first();
        
        $sku = $product->sku ?? 'UNKNOWN';

        return [
            'old_id' => $oldData['old_id'],
            'product_id' => $newProductId,
            'dealer_id' => $dealerId,
            'sku' => $sku,
            'barcode' => $oldData['code'],
            'location' => $location,
            'status' => $status,
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']);

        // Barcode unique kontrolü
        $existing = DB::table('stock_items')
            ->where('barcode', $newData['barcode'])
            ->first();

        if ($existing) {
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            return $existing->id;
        }

        $id = DB::table('stock_items')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        return $id;
    }
}

