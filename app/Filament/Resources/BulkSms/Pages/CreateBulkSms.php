<?php

namespace App\Filament\Resources\BulkSms\Pages;

use App\Filament\Resources\BulkSms\BulkSmsResource;
use App\Jobs\SendBulkSmsJob;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBulkSms extends CreateRecord
{
    protected static string $resource = BulkSmsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        // Sender'ı ayarlardan al (disabled alan form'da gönderilmez)
        $data['sender'] = app(\App\Settings\VatanSmsSettings::class)->sender;

        // Yeni yapıya göre target_type ve total_recipients hesapla
        $sendToAll = $data['send_to_all'] ?? false;
        $includeCustomers = $data['include_customers'] ?? false;
        $includeDealers = $data['include_dealers'] ?? false;

        // Validation: En az bir seçenek seçilmeli
        if (! $sendToAll && ! $includeCustomers && ! $includeDealers) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['Hedef seçimi' => ['En az bir hedef seçmelisiniz: Tümüne gönder, Müşteriler veya Bayiler']]
            );
        }

        if ($sendToAll) {
            $data['target_type'] = 'all';
        } elseif ($includeCustomers && $includeDealers) {
            $data['target_type'] = 'both';
        } elseif ($includeCustomers) {
            $data['target_type'] = 'customers';
        } elseif ($includeDealers) {
            $data['target_type'] = 'dealers';
        }

        // Tüm durumlarda total_recipients job'da hesaplanacak (tüm kayıtlar getirilecek)
        $data['total_recipients'] = 0;
        $data['target_ids'] = null; // Artık kullanılmıyor

        // Form'dan gelen toggle değerlerini kaldır
        unset($data['send_to_all'], $data['include_customers'], $data['include_dealers']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createAndSend')
                ->label('Kaydet ve Gönder')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Toplu SMS Gönder')
                ->modalDescription('Bu toplu SMS kaydedilecek ve hemen gönderilecektir. Devam etmek istiyor musunuz?')
                ->action(function () {
                    $this->create();
                    $this->sendBulkSms();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        // Normal create işleminden sonra gönderme yapılmaz
        // Sadece "Kaydet ve Gönder" butonuna tıklandığında gönderilir
    }

    protected function sendBulkSms(): void
    {
        if ($this->record) {
            SendBulkSmsJob::dispatch($this->record->id);
        }
    }
}
