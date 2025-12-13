<?php

namespace App\Filament\Resources\NexptgApiUsers\Pages;

use App\Filament\Resources\NexptgApiUsers\NexptgApiUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateNexptgApiUser extends CreateRecord
{
    protected static string $resource = NexptgApiUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        // Check if user already has an API user
        if (isset($data['user_id'])) {
            $existingApiUser = \App\Models\NexptgApiUser::where('user_id', $data['user_id'])->first();
            if ($existingApiUser) {
                throw ValidationException::withMessages([
                    'user_id' => 'Bu kullanıcının zaten bir API kullanıcısı var.',
                ]);
            }
        }

        return $data;
    }
}
