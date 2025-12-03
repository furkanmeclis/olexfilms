<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // If dealer owner, set dealer_id automatically
        if ($user && $user->hasRole(UserRoleEnum::DEALER_OWNER->value)) {
            $data['dealer_id'] = $user->dealer_id;
        }

        // Get role from form data and remove it (not a model field)
        $role = $data['role'] ?? null;
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $role = $this->form->getState()['role'] ?? null;
        if ($role) {
            $this->record->assignRole($role);
        }
    }
}
