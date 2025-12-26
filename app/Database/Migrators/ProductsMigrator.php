<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class ProductsMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'products';
    }

    protected function getOldTableName(): string
    {
        return 'products';
    }

    protected function readOldData(): \Generator
    {
        $query = $this->oldDb()
            ->select('id', 'sku', 'name', 'description', 'price', 'image', 'warranty', 'active', 'category', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Category mapping
        $categoryMapping = $this->getPreviousMapping('product_categories');
        
        // Category name'den ID bul
        $categoryId = null;
        if (!empty($oldData['category'])) {
            // Önce name ile bul
            $category = DB::table('product_categories')
                ->where('name', $oldData['category'])
                ->first();
            
            if ($category) {
                $categoryId = $category->id;
            } else {
                // Mapping'de ara
                foreach ($categoryMapping as $oldCatId => $newCatId) {
                    $cat = $this->oldDb->table('categories')
                        ->where('id', $oldCatId)
                        ->first();
                    if ($cat && $cat->name === $oldData['category']) {
                        $categoryId = $newCatId;
                        break;
                    }
                }
            }
        }

        if ($categoryId === null) {
            $this->command->warn("Product ID {$oldData['id']}: Category '{$oldData['category']}' bulunamadı, atlanıyor.");
            return null;
        }

        // Warranty parsing (örn: "2 yıl" -> 24)
        $warrantyDuration = $this->parseWarranty($oldData['warranty']);

        // Image'ı storage'a kaydet
        $imagePath = null;
        if (!empty($oldData['image'])) {
            $imagePath = $this->saveImageToStorage($oldData['image'], 'products');
        }

        return [
            'old_id' => $oldData['old_id'],
            'category_id' => $categoryId,
            'name' => $oldData['name'],
            'sku' => $oldData['sku'],
            'description' => $oldData['description'] ?? null,
            'warranty_duration' => $warrantyDuration,
            'price' => number_format((float) $oldData['price'] / 100, 2, '.', ''), // int -> decimal (kuruş -> lira)
            'image_path' => $imagePath,
            'is_active' => $this->boolToTinyint((bool) $oldData['active']),
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']);

        // SKU unique kontrolü
        $existing = DB::table('products')
            ->where('sku', $newData['sku'])
            ->first();

        if ($existing) {
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            return $existing->id;
        }

        $id = DB::table('products')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        return $id;
    }

    /**
     * Warranty string'ini ay sayısına çevir
     */
    protected function parseWarranty(?string $warranty): ?int
    {
        if (empty($warranty)) {
            return null;
        }

        // "2 yıl" -> 24, "6 ay" -> 6 gibi
        if (preg_match('/(\d+)\s*(yıl|year)/i', $warranty, $matches)) {
            return (int) $matches[1] * 12;
        }

        if (preg_match('/(\d+)\s*(ay|month)/i', $warranty, $matches)) {
            return (int) $matches[1];
        }

        // Sadece sayı varsa ay olarak kabul et
        if (preg_match('/(\d+)/', $warranty, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}

