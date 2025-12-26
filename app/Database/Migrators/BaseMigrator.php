<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseMigrator
{
    protected Connection $oldDb;
    protected Connection $newDb;
    protected Command $command;
    protected bool $dryRun;
    protected array $idMapping = [];
    protected array $previousMappings = [];
    protected string $tableName;
    protected int $processed = 0;
    protected int $successful = 0;
    protected int $failed = 0;
    protected array $errors = [];
    protected ?string $lastTransformError = null;

    public function __construct(Command $command, bool $dryRun = false, array $previousMappings = [])
    {
        $this->command = $command;
        $this->dryRun = $dryRun;
        $this->previousMappings = $previousMappings;
        $this->oldDb = DB::connection('old_db');
        $this->newDb = DB::connection();
        $this->tableName = $this->getTableName();
    }

    /**
     * Tablo adını döndürür
     */
    abstract protected function getTableName(): string;

    /**
     * Eski tablodan verileri okur
     */
    abstract protected function readOldData(): \Generator;

    /**
     * Veriyi yeni formata dönüştürür
     */
    abstract protected function transformData(array $oldData): ?array;

    /**
     * Yeni veritabanına kaydeder
     */
    abstract protected function saveNewData(array $newData): ?int;

    /**
     * Ana migrasyon metodu
     */
    public function migrate(): bool
    {
        $this->command->info("Migrating {$this->tableName}...");

        try {
            $this->newDb->beginTransaction();

            $total = $this->getTotalCount();
            $bar = $this->command->getOutput()->createProgressBar($total);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $bar->setMessage('Starting...');

            foreach ($this->readOldData() as $oldData) {
                $bar->setMessage("Processing ID: {$oldData['id']}...");
                $bar->advance();

                try {
                    // Transform hatasını sıfırla
                    $this->lastTransformError = null;
                    
                    $transformed = $this->transformData($oldData);

                    if ($transformed === null) {
                        $this->failed++;
                        $errorMessage = $this->getLastTransformError() ?? 'Transform returned null';
                        $this->errors[] = [
                            'old_id' => $oldData['id'],
                            'error' => $errorMessage,
                        ];
                        
                        // Log dosyasına kaydet
                        Log::warning("Migration skipped for {$this->tableName}", [
                            'old_id' => $oldData['id'],
                            'reason' => $errorMessage,
                            'data' => $this->sanitizeDataForLog($oldData),
                        ]);
                        
                        continue;
                    }

                    if (! $this->dryRun) {
                        $newId = $this->saveNewData($transformed);

                        if ($newId) {
                            $this->idMapping[$oldData['id']] = $newId;
                            $this->successful++;
                        } else {
                            $this->failed++;
                            $this->errors[] = [
                                'old_id' => $oldData['id'],
                                'error' => 'Save returned null',
                            ];
                        }
                    } else {
                        $this->successful++;
                    }

                    $this->processed++;
                } catch (\Exception $e) {
                    $this->failed++;
                    $this->errors[] = [
                        'old_id' => $oldData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                    Log::error("Migration error for {$this->tableName}", [
                        'old_id' => $oldData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $bar->setMessage('Completed');
            $bar->finish();
            $this->command->newLine(2);

            if (! $this->dryRun) {
                $this->newDb->commit();
            } else {
                $this->newDb->rollBack();
            }

            $this->displaySummary();

            return $this->failed === 0;
        } catch (\Exception $e) {
            if (! $this->dryRun) {
                $this->newDb->rollBack();
            }

            $this->command->error("Migration failed for {$this->tableName}: {$e->getMessage()}");
            Log::error("Migration failed for {$this->tableName}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Eski veritabanından toplam kayıt sayısını döndürür
     */
    protected function getTotalCount(): int
    {
        return $this->oldDb()->count();
    }

    /**
     * Eski tablo adını döndürür (varsayılan olarak yeni tablo adıyla aynı)
     */
    protected function getOldTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Read-only old database connection wrapper
     * Sadece SELECT sorgularına izin verir
     */
    protected function oldDb(): Builder
    {
        return $this->oldDb->table($this->getOldTableName());
    }

    /**
     * ID mapping'i döndürür
     */
    public function getIdMapping(): array
    {
        return $this->idMapping;
    }

    /**
     * Eski ID'ye karşılık gelen yeni ID'yi döndürür
     */
    protected function getMappedId(int $oldId, ?array $mapping = null): ?int
    {
        $mapping = $mapping ?? $this->idMapping;

        return $mapping[$oldId] ?? null;
    }

    /**
     * Önceki bir migrator'ın ID mapping'ini döndürür
     */
    protected function getPreviousMapping(string $migratorName): array
    {
        return $this->previousMappings[$migratorName] ?? [];
    }

    /**
     * Son transform hatasını al
     */
    protected function getLastTransformError(): ?string
    {
        return $this->lastTransformError;
    }

    /**
     * Transform hatasını kaydet
     */
    protected function setTransformError(string $error): void
    {
        $this->lastTransformError = $error;
    }

    /**
     * Log için veriyi temizle (sensitive data'yı kaldır)
     */
    protected function sanitizeDataForLog(array $data): array
    {
        // Password, token gibi sensitive field'ları kaldır
        $sensitiveFields = ['password', 'remember_token', 'api_token', 'fcm_token', 'player_id'];
        $sanitized = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '***REDACTED***';
            }
        }
        
        // Çok uzun string'leri kısalt
        foreach ($sanitized as $key => $value) {
            if (is_string($value) && strlen($value) > 500) {
                $sanitized[$key] = substr($value, 0, 500) . '... (truncated)';
            }
        }
        
        return $sanitized;
    }

    /**
     * Özet bilgileri gösterir
     */
    protected function displaySummary(): void
    {
        $this->command->info("Summary for {$this->tableName}:");
        $this->command->line("  Processed: {$this->processed}");
        $this->command->line("  Successful: {$this->successful}");
        $this->command->line("  Failed: {$this->failed}");

        if ($this->failed > 0 && count($this->errors) > 0) {
            $this->command->warn('Errors:');
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->command->line("  - Old ID {$error['old_id']}: {$error['error']}");
            }
            if (count($this->errors) > 10) {
                $this->command->line("  ... and ".(count($this->errors) - 10)." more errors");
            }
        }
    }

    /**
     * JSON string'i decode eder, hata durumunda null döner
     */
    protected function safeJsonDecode(?string $json): ?array
    {
        if (empty($json)) {
            return null;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }

    /**
     * Enum değerini string'e çevirir
     */
    protected function enumToString($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'value')) {
            return $value->value;
        }

        return (string) $value;
    }

    /**
     * Boolean değeri tinyint'e çevirir
     */
    protected function boolToTinyint(?bool $value): int
    {
        return $value ? 1 : 0;
    }

    /**
     * String veya integer'ı integer'a çevirir, hata durumunda null döner
     */
    protected function safeIntCast(string|int|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    /**
     * Resim dosyasını storage'a kaydeder
     * URL, path veya base64 string kabul eder
     */
    protected function saveImageToStorage(?string $imageSource, string $directory): ?string
    {
        if (empty($imageSource)) {
            return null;
        }

        try {
            // Eğer zaten storage path'i ise (yeni DB'den geliyorsa), olduğu gibi döndür
            if (str_starts_with($imageSource, $directory.'/')) {
                return $imageSource;
            }

            // Eğer URL ise, indir
            if (filter_var($imageSource, FILTER_VALIDATE_URL)) {
                return $this->downloadImageFromUrl($imageSource, $directory);
            }

            // Eğer eski DB'deki path ise, dosyayı kopyala veya URL olarak işle
            // Eski sistemde logo genelde URL veya relative path olabilir
            if (str_starts_with($imageSource, 'http')) {
                return $this->downloadImageFromUrl($imageSource, $directory);
            }

            // Local path ise (eski sistemden), dosyayı kopyala
            if (file_exists($imageSource)) {
                return $this->copyLocalImage($imageSource, $directory);
            }

            // Eski DB'deki path'i URL olarak dene (public path olabilir)
            $publicPath = public_path($imageSource);
            if (file_exists($publicPath)) {
                return $this->copyLocalImage($publicPath, $directory);
            }

            return null;
        } catch (\Exception $e) {
            $this->command->warn("Resim kaydedilemedi: {$imageSource} - {$e->getMessage()}");
            return null;
        }
    }

    /**
     * URL'den resim indirip storage'a kaydeder
     */
    protected function downloadImageFromUrl(string $url, string $directory): ?string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $extension = $this->getImageExtensionFromUrl($url, $response->header('Content-Type'));
            $filename = \Illuminate\Support\Str::random(40).'.'.$extension;
            $filePath = "{$directory}/{$filename}";

            \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->put($filePath, $response->body());

            return $filePath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Local dosyayı storage'a kopyalar
     */
    protected function copyLocalImage(string $localPath, string $directory): ?string
    {
        try {
            $extension = pathinfo($localPath, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = \Illuminate\Support\Str::random(40).'.'.$extension;
            $filePath = "{$directory}/{$filename}";

            $content = file_get_contents($localPath);
            if ($content === false) {
                return null;
            }

            \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->put($filePath, $content);

            return $filePath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * URL veya Content-Type'dan resim uzantısını alır
     */
    protected function getImageExtensionFromUrl(string $url, ?string $contentType): string
    {
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        if (!empty($pathInfo['extension'])) {
            return $pathInfo['extension'];
        }

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

        return 'jpg';
    }
}

