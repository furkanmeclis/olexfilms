<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Crypt;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewCustomerPage')
                ->label('Müşteri Sayfasını Aç')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('customer.notify', Crypt::encrypt($this->record->id)))
                ->openUrlInNewTab()
                ->color('success'),
        ];
    }
}
