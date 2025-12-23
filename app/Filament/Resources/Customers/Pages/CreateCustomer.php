<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // created_by otomatik set edilir
        $data['created_by'] = $user->id;

        // Bayi için dealer_id otomatik set edilir (eğer form'da yoksa)
        if ($user && $user->dealer_id && ! isset($data['dealer_id'])) {
            $data['dealer_id'] = $user->dealer_id;
        }

        return $data;
    }
}
