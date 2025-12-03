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
    protected $description = 'Import car brands and models from JSON export file with logo downloads';

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
        $filePath = $this->argument('file') ?? storage_path('car_data_db_export.json');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        $this->info('Starting car data import...');

        try {
            // 1. Clear existing data FIRST (before reading JSON)
            $this->clearExistingData();

            // 2. Read and parse JSON
            $this->info("Reading file: {$filePath}");
            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);

            if (! $data || ! is_array($data)) {
                throw new \Exception('Invalid JSON format');
            }

            // 3. Extract brands and models from JSON
            $brandsData = [];
            $modelsData = [];

            foreach ($data as $item) {
                if ($item['type'] === 'table' && $item['name'] === 'car_brands') {
                    $brandsData = $item['data'];
                } elseif ($item['type'] === 'table' && $item['name'] === 'car_models') {
                    $modelsData = $item['data'];
                }
            }

            // 4. Group models by brand external_id to find missing brands
            $modelsByBrandExternalId = [];
            foreach ($modelsData as $modelData) {
                $brandExternalId = $modelData['brand_id'];
                if (! isset($modelsByBrandExternalId[$brandExternalId])) {
                    $modelsByBrandExternalId[$brandExternalId] = [];
                }
                $modelsByBrandExternalId[$brandExternalId][] = $modelData;
            }

            // 5. Create brand lookup array (external_id => brand_data)
            $brandLookup = [];
            foreach ($brandsData as $brandData) {
                $brandLookup[$brandData['external_id']] = $brandData;
            }

            // 6. Create missing brands from models (if brand doesn't exist in brandsData)
            $this->createMissingBrandsFromModels($modelsByBrandExternalId, $brandLookup);

            // 7. Process everything in transaction
            DB::beginTransaction();

            // Process all brands (including newly created ones)
            $this->processBrands(array_values($brandLookup));

            // Process models with brand lookup
            $this->processModelsWithLookup($modelsData, $brandLookup);

            DB::commit();

            $this->displayResults();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());

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
     * Process car brands data
     */
    private function processBrands(array $brandsData): void
    {
        $this->info('Processing car brands...');

        foreach ($brandsData as $brandData) {
            $this->processBrand($brandData);
        }
    }

    /**
     * Process a single brand
     */
    private function processBrand(array $brandData): void
    {
        // Download and save logo
        $logoPath = $this->downloadLogo($brandData['logo'] ?? null, 'car-brands');

        $brandDataArray = [
            'name' => $brandData['name'],
            'external_id' => (string) $brandData['external_id'],
            'logo' => $logoPath,
            'last_update' => $this->parseDateTime($brandData['last_update'] ?? null),
            'is_active' => (bool) ($brandData['is_active'] ?? true),
        ];

        CarBrand::create($brandDataArray);
        $this->brandsCreated++;
        $this->line("Created brand: {$brandData['name']}");

        $this->brandsProcessed++;
    }

    /**
     * Process car models with brand lookup
     */
    private function processModelsWithLookup(array $modelsData, array $brandLookup): void
    {
        $this->info('Processing car models...');

        foreach ($modelsData as $modelData) {
            $this->processModelWithLookup($modelData, $brandLookup);
        }
    }

    /**
     * Create missing brands from models (if brand doesn't exist in brandsData)
     */
    private function createMissingBrandsFromModels(array $modelsByBrandExternalId, array &$brandLookup): void
    {
        $this->info('Checking for missing brands from models...');

        foreach ($modelsByBrandExternalId as $brandExternalId => $models) {
            // If brand doesn't exist in lookup, create a placeholder brand
            if (! isset($brandLookup[$brandExternalId])) {
                // Try to find brand name from first model or use external_id
                $firstModel = $models[0];
                $brandName = $this->extractBrandNameFromModel($firstModel['name']) ?? "Brand {$brandExternalId}";

                $this->warn("⚠️  Brand not found in brands data (external_id: {$brandExternalId}), creating placeholder: {$brandName}");

                // Create placeholder brand data
                $brandLookup[$brandExternalId] = [
                    'external_id' => (string) $brandExternalId,
                    'name' => $brandName,
                    'logo' => null,
                    'last_update' => null,
                    'is_active' => true,
                ];
            }
        }
    }

    /**
     * Extract brand name from model name (e.g., "BMW 3 Series" -> "BMW")
     */
    private function extractBrandNameFromModel(string $modelName): ?string
    {
        // Try to extract brand name from model name
        // This is a simple heuristic - you might need to adjust based on your data
        $parts = explode(' ', trim($modelName));
        if (count($parts) > 0) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Process a single model with brand lookup
     */
    private function processModelWithLookup(array $modelData, array $brandLookup): void
    {
        // Brand should always exist now (we created missing ones)
        if (! isset($brandLookup[$modelData['brand_id']])) {
            $this->error("❌ Critical error: Brand not found for model: '{$modelData['name']}' (external_id: {$modelData['brand_id']})");
            $this->modelsSkipped++;

            return;
        }

        // Find brand in database by external_id
        $brand = CarBrand::where('external_id', $modelData['brand_id'])->first();

        if (! $brand) {
            $this->error("❌ Critical error: Brand not found in database for model: '{$modelData['name']}' (external_id: {$modelData['brand_id']})");
            $this->modelsSkipped++;

            return;
        }

        $modelDataArray = [
            'brand_id' => $brand->id,
            'name' => $modelData['name'],
            'external_id' => $modelData['external_id'],
            'last_update' => $this->parseDateTime($modelData['last_update'] ?? null),
            'is_active' => (bool) ($modelData['is_active'] ?? true),
        ];

        CarModel::create($modelDataArray);
        $this->modelsCreated++;
        $this->line("Created model: {$modelData['name']} ({$brand->name})");

        $this->modelsProcessed++;
    }

    /**
     * Download logo from URL and save to storage
     */
    private function downloadLogo(?string $logoUrl, string $directory): ?string
    {
        if (empty($logoUrl) || ! filter_var($logoUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($logoUrl);

            if (! $response->successful()) {
                $this->logosFailed++;

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
            $this->logosFailed++;
            $this->warn("Failed to download logo: {$logoUrl} - {$e->getMessage()}");

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

