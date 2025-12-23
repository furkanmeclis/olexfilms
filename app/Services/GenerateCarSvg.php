<?php

namespace App\Services;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Application;

class GenerateCarSvg
{
    public string $svgCarPath = '';

    public array $parts = [
        'window_sunroof',
        'window_on_cam',
        'window_arka_cam',
        'window_sol_arka_kapi',
        'window_sol_on_kapi',
        'window_sag_arka_kapi',
        'window_sag_on_kapi',
        'body_tavan',
        'body_kaput',
        'body_bagaj',
        'body_arka_tampon',
        'body_on_tampon',
        'body_sol_arka_camurluk',
        'body_sol_on_camurluk',
        'body_sol_arka_kapi',
        'body_sol_on_kapi',
        'body_sag_arka_camurluk',
        'body_sag_on_camurluk',
        'body_sag_arka_kapi',
        'body_sag_on_kapi',
    ];

    public array $activeColors = [
        'window_sunroof' => '#3db5ff',
        'window_on_cam' => '#3db5ff',
        'window_arka_cam' => '#3db5ff',
        'window_sol_arka_kapi' => '#3db5ff',
        'window_sol_on_kapi' => '#3db5ff',
        'window_sag_arka_kapi' => '#3db5ff',
        'window_sag_on_kapi' => '#3db5ff',
        'body_tavan' => '#1a8f14',
        'body_kaput' => '#1a8f14',
        'body_bagaj' => '#1a8f14',
        'body_arka_tampon' => '#1a8f14',
        'body_on_tampon' => '#1a8f14',
        'body_sol_arka_camurluk' => '#1a8f14',
        'body_sol_on_camurluk' => '#1a8f14',
        'body_sol_arka_kapi' => '#1a8f14',
        'body_sol_on_kapi' => '#1a8f14',
        'body_sag_arka_camurluk' => '#1a8f14',
        'body_sag_on_camurluk' => '#1a8f14',
        'body_sag_arka_kapi' => '#1a8f14',
        'body_sag_on_kapi' => '#1a8f14',
    ];

    public string $svgContent = '';

    public function __construct()
    {
        $this->svgCarPath = storage_path('olex_car_template.svg');
        $this->svgContent = file_get_contents($this->svgCarPath);
    }

    public function getActiveColor(string $partName): string
    {
        return $this->activeColors[$partName] ?? '#efefef';
    }

    public function generateFile(): Application|string|UrlGenerator
    {
        $fileName = 'car_'.time().'.svg';
        $path = public_path('cache/'.$fileName);
        $this->deleteOtherFiles();
        file_put_contents($path, $this->svgContent);

        return url('cache/'.$fileName);
    }

    public function deleteOtherFiles(): void
    {
        $files = glob(public_path('cache/*'));

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function fillCar(array $partNames = [], bool $returnBase64 = true): array|string|false
    {
        $svgContent = $this->svgContent;

        foreach ($partNames as $part) {
            $fillColor = $this->getActiveColor($part);

            // Pattern to match path with id attribute
            $pattern = "/(<path[^>]*id=\"$part\"[^>]*fill=)[\"'].*?[\"']/";
            $replacement = "$1\"$fillColor\"";

            $svgContent = preg_replace($pattern, $replacement, $svgContent);

            // Also handle style attribute with fill
            $patternStyle = "/(<path[^>]*id=\"$part\"[^>]*style=\"[^\"]*fill:\s*)[^;]+(;)/";
            $replacementStyle = "$1$fillColor$2";

            $svgContent = preg_replace($patternStyle, $replacementStyle, $svgContent);
        }

        $this->svgContent = $svgContent;

        return $returnBase64 ? $this->convertBase64() : $this->generateFile();
    }

    public function convertBase64(): string
    {
        $base64 = base64_encode($this->svgContent);

        return 'data:image/svg+xml;base64,'.$base64;
    }
}
