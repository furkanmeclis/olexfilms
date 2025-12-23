<?php

namespace App\Filament\Exports;

use App\Models\Dealer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class DealerExporter extends Exporter
{
    protected static ?string $model = Dealer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('dealer_code')
                ->label('Bayi Kodu'),
            ExportColumn::make('name')
                ->label('Bayi Adı'),
            ExportColumn::make('email')
                ->label('E-posta'),
            ExportColumn::make('phone')
                ->label('Telefon'),
            ExportColumn::make('address')
                ->label('Adres'),
            ExportColumn::make('city')
                ->label('İl'),
            ExportColumn::make('district')
                ->label('İlçe'),
            ExportColumn::make('website_url')
                ->label('Web Sitesi'),
            ExportColumn::make('facebook_url')
                ->label('Facebook'),
            ExportColumn::make('instagram_url')
                ->label('Instagram'),
            ExportColumn::make('twitter_url')
                ->label('Twitter/X'),
            ExportColumn::make('linkedin_url')
                ->label('LinkedIn'),
            ExportColumn::make('is_active')
                ->label('Aktif')
                ->formatStateUsing(fn ($state) => $state ? 'Evet' : 'Hayır'),
            ExportColumn::make('created_at')
                ->label('Oluşturulma')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d.m.Y H:i') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your dealer export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
