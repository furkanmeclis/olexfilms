<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Inertia\Inertia;

class WarrantyController extends Controller
{
    public function index($serviceNo)
    {
        try {
            $service = Service::where('service_no', $serviceNo)
                ->with([
                    'carBrand',
                    'carModel',
                    'dealer',
                    'items.stockItem.product.category',
                    'items.stockItem.warranties',
                ])
                ->first();

            if (!$service) {
                return Inertia::render('Warranty/Error', [
                    'serviceNo' => $serviceNo,
                ]);
            }

            // Applied services verilerini hazırla (category bazında gruplandır)
            $appliedServices = [];
            $itemsByCategory = [];

            foreach ($service->items as $item) {
                // Null check'ler
                if (!$item->stockItem || !$item->stockItem->product || !$item->stockItem->product->category) {
                    continue;
                }

                $category = $item->stockItem->product->category;
                $categoryName = $category->name ?? 'Bilinmeyen Kategori';
                
                if (!isset($itemsByCategory[$categoryName])) {
                    $itemsByCategory[$categoryName] = [];
                }

                $warranty = $item->stockItem->warranties()->first();
                $warrantyText = $this->formatWarranty($warranty);

                $itemsByCategory[$categoryName][] = [
                    'name' => $item->stockItem->product->name ?? 'Bilinmeyen Ürün',
                    'warranty' => $warrantyText,
                ];
            }

            // Category bazında birleştir
            foreach ($itemsByCategory as $categoryName => $items) {
                $warranties = array_unique(array_column($items, 'warranty'));
                $names = array_column($items, 'name');
                
                $appliedServices[] = [
                    'category' => $categoryName,
                    'name' => implode(', ', $names),
                    'warranty' => implode(' • ', $warranties),
                    'multiple' => count($items) > 1,
                    'warrantyMultiple' => count($warranties) > 1,
                ];
            }

            // Brand logo path'ini oluştur
            $brandLogo = null;
            if ($service->carBrand && $service->carBrand->logo) {
                // Logo path'i zaten tam path olabilir veya relative olabilir
                $logoPath = $service->carBrand->logo;
                if (str_starts_with($logoPath, 'http')) {
                    $brandLogo = $logoPath;
                } elseif (str_starts_with($logoPath, '/')) {
                    $brandLogo = $logoPath;
                } else {
                    $brandLogo = asset('storage/' . $logoPath);
                }
            }

            return Inertia::render('Warranty/Index', [
                'serviceNo' => $serviceNo,
                'serviceData' => [
                    'service_no' => $service->service_no ?? '',
                    'brand' => $service->carBrand->name ?? '',
                    'brand_logo' => $brandLogo,
                    'model' => $service->carModel->name ?? '',
                    'generation' => '', // Generation bilgisi yok, boş bırakıyoruz
                    'year' => $service->year ?? '',
                    'plate' => $service->plate ?? '',
                    'applied_services' => $appliedServices,
                    'dealer' => [
                        'company_name' => $service->dealer->name ?? '',
                        'company_city' => $service->dealer->city ?? '',
                        'company_country' => $service->dealer->district ?? '',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // Hataları logla
            \Log::error('WarrantyController Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'service_no' => $serviceNo,
            ]);

            // Hata durumunda hata sayfasını göster
            return Inertia::render('Warranty/Error', [
                'serviceNo' => $serviceNo,
                'error' => 'Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.',
            ]);
        }
    }

    private function formatWarranty($warranty): string
    {
        if (!$warranty || !$warranty->end_date) {
            return 'Garanti yok';
        }

        $now = now();
        $endDate = $warranty->end_date;
        
        if ($now->greaterThan($endDate)) {
            return 'X Garanti süresi doldu';
        }

        $daysRemaining = $now->diffInDays($endDate);
        
        if ($daysRemaining >= 365) {
            $years = floor($daysRemaining / 365);
            return $years . ' yıl garanti';
        } elseif ($daysRemaining >= 30) {
            $months = floor($daysRemaining / 30);
            return $months . ' ay garanti';
        } else {
            return $daysRemaining . ' gün garanti';
        }
    }
}

