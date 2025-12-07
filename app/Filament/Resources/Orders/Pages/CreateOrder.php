<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Bayi için dealer_id otomatik set edilir
        if ($user && $user->dealer_id) {
            $data['dealer_id'] = $user->dealer_id;
        }

        // created_by otomatik set edilir
        $data['created_by'] = $user->id;
        $data['status'] = 'pending';

        return $data;
    }
}
