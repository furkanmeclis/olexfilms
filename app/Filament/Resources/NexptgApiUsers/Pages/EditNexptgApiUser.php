<?php

namespace App\Filament\Resources\NexptgApiUsers\Pages;

use App\Filament\Resources\NexptgApiUsers\NexptgApiUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNexptgApiUser extends EditRecord
{
    protected static string $resource = NexptgApiUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('API Kullanıcısını Sil')
                ->modalDescription(function ($record) {
                    $reportsCount = $record->reports()->count();
                    
                    if ($reportsCount > 0) {
                        return "Bu API kullanıcısının {$reportsCount} adet raporu bulunmaktadır. Silme işlemi devam ederse bu raporlar silinmeyecektir, ancak API kullanıcı bağlantısı kaldırılacaktır. Devam etmek istediğinize emin misiniz?";
                    }
                    
                    return 'Bu API kullanıcısını silmek istediğinize emin misiniz?';
                })
                ->modalSubmitActionLabel('Evet, Sil')
                ->modalCancelActionLabel('İptal'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If password is empty, remove it from data so it won't be updated
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
