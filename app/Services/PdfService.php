<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    private GenerateCarSvg $carSvgService;

    private array $logoCache = [];

    public function __construct(GenerateCarSvg $carSvgService)
    {
        $this->carSvgService = $carSvgService;
    }

    public function generateWarrantyPdf(object $serviceDetails): \Barryvdh\DomPDF\PDF
    {
        PDF::setOptions([
            'isRemoteEnabled' => true,
            'defaultFont' => 'Michroma Regular',
            'chroot' => base_path(),
            'enable-font-subsetting' => true,
            'enable-php' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
        ]);

        return PDF::loadView('pdf.service', [
            'carSvg' => $this->carSvgService->fillCar($serviceDetails->body_data),
            'brandLogo' => $this->getBase64Logo($serviceDetails->brand_logo),
            'brandName' => $serviceDetails->brand,
            'carModel' => $serviceDetails->model,
            'carGeneration' => $serviceDetails->generation,
            'carYear' => $serviceDetails->year,
            'page4Logo' => $this->getStorageLogoBase64('pdf_logos/with_text.png'),
            'appliedServices' => $serviceDetails->applied_services,
            'measurements' => $serviceDetails->measurements ?? [],
            'page2TableLogo' => $this->getStorageLogoBase64('pdf_logos/page_2_table.png'),
            'page2Logo' => $this->getStorageLogoBase64('pdf_logos/page_2_logo.png'),
            'page2Check' => $this->getStorageLogoBase64('pdf_logos/page_2_check.png'),
            'page3' => $this->getStoragePdfPageBase64('pdf_pages/Page 3.png'),
            'page4' => $this->getStoragePdfPageBase64('pdf_pages/Page 4.png'),
            'page5' => $this->getStoragePdfPageBase64('pdf_pages/Page 5.png'),
            'page6' => $this->getStoragePdfPageBase64('pdf_pages/Page 6.png'),
        ]);
    }

    private function getBase64Logo(string $url): string
    {
        if (isset($this->logoCache[$url])) {
            return $this->logoCache[$url];
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $logoData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($logoData === false || $httpCode !== 200) {
                throw new \Exception('Failed to fetch logo: HTTP '.$httpCode);
            }

            $base64Logo = 'data:image/png;base64,'.base64_encode($logoData);
            $this->logoCache[$url] = $base64Logo;

            return $base64Logo;
        } catch (\Exception $e) {
            // Return a default logo or throw exception based on requirements
            $defaultPath = storage_path('pdf_logos/default.png');
            if (file_exists($defaultPath)) {
                $defaultData = file_get_contents($defaultPath);

                return 'data:image/png;base64,'.base64_encode($defaultData);
            }

            throw new \Exception('Failed to fetch logo and no default logo available: '.$e->getMessage());
        }
    }

    private function getStorageLogoBase64(string $path): string
    {
        $fullPath = storage_path($path);
        $cacheKey = 'storage_logo_'.$path;

        if (isset($this->logoCache[$cacheKey])) {
            return $this->logoCache[$cacheKey];
        }

        if (! file_exists($fullPath)) {
            throw new \Exception("Logo file not found: {$path} (checked: {$fullPath})");
        }

        $base64Logo = 'data:image/png;base64,'.base64_encode(file_get_contents($fullPath));
        $this->logoCache[$cacheKey] = $base64Logo;

        return $base64Logo;
    }

    private function getStoragePdfPageBase64(string $path): string
    {
        // pdf_pages/Page 3.png formatında path geliyor
        $fullPath = storage_path($path);
        $cacheKey = 'storage_pdf_page_'.$path;

        if (isset($this->logoCache[$cacheKey])) {
            return $this->logoCache[$cacheKey];
        }

        if (! file_exists($fullPath)) {
            throw new \Exception("PDF page file not found: {$path} (checked: {$fullPath})");
        }

        $base64Page = 'data:image/png;base64,'.base64_encode(file_get_contents($fullPath));
        $this->logoCache[$cacheKey] = $base64Page;

        return $base64Page;
    }

    /**
     * PDF'i cache'den al veya yeni oluştur
     *
     * @param  string  $serviceNo  Servis numarası
     * @param  object  $serviceDetails  Servis detayları
     * @param  bool  $forceGenerate  Zorla yeniden oluştur
     * @return string PDF dosya path'i
     */
    public function getOrGeneratePdf(string $serviceNo, object $serviceDetails, bool $forceGenerate = false): string
    {
        $disk = Storage::disk(config('filesystems.default'));
        $filePath = $this->getPdfFilePath($serviceNo);

        // Eğer zorla oluştur isteniyorsa direkt oluştur
        if ($forceGenerate) {
            return $this->generateAndSavePdf($serviceNo, $serviceDetails, $filePath);
        }

        // Dosya var mı kontrol et (S3 için try-catch ile)
        $fileExists = false;
        try {
            $fileExists = $disk->exists($filePath);
        } catch (\Exception $e) {
            // S3 hatası durumunda dosya yok sayılır
            \Log::warning('PDF existence check failed, will regenerate', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            $fileExists = false;
        }

        // Dosya yoksa veya eskiyse yeni oluştur
        if (! $fileExists || $this->isPdfExpired($filePath)) {
            return $this->generateAndSavePdf($serviceNo, $serviceDetails, $filePath);
        }

        // Mevcut dosyayı döndür
        return $filePath;
    }

    /**
     * PDF dosya path'ini oluştur (tarih ile)
     *
     * @param  string  $serviceNo  Servis numarası
     * @return string Dosya path'i
     */
    private function getPdfFilePath(string $serviceNo): string
    {
        $today = Carbon::now()->format('Y-m-d');
        $fileName = "service-{$serviceNo}-{$today}.pdf";

        return "service-pdfs/{$fileName}";
    }

    /**
     * PDF'in süresi dolmuş mu kontrol et (1 aydan fazla)
     *
     * @param  string  $filePath  Dosya path'i
     * @return bool True ise süresi dolmuş
     */
    private function isPdfExpired(string $filePath): bool
    {
        $disk = Storage::disk(config('filesystems.default'));

        // Dosya var mı kontrol et (S3 için try-catch ile)
        try {
            if (! $disk->exists($filePath)) {
                return true;
            }
        } catch (\Exception $e) {
            // S3 hatası durumunda eski kabul et
            \Log::warning('PDF expiration check failed, will regenerate', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return true;
        }

        // Dosya isminden tarihi çıkar (service-SERVICE_NO-2025-01-15.pdf)
        $fileName = basename($filePath);
        if (preg_match('/service-.*?-(\d{4}-\d{2}-\d{2})\.pdf$/', $fileName, $matches)) {
            try {
                $fileDate = Carbon::parse($matches[1]);
                $oneMonthAgo = Carbon::now()->subMonth();

                return $fileDate->isBefore($oneMonthAgo);
            } catch (\Exception $e) {
                // Tarih parse edilemezse eski kabul et
                \Log::warning('PDF date parsing failed', [
                    'path' => $filePath,
                    'date' => $matches[1] ?? null,
                    'error' => $e->getMessage(),
                ]);

                return true;
            }
        }

        // Tarih parse edilemezse eski kabul et
        return true;
    }

    /**
     * PDF oluştur ve kaydet
     *
     * @param  string  $serviceNo  Servis numarası
     * @param  object  $serviceDetails  Servis detayları
     * @param  string  $filePath  Kaydedilecek dosya path'i
     * @return string Kaydedilen dosya path'i
     */
    private function generateAndSavePdf(string $serviceNo, object $serviceDetails, string $filePath): string
    {
        $disk = Storage::disk(config('filesystems.default'));

        // PDF oluştur
        $pdf = $this->generateWarrantyPdf($serviceDetails);

        // PDF içeriğini al
        $pdfContent = $pdf->output();

        // Dosyayı kaydet
        $disk->put($filePath, $pdfContent);

        return $filePath;
    }
}
