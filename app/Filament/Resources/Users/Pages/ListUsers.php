<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // If dealer owner, only show users from their dealer
        if ($user && $user->hasRole(UserRoleEnum::DEALER_OWNER->value)) {
            return parent::getEloquentQuery()
                ->where('dealer_id', $user->dealer_id);
        }

        // Admin and center staff see all users
        return parent::getEloquentQuery();
    }
}
