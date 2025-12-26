<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class CarModelsMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'car_models';
    }

    protected function getOldTableName(): string
    {
        return 'car_models';
    }

    protected function readOldData(): \Generator
    {
        // Brand bilgilerini önce yükle (external_id için)
        $brandCache = [];
        $oldBrands = $this->oldDb->table('car_brands')
            ->select('id', 'external_id')
            ->get();
        
        foreach ($oldBrands as $oldBrand) {
            $brandCache[$oldBrand->id] = [
                'external_id' => $oldBrand->external_id ?? null,
            ];
        }

        $query = $this->oldDb()
            ->select('id', 'brand_id', 'name', 'external_id', 'last_update', 'is_active', 'created_at', 'updated_at', 'deleted_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            // Brand external_id'yi cache'den ekle
            $rowArray['old_brand_external_id'] = $brandCache[$rowArray['brand_id']]['external_id'] ?? null;
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Brand ID mapping - önce mapping'den bak
        $brandMapping = $this->getPreviousMapping('car_brands');
        $newBrandId = $brandMapping[$oldData['brand_id']] ?? null;

        // Eğer mapping'de yoksa, external_id ile yeni veritabanında ara
        if ($newBrandId === null && !empty($oldData['old_brand_external_id'])) {
            $brand = DB::table('car_brands')
                ->where('external_id', $oldData['old_brand_external_id'])
                ->first();
            
            if ($brand) {
                $newBrandId = $brand->id;
            }
        }

        // Hala bulunamadıysa, eski veritabanından brand external_id'yi al ve tekrar ara
        if ($newBrandId === null) {
            $oldBrand = $this->oldDb->table('car_brands')
                ->where('id', $oldData['brand_id'])
                ->first();
            
            if ($oldBrand && !empty($oldBrand->external_id)) {
                $brand = DB::table('car_brands')
                    ->where('external_id', $oldBrand->external_id)
                    ->first();
                
                if ($brand) {
                    $newBrandId = $brand->id;
                }
            }
        }

        if ($newBrandId === null) {
            $this->command->warn("CarModel ID {$oldData['id']}: Brand ID {$oldData['brand_id']} (external_id: {$oldData['old_brand_external_id']}) bulunamadı, atlanıyor.");
            return null;
        }

        return [
            'old_id' => $oldData['old_id'] ?? $oldData['id'],
            'brand_id' => $newBrandId,
            'name' => $oldData['name'],
            'external_id' => $oldData['external_id'],
            'last_update' => $oldData['last_update'],
            'is_active' => $this->boolToTinyint((bool) $oldData['is_active']),
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
            'deleted_at' => $oldData['deleted_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']);

        // External ID unique kontrolü
        $existing = DB::table('car_models')
            ->where('external_id', $newData['external_id'])
            ->first();

        if ($existing) {
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            return $existing->id;
        }

        $id = DB::table('car_models')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        return $id;
    }
}

