<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                    'beforeReports.measurements',
                    'afterReports.measurements',
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
                $logoPath = $service->carBrand->logo;
                
                // Eğer tam URL ise direkt kullan
                if (str_starts_with($logoPath, 'http')) {
                    $brandLogo = $logoPath;
                } 
                // Eğer / ile başlıyorsa direkt kullan
                elseif (str_starts_with($logoPath, '/')) {
                    $brandLogo = $logoPath;
                } 
                // Storage path ise getFileUrl() kullan
                else {
                    $brandLogo = $this->getFileUrl($logoPath);
                }
            }

            // Ölçüm verilerini hazırla
            $measurements = $this->prepareMeasurements($service);

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
                    'measurements' => $measurements,
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

    /**
     * Get file URL with temporaryUrl support for cloud disks
     * 
     * @param string $path File path in storage
     * @return string|null File URL or null if file doesn't exist
     */
    private function getFileUrl(string $path): ?string
    {
        $defaultDisk = config('filesystems.default');
        $disk = Storage::disk($defaultDisk);
        
        // Check if file exists
        if (!$disk->exists($path)) {
            return null;
        }
        
        // Get disk driver
        $driver = config("filesystems.disks.{$defaultDisk}.driver");
        
        // Cloud disk'lerde (s3, etc.) temporaryUrl kullan
        if (in_array($driver, ['s3'])) {
            try {
                return $disk->temporaryUrl($path, now()->addHour());
            } catch (\Exception $e) {
                // Fallback to regular url if temporaryUrl fails
                return $disk->url($path);
            }
        }
        
        // Local disk'lerde normal url
        return $disk->url($path);
    }

    public function downloadPdf(Request $request, $serviceNo, PdfService $pdfService)
    {
        try {
            $service = Service::where('service_no', $serviceNo)
                ->with([
                    'carBrand',
                    'carModel',
                    'items.stockItem.product.category',
                    'items.stockItem.warranties',
                    'beforeReports.measurements',
                    'afterReports.measurements',
                ])
                ->first();

            if (!$service) {
                abort(404, 'Service not found');
            }

            // Applied services verilerini hazırla (PDF için code field'ı ile)
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
                    'code' => $item->stockItem->barcode ?? '',
                    'name' => $item->stockItem->product->name ?? 'Bilinmeyen Ürün',
                    'warranty' => $warrantyText,
                ];
            }

            // Category bazında birleştir - PDF için her item ayrı satır olacak
            foreach ($itemsByCategory as $categoryName => $items) {
                foreach ($items as $item) {
                    $appliedServices[] = [
                        'category' => $categoryName,
                        'code' => $item['code'],
                        'name' => $item['name'],
                        'warranty' => $item['warranty'],
                    ];
                }
            }

            // Brand logo path'ini oluştur
            $brandLogo = null;
            if ($service->carBrand && $service->carBrand->logo) {
                $logoPath = $service->carBrand->logo;
                
                // Eğer tam URL ise direkt kullan
                if (str_starts_with($logoPath, 'http')) {
                    $brandLogo = $logoPath;
                } 
                // Eğer / ile başlıyorsa direkt kullan
                elseif (str_starts_with($logoPath, '/')) {
                    $brandLogo = $logoPath;
                } 
                // Storage path ise getFileUrl() kullan
                else {
                    $brandLogo = $this->getFileUrl($logoPath);
                }
            }

            // Ölçüm verilerini hazırla
            $measurements = $this->prepareMeasurements($service);

            // Service details objesi oluştur
            $serviceDetails = (object) [
                'brand_logo' => $brandLogo ?? '',
                'brand' => $service->carBrand->name ?? '',
                'model' => $service->carModel->name ?? '',
                'generation' => '', // Generation bilgisi yok
                'year' => $service->year ?? '',
                'body_data' => $service->applied_parts ?? [],
                'applied_services' => $appliedServices,
                'measurements' => $measurements,
            ];

            // URL'den generate parametresini kontrol et
            $forceGenerate = $request->boolean('generate', false);

            // PDF'i cache'den al veya yeni oluştur
            $disk = Storage::disk(config('filesystems.default'));
            $pdfPath = $pdfService->getOrGeneratePdf($serviceNo, $serviceDetails, $forceGenerate);

            // PDF dosyasını oku ve stream et
            if (!$disk->exists($pdfPath)) {
                abort(500, 'PDF dosyası bulunamadı');
            }

            $pdfContent = $disk->get($pdfPath);
            $fileName = 'service-' . $serviceNo . '.pdf';

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            // Hataları logla
            \Log::error('WarrantyController PDF Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'service_no' => $serviceNo,
            ]);

            abort(500, 'PDF oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Ölçüm verilerini hazırla (place_id bazında gruplanmış)
     */
    private function prepareMeasurements(Service $service): array
    {
        $beforeMeasurementsByPlace = [];
        $afterMeasurementsByPlace = [];

        // BEFORE ve AFTER report'ları al (maksimum 1'er tane)
        $beforeReport = $service->beforeReports()->with('measurements')->first();
        $afterReport = $service->afterReports()->with('measurements')->first();

        // Eğer hiç ölçüm yoksa boş array döndür
        if (!$beforeReport && !$afterReport) {
            return [
                'before' => ['measurements' => [], 'unit_of_measure' => null],
                'after' => ['measurements' => [], 'unit_of_measure' => null],
            ];
        }

        // Place ID enum label'larını al
        $placeLabels = \App\Enums\NexptgPlaceIdEnum::getLabels();
        $partLabels = \App\Enums\NexptgPartTypeEnum::getLabels();

        // Place ID bazında gruplama
        $placeGroups = [];

        // BEFORE report measurements'ları işle
        if ($beforeReport && $beforeReport->measurements) {
            foreach ($beforeReport->measurements as $measurement) {
                $placeId = $measurement->place_id ?? null;
                $partType = $measurement->part_type->value ?? null;
                
                if (!$placeId || !$partType) {
                    continue;
                }

                if (!isset($placeGroups[$placeId])) {
                    $placeGroups[$placeId] = [];
                }

                if (!isset($placeGroups[$placeId][$partType])) {
                    $placeGroups[$placeId][$partType] = [
                        'before_positions' => [1 => null, 2 => null, 3 => null, 4 => null, 5 => null],
                        'after_positions' => [1 => null, 2 => null, 3 => null, 4 => null, 5 => null],
                        'before_values' => [],
                        'after_values' => [],
                        'substrate_types' => [],
                    ];
                }

                // Position bazında değer atama (1-5 arası)
                $position = $measurement->position;
                if ($position >= 1 && $position <= 5) {
                    $placeGroups[$placeId][$partType]['before_positions'][$position] = $measurement->value;
                }

                // Before value'ları topla (min/max/avg için)
                if ($measurement->value !== null) {
                    $placeGroups[$placeId][$partType]['before_values'][] = (float) $measurement->value;
                }

                // Substrate type'ı topla
                if ($measurement->substrate_type) {
                    $placeGroups[$placeId][$partType]['substrate_types'][$measurement->substrate_type] = true;
                }
            }
        }

        // AFTER report measurements'ları işle
        if ($afterReport && $afterReport->measurements) {
            foreach ($afterReport->measurements as $measurement) {
                $placeId = $measurement->place_id ?? null;
                $partType = $measurement->part_type->value ?? null;
                
                if (!$placeId || !$partType) {
                    continue;
                }

                if (!isset($placeGroups[$placeId])) {
                    $placeGroups[$placeId] = [];
                }

                if (!isset($placeGroups[$placeId][$partType])) {
                    $placeGroups[$placeId][$partType] = [
                        'before_positions' => [1 => null, 2 => null, 3 => null, 4 => null, 5 => null],
                        'after_positions' => [1 => null, 2 => null, 3 => null, 4 => null, 5 => null],
                        'before_values' => [],
                        'after_values' => [],
                        'substrate_types' => [],
                    ];
                }

                // Position bazında değer atama (1-5 arası)
                $position = $measurement->position;
                if ($position >= 1 && $position <= 5) {
                    $placeGroups[$placeId][$partType]['after_positions'][$position] = $measurement->value;
                }

                // After value'ları topla (min/max/avg için)
                if ($measurement->value !== null) {
                    $placeGroups[$placeId][$partType]['after_values'][] = (float) $measurement->value;
                }

                // Substrate type'ı topla
                if ($measurement->substrate_type) {
                    $placeGroups[$placeId][$partType]['substrate_types'][$measurement->substrate_type] = true;
                }
            }
        }

        // Her place_id için final veri yapısını oluştur
        foreach ($placeGroups as $placeId => $partsData) {
            // BEFORE measurements için veri hazırla
            $beforePlaceMeasurements = [];
            $afterPlaceMeasurements = [];

            foreach ($partsData as $partType => $data) {
                $beforeValues = $data['before_values'] ?? [];
                $afterValues = $data['after_values'] ?? [];

                // BEFORE measurements için veri hazırla
                $hasBeforeData = !empty($beforeValues) || array_filter($data['before_positions'], fn($val) => $val !== null);
                if ($hasBeforeData) {
                    $beforePlaceMeasurements[] = [
                        'part_type' => $partType,
                        'part_label' => $partLabels[$partType] ?? $partType,
                        'substrate_type' => !empty($data['substrate_types']) 
                            ? implode(' + ', array_keys($data['substrate_types']))
                            : '-',
                        'min_value' => !empty($beforeValues) ? min($beforeValues) : null,
                        'max_value' => !empty($beforeValues) ? max($beforeValues) : null,
                        'avg_value' => !empty($beforeValues) ? round(array_sum($beforeValues) / count($beforeValues), 1) : null,
                        'positions' => $data['before_positions'],
                    ];
                }

                // AFTER measurements için veri hazırla
                $hasAfterData = !empty($afterValues) || array_filter($data['after_positions'], fn($val) => $val !== null);
                if ($hasAfterData) {
                    $afterPlaceMeasurements[] = [
                        'part_type' => $partType,
                        'part_label' => $partLabels[$partType] ?? $partType,
                        'substrate_type' => !empty($data['substrate_types']) 
                            ? implode(' + ', array_keys($data['substrate_types']))
                            : '-',
                        'min_value' => !empty($afterValues) ? min($afterValues) : null,
                        'max_value' => !empty($afterValues) ? max($afterValues) : null,
                        'avg_value' => !empty($afterValues) ? round(array_sum($afterValues) / count($afterValues), 1) : null,
                        'positions' => $data['after_positions'],
                    ];
                }
            }

            if (!empty($beforePlaceMeasurements)) {
                $beforeMeasurementsByPlace[] = [
                    'place_id' => $placeId,
                    'place_label' => $placeLabels[$placeId] ?? strtoupper($placeId),
                    'measurements' => $beforePlaceMeasurements,
                ];
            }

            if (!empty($afterPlaceMeasurements)) {
                $afterMeasurementsByPlace[] = [
                    'place_id' => $placeId,
                    'place_label' => $placeLabels[$placeId] ?? strtoupper($placeId),
                    'measurements' => $afterPlaceMeasurements,
                ];
            }
        }

        // BEFORE ve AFTER unit_of_measure bilgilerini al
        $beforeUnitOfMeasure = $beforeReport->unit_of_measure ?? null;
        $afterUnitOfMeasure = $afterReport->unit_of_measure ?? null;

        return [
            'before' => [
                'measurements' => $beforeMeasurementsByPlace,
                'unit_of_measure' => $beforeUnitOfMeasure,
            ],
            'after' => [
                'measurements' => $afterMeasurementsByPlace,
                'unit_of_measure' => $afterUnitOfMeasure,
            ],
        ];
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

