<?php

namespace App\Filament\Resources\NexptgApiUsers\Pages;

use App\Filament\Resources\NexptgApiUsers\NexptgApiUserResource;
use App\Filament\Resources\NexptgApiUsers\Widgets\ApiUserLogsTable;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNexptgApiUser extends ViewRecord
{
    protected static string $resource = NexptgApiUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Düzenle'),
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

    protected function getFooterWidgets(): array
    {
        return [
            ApiUserLogsTable::class,
        ];
    }
}
