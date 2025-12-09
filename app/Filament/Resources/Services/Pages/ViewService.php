<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));
        $isDealer = $user && ($user->hasRole(UserRoleEnum::DEALER_OWNER->value) || $user->hasRole(UserRoleEnum::DEALER_STAFF->value));
        $record = $this->record;
        $currentStatus = $record->status;

        $actions = [];

        // Durum Güncelleme Action
        $statusAction = Actions\Action::make('updateStatus')
            ->label('Durum Güncelle')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->form([
                Select::make('status')
                    ->label('Yeni Durum')
                    ->options(function () use ($isAdmin, $currentStatus) {
                        $allStatuses = ServiceStatusEnum::getLabels();
                        
                        if ($isAdmin) {
                            // Admin tüm durumlar arasında geçiş yapabilir
                            return $allStatuses;
                        }

                        // Bayi için sadece geçerli geçişler
                        $allowedTransitions = match ($currentStatus) {
                            ServiceStatusEnum::DRAFT => [ServiceStatusEnum::PENDING->value => $allStatuses[ServiceStatusEnum::PENDING->value]],
                            ServiceStatusEnum::PENDING => [ServiceStatusEnum::PROCESSING->value => $allStatuses[ServiceStatusEnum::PROCESSING->value]],
                            ServiceStatusEnum::PROCESSING => [ServiceStatusEnum::READY->value => $allStatuses[ServiceStatusEnum::READY->value]],
                            ServiceStatusEnum::READY => [ServiceStatusEnum::COMPLETED->value => $allStatuses[ServiceStatusEnum::COMPLETED->value]],
                            default => [],
                        };

                        return $allowedTransitions;
                    })
                    ->required()
                    ->default($currentStatus->value),
            ])
            ->action(function (array $data) use ($record) {
                $newStatus = ServiceStatusEnum::from($data['status']);
                $record->status = $newStatus;

                // Eğer completed ise completed_at set et
                if ($newStatus === ServiceStatusEnum::COMPLETED && !$record->completed_at) {
                    $record->completed_at = now();
                }

                $record->save();

                \Filament\Notifications\Notification::make()
                    ->title('Durum güncellendi')
                    ->success()
                    ->send();
            })
            ->requiresConfirmation();

        // Bayi için sadece geçerli durumlarda göster
        if ($isDealer) {
            $allowedStatuses = [ServiceStatusEnum::DRAFT, ServiceStatusEnum::PENDING, ServiceStatusEnum::PROCESSING, ServiceStatusEnum::READY];
            if (in_array($currentStatus, $allowedStatuses)) {
                $actions[] = $statusAction;
            }
        } else {
            // Admin için her zaman göster
            $actions[] = $statusAction;
        }

        // Admin için hizmet düzenleme
        if ($isAdmin) {
            $actions[] = Actions\Action::make('edit')
                ->label('Düzenle')
                ->icon('heroicon-o-pencil')
                ->url(fn () => ServiceResource::getUrl('edit', ['record' => $record]));
        }

        // Galeri yönetimi
        $actions[] = Actions\Action::make('manageImages')
            ->label('Galeri Yönetimi')
            ->icon('heroicon-o-photo')
            ->color('success')
            ->url(fn () => ServiceResource::getUrl('manage-images', ['record' => $record]));

        return $actions;
    }
}

