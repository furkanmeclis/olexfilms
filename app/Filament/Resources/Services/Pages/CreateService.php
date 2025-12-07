<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;

class CreateService extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ServiceResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Müşteri ve Araç Bilgileri')
                ->description('Müşteri seçimi ve araç bilgilerini girin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getCustomerAndCarStep()),

            Step::make('Stok/Malzeme Seçimi')
                ->description('Hizmet için kullanılacak stok ürünlerini seçin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getStockStep()),

            Step::make('Kaplama Alanları')
                ->description('Uygulanacak parçaları seçin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getAppliedPartsStep()),

            Step::make('Durum ve Notlar')
                ->description('Hizmet numarası, durum ve notları belirleyin')
                ->schema(\App\Filament\Resources\Services\Schemas\ServiceForm::getStatusAndNotesStep()),
        ];
    }
}
