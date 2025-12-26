<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Database\Migrators\BaseMigrator;
use App\Database\Migrators\CarBrandsMigrator;
use App\Database\Migrators\CarModelsMigrator;
use App\Database\Migrators\CustomersMigrator;
use App\Database\Migrators\DealersMigrator;
use App\Database\Migrators\OrdersMigrator;
use App\Database\Migrators\ProductCategoriesMigrator;
use App\Database\Migrators\ProductsMigrator;
use App\Database\Migrators\ServiceItemsMigrator;
use App\Database\Migrators\ServicesMigrator;
use App\Database\Migrators\StockItemsMigrator;
use App\Database\Migrators\UsersMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:old-db
                            {--module= : Belirli bir modülü migrate et}
                            {--dry-run : Veri yazmadan test et}
                            {--force : Onay beklemeden çalıştır}
                            {--refresh : Yeni veritabanını refresh et (migrate:fresh)}
                            {--seed : Refresh sonrası seed çalıştır}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eski veritabanından yeni şemaya veri aktarımı yapar';

    /**
     * Migrator sınıflarının sıralı listesi (bağımlılık sırasına göre)
     */
    protected array $migrators = [
        'car_brands' => CarBrandsMigrator::class,
        'car_models' => CarModelsMigrator::class,
        'users' => UsersMigrator::class,
        'dealers' => DealersMigrator::class,
        'product_categories' => ProductCategoriesMigrator::class,
        'products' => ProductsMigrator::class,
        'customers' => CustomersMigrator::class,
        'stock_items' => StockItemsMigrator::class,
        'orders' => OrdersMigrator::class,
        'services' => ServicesMigrator::class,
        'service_items' => ServiceItemsMigrator::class,
    ];

    /**
     * ID mapping'leri saklamak için
     */
    protected array $idMappings = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Eski Veritabanından Yeni Şemaya Veri Aktarımı');
        $this->newLine();

        // Bağlantı kontrolü
        if (! $this->checkConnections()) {
            return Command::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $module = $this->option('module');
        $force = $this->option('force');
        $refresh = $this->option('refresh');
        $seed = $this->option('seed');

        // Refresh işlemi
        if ($refresh) {
            if (! $force && ! $this->confirm('Yeni veritabanı refresh edilecek (tüm veriler silinecek). Devam etmek istiyor musunuz?', false)) {
                $this->info('İşlem iptal edildi.');
                return Command::SUCCESS;
            }

            $this->info('Veritabanı refresh ediliyor...');
            $this->call('migrate:fresh', array_filter([
                '--seed' => $seed,
                '--force' => true,
            ]));
            $this->newLine();
        }

        if ($dryRun) {
            $this->warn('DRY-RUN MODU: Veriler yazılmayacak!');
            $this->newLine();
        }

        if (! $force && ! $dryRun) {
            if (! $this->confirm('Eski veritabanından veri aktarımı yapılacak. Devam etmek istiyor musunuz?', true)) {
                $this->info('İşlem iptal edildi.');
                return Command::SUCCESS;
            }
        }

        try {
            if ($module) {
                return $this->migrateModule($module, $dryRun);
            }

            return $this->migrateAll($dryRun);
        } catch (\Exception $e) {
            $this->error("Kritik hata: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Tüm modülleri migrate eder
     */
    protected function migrateAll(bool $dryRun): int
    {
        $this->info('Tüm modüller migrate ediliyor...');
        $this->newLine();

        // Car brands/models migrate edilecekse, önce ImportCarDataFromExport çalıştır
        if (isset($this->migrators['car_brands']) || isset($this->migrators['car_models'])) {
            $this->importCarData();
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($this->migrators as $moduleName => $migratorClass) {
            // Car brands/models için migrator çalıştırma, sadece import komutu yeterli
            if (in_array($moduleName, ['car_brands', 'car_models'])) {
                continue;
            }

            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("Modül: {$moduleName}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            try {
                $migrator = $this->createMigrator($migratorClass, $dryRun);
                $success = $migrator->migrate();

                if ($success) {
                    $successCount++;
                    $this->idMappings[$moduleName] = $migrator->getIdMapping();
                } else {
                    $failCount++;
                    $this->warn("Modül {$moduleName} başarısız oldu, devam ediliyor...");
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->error("Modül {$moduleName} hatası: {$e->getMessage()}");
            }

            $this->newLine();
        }

        $this->displayFinalSummary($successCount, $failCount);

        return $failCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Belirli bir modülü migrate eder
     */
    protected function migrateModule(string $module, bool $dryRun): int
    {
        if (! isset($this->migrators[$module])) {
            $this->error("Modül '{$module}' bulunamadı.");
            $this->info('Mevcut modüller: '.implode(', ', array_keys($this->migrators)));

            return Command::FAILURE;
        }

        // Car brands/models için sadece ImportCarDataFromExport çalıştır, migrator çalıştırma
        if (in_array($module, ['car_brands', 'car_models'])) {
            $this->importCarData();

            return Command::SUCCESS;
        }

        $migratorClass = $this->migrators[$module];
        $this->info("Modül '{$module}' migrate ediliyor...");
        $this->newLine();

        try {
            $migrator = $this->createMigrator($migratorClass, $dryRun);
            $success = $migrator->migrate();

            if ($success) {
                $this->idMappings[$module] = $migrator->getIdMapping();
            }

            return $success ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("Modül hatası: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * Migrator instance'ı oluşturur
     */
    protected function createMigrator(string $migratorClass, bool $dryRun): BaseMigrator
    {
        return new $migratorClass($this, $dryRun, $this->idMappings);
    }

    /**
     * Veritabanı bağlantılarını kontrol eder
     */
    protected function checkConnections(): bool
    {
        try {
            DB::connection('old_db')->getPdo();
            $this->info('✓ Eski veritabanı bağlantısı başarılı');
        } catch (\Exception $e) {
            $this->error('✗ Eski veritabanı bağlantısı başarısız: '.$e->getMessage());
            $this->error('Lütfen .env dosyasında OLD_DB_* ayarlarını kontrol edin.');

            return false;
        }

        try {
            DB::connection()->getPdo();
            $this->info('✓ Yeni veritabanı bağlantısı başarılı');
        } catch (\Exception $e) {
            $this->error('✗ Yeni veritabanı bağlantısı başarısız: '.$e->getMessage());

            return false;
        }

        $this->newLine();

        return true;
    }

    /**
     * Final özeti gösterir
     */
    protected function displayFinalSummary(int $successCount, int $failCount): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('FİNAL ÖZET');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("Başarılı modüller: {$successCount}");
        $this->info("Başarısız modüller: {$failCount}");
        $this->info('Toplam modül: '.(count($this->migrators)));
        $this->newLine();
    }

    /**
     * ImportCarDataFromExport komutunu çalıştırır
     */
    protected function importCarData(): void
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Araç Verileri İçe Aktarılıyor');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        try {
            $this->call('car:import-export');
            $this->newLine();
            $this->info('✓ Araç verileri başarıyla içe aktarıldı');
            $this->newLine();
        } catch (\Exception $e) {
            $this->warn('⚠ Araç verileri içe aktarılamadı: '.$e->getMessage());
            $this->warn('Devam ediliyor...');
            $this->newLine();
        }
    }
}

