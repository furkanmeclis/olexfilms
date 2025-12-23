<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CarBrand;
use App\Models\CarModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportCarDataFromExport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'car:import-export {file? : JSON export file path}';

    /**
     * The console command description.
     */
    protected $description = 'Import car brands and models from autodatacars JSON export file with logo downloads';

    private int $brandsProcessed = 0;

    private int $modelsProcessed = 0;

    private int $brandsCreated = 0;

    private int $modelsCreated = 0;

    private int $modelsSkipped = 0;

    private int $logosDownloaded = 0;

    private int $logosFailed = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file') ?? storage_path('car_data_autodatacars_export.json');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        $this->info('Starting car data import...');

        try {
            // 1. Load logo URLs from car_data_db_export.json
            $this->info('Loading logo URLs from car_data_db_export.json...');
            $logoLookup = $this->loadLogoUrlsFromExport();

            // 2. Clear existing data FIRST (before reading JSON)
            $this->clearExistingData();

            // 3. Read and parse JSON
            $this->info("Reading file: {$filePath}");
            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);

            if (! $data || ! isset($data['brands']['brand']) || ! is_array($data['brands']['brand'])) {
                throw new \Exception('Invalid JSON format. Expected brands.brand array.');
            }

            // 4. Process everything in transaction
            DB::beginTransaction();

            // 5. Prepare brands data
            $brands = [];
            foreach ($data['brands']['brand'] as $brandData) {
                $externalId = (string) $brandData['id'];
                $brandName = $brandData['name'];

                // Try to find logo URL: first by external_id, then by name
                $logoUrl = $logoLookup['by_external_id'][$externalId] ??
                          $logoLookup['by_name'][strtolower(trim($brandName))] ??
                          null;

                // If logo URL found in export, download it; otherwise try template URLs
                if ($logoUrl) {
                    $logoPath = $this->downloadLogo($logoUrl, 'car-brands');
                    if ($logoPath === null) {
                        // If download failed, try template URLs as fallback
                        $logoPath = $this->downloadLogoWithExtensions($brandName, 'car-brands');
                    }
                } else {
                    // Fallback to template URLs
                    $logoPath = $this->downloadLogoWithExtensions($brandName, 'car-brands');
                }

                $brands[] = [
                    'external_id' => $externalId,
                    'name' => $brandData['name'],
                    'logo' => $logoPath,
                    'last_update' => $this->parseDateTime($brandData['update'] ?? null),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 6. Batch insert brands
            $this->insertInChunks(CarBrand::class, $brands, 'external_id', ['name', 'logo', 'last_update', 'is_active']);

            // 7. Prepare models data
            $models = [];
            foreach ($data['brands']['brand'] as $brandData) {
                $brand = CarBrand::where('external_id', $brandData['id'])->first();

                if (! $brand) {
                    $this->warn("Brand not found for external_id: {$brandData['id']}");

                    continue;
                }

                if (isset($brandData['models']['model']) && is_array($brandData['models']['model'])) {
                    foreach ($brandData['models']['model'] as $modelData) {
                        // Collect all modifications from all generations
                        $modifications = $this->collectModifications($modelData);

                        $models[] = [
                            'external_id' => (string) $modelData['id'],
                            'brand_id' => $brand->id,
                            'name' => $modelData['name'],
                            'last_update' => $this->parseDateTime($modelData['update'] ?? null),
                            'is_active' => true,
                            'powertrain' => $modifications['powertrain'],
                            'yearstart' => $modifications['yearstart'],
                            'yearstop' => $modifications['yearstop'],
                            'coupe' => $modifications['coupe'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // 8. Batch insert models
            $this->insertInChunks(CarModel::class, $models, 'external_id', [
                'brand_id',
                'name',
                'last_update',
                'is_active',
                'powertrain',
                'yearstart',
                'yearstop',
                'coupe',
            ]);

            DB::commit();

            $this->displayResults();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }

        return 0;
    }

    /**
     * Clear existing data and images
     */
    private function clearExistingData(): void
    {
        $this->info('Clearing existing data...');

        // Clear database
        CarModel::withTrashed()->forceDelete();
        CarBrand::withTrashed()->forceDelete();

        // Clear images
        $this->clearImages();

        $this->info('Existing data cleared successfully.');
    }

    /**
     * Clear image directories
     */
    private function clearImages(): void
    {
        $directories = ['car-brands', 'car-models'];

        foreach ($directories as $directory) {
            $path = storage_path("app/public/{$directory}");
            if (is_dir($path)) {
                $files = glob($path.'/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                $this->line("Cleared images from: {$directory}");
            }
        }
    }

    /**
     * Load logo URLs from car_data_db_export.json file
     * Returns array with both external_id and name => logo_url mappings
     */
    private function loadLogoUrlsFromExport(): array
    {
        $exportFilePath = storage_path('car_data_db_export.json');

        if (! file_exists($exportFilePath)) {
            $this->warn("Logo export file not found: {$exportFilePath}. Will use template URLs as fallback.");

            return ['by_external_id' => [], 'by_name' => []];
        }

        $exportContent = file_get_contents($exportFilePath);
        $exportData = json_decode($exportContent, true);

        if (! $exportData || ! is_array($exportData)) {
            $this->warn('Invalid logo export file format. Will use template URLs as fallback.');

            return ['by_external_id' => [], 'by_name' => []];
        }

        $logoLookupById = [];
        $logoLookupByName = [];

        // Find car_brands table in export data
        foreach ($exportData as $item) {
            if (isset($item['type']) && $item['type'] === 'table' &&
                isset($item['name']) && $item['name'] === 'car_brands' &&
                isset($item['data']) && is_array($item['data'])) {
                foreach ($item['data'] as $brandData) {
                    if (isset($brandData['logo']) && ! empty($brandData['logo'])) {
                        // Index by external_id
                        if (isset($brandData['external_id'])) {
                            $logoLookupById[(string) $brandData['external_id']] = $brandData['logo'];
                        }
                        // Index by name (case-insensitive)
                        if (isset($brandData['name'])) {
                            $logoLookupByName[strtolower(trim($brandData['name']))] = $brandData['logo'];
                        }
                    }
                }
                break;
            }
        }

        $this->info('Loaded '.count($logoLookupById).' logo URLs by external_id and '.count($logoLookupByName).' by name from export file.');

        return [
            'by_external_id' => $logoLookupById,
            'by_name' => $logoLookupByName,
        ];
    }

    /**
     * Download logo from auto-data.net
     * Used as fallback when logo URL is not found in export file
     */
    private function downloadLogoWithExtensions(string $brandName, string $directory): ?string
    {
        // Replace spaces with underscores in brand name
        $brandSlug = str_replace(' ', '_', $brandName);

        // Try both sizes: logos2 (100x100) first, then logos (40x40)
        $paths = [
            "https://www.auto-data.net/img/logos2/{$brandSlug}.png", // 100x100
            "https://www.auto-data.net/img/logos/{$brandSlug}.png",  // 40x40
        ];

        foreach ($paths as $logoUrl) {
            $logoPath = $this->downloadLogo($logoUrl, $directory);

            if ($logoPath !== null) {
                return $logoPath;
            }
        }

        // Only warn if all paths failed
        $this->logosFailed++;
        $this->warn("No logo found for brand: {$brandName} (tried auto-data.net logos2 and logos)");

        return null;
    }

    /**
     * Collect and aggregate modifications from all generations
     */
    private function collectModifications(array $modelData): array
    {
        $allPowertrains = [];
        $allYearStarts = [];
        $allYearStops = [];
        $allCoupes = [];

        if (isset($modelData['generations']['generation']) && is_array($modelData['generations']['generation'])) {
            foreach ($modelData['generations']['generation'] as $generation) {
                if (isset($generation['modifications']['modification']) && is_array($generation['modifications']['modification'])) {
                    foreach ($generation['modifications']['modification'] as $modification) {
                        // Collect powertrain
                        if (! empty($modification['powertrain'])) {
                            $allPowertrains[] = $modification['powertrain'];
                        }

                        // Collect yearstart
                        if (! empty($modification['yearstart'])) {
                            $year = (int) $modification['yearstart'];
                            if ($year > 0) {
                                $allYearStarts[] = $year;
                            }
                        }

                        // Collect yearstop (boş string ise NULL say)
                        if (! empty($modification['yearstop'])) {
                            $year = (int) $modification['yearstop'];
                            if ($year > 0) {
                                $allYearStops[] = $year;
                            }
                        }

                        // Collect coupe
                        if (! empty($modification['coupe'])) {
                            $allCoupes[] = $modification['coupe'];
                        }
                    }
                }
            }
        }

        return [
            'powertrain' => ! empty($allPowertrains) ? implode(', ', array_unique($allPowertrains)) : null,
            'yearstart' => ! empty($allYearStarts) ? min($allYearStarts) : null,
            'yearstop' => ! empty($allYearStops) ? max($allYearStops) : null,
            'coupe' => ! empty($allCoupes) ? implode(', ', array_unique($allCoupes)) : null,
        ];
    }

    /**
     * Insert data in chunks to avoid memory issues
     */
    private function insertInChunks(string $modelClass, array $data, string $uniqueKey, array $updateColumns): void
    {
        if (empty($data)) {
            return;
        }

        $chunkSize = 500;
        $chunks = array_chunk($data, $chunkSize);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $this->info('Processing chunk '.($index + 1)."/{$totalChunks}...");

            foreach ($chunk as $item) {
                $uniqueValue = $item[$uniqueKey];
                $existing = $modelClass::where($uniqueKey, $uniqueValue)->first();

                if ($existing) {
                    // Update existing record
                    $updateData = [];
                    foreach ($updateColumns as $column) {
                        if (isset($item[$column])) {
                            $updateData[$column] = $item[$column];
                        }
                    }
                    $existing->update($updateData);

                    if ($modelClass === CarBrand::class) {
                        $this->brandsProcessed++;
                    } else {
                        $this->modelsProcessed++;
                    }
                } else {
                    // Create new record
                    $modelClass::create($item);

                    if ($modelClass === CarBrand::class) {
                        $this->brandsCreated++;
                        $this->brandsProcessed++;
                        $this->line("Created brand: {$item['name']}");
                    } else {
                        $this->modelsCreated++;
                        $this->modelsProcessed++;
                        $brandName = CarBrand::find($item['brand_id'])->name ?? 'Unknown';
                        $this->line("Created model: {$item['name']} ({$brandName})");
                    }
                }
            }
        }
    }

    /**
     * Download logo from URL and save to storage
     * Returns null silently on failure (to allow trying different extensions)
     */
    private function downloadLogo(?string $logoUrl, string $directory): ?string
    {
        if (empty($logoUrl) || ! filter_var($logoUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($logoUrl);

            if (! $response->successful()) {
                // Silently return null to allow trying next extension
                return null;
            }

            // Get file extension from URL or content type
            $extension = $this->getFileExtension($logoUrl, $response->header('Content-Type'));
            $filename = Str::random(40).'.'.$extension;
            $filePath = "{$directory}/{$filename}";

            // Save to public disk
            Storage::disk(config('filesystems.default'))->put($filePath, $response->body());

            $this->logosDownloaded++;

            return $filePath;

        } catch (\Exception $e) {
            // Silently return null to allow trying next extension
            return null;
        }
    }

    /**
     * Get file extension from URL or content type
     */
    private function getFileExtension(string $url, ?string $contentType): string
    {
        // Try to get extension from URL
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        if (! empty($pathInfo['extension'])) {
            return $pathInfo['extension'];
        }

        // Try to get extension from content type
        if ($contentType) {
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
            ];

            if (isset($mimeToExt[$contentType])) {
                return $mimeToExt[$contentType];
            }
        }

        // Default to jpg
        return 'jpg';
    }

    /**
     * Parse datetime string
     */
    private function parseDateTime(?string $dateTimeString): ?Carbon
    {
        if (empty($dateTimeString)) {
            return null;
        }

        try {
            return Carbon::parse($dateTimeString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Display import results
     */
    private function displayResults(): void
    {
        $this->newLine();
        $this->info('=== Import Results ===');
        $this->table(
            ['Type', 'Processed', 'Created', 'Skipped'],
            [
                ['Brands', $this->brandsProcessed, $this->brandsCreated, '-'],
                ['Models', $this->modelsProcessed, $this->modelsCreated, $this->modelsSkipped],
            ]
        );

        $this->newLine();
        $this->info('=== Logo Download Results ===');
        $this->info("Downloaded: {$this->logosDownloaded}");
        $this->info("Failed: {$this->logosFailed}");

        $this->newLine();
        $this->info('Import completed successfully!');
    }
}
