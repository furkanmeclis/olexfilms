<?php

namespace App\Jobs;

use App\Enums\UserRoleEnum;
use App\Models\BulkSms;
use App\Models\Customer;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendBulkSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $bulkSmsId
    ) {}

    public function handle(): void
    {
        $bulkSms = BulkSms::find($this->bulkSmsId);

        if (! $bulkSms || $bulkSms->status !== 'draft') {
            return;
        }

        // Status'u sending olarak güncelle
        $bulkSms->update([
            'status' => 'sending',
            'sent_at' => now(),
        ]);

        // Alıcıları belirle
        $recipients = $this->getRecipients($bulkSms);

        // Total recipients'ı güncelle (tümüne gönder durumunda)
        if ($bulkSms->target_type === 'all' && $bulkSms->total_recipients === 0) {
            $bulkSms->update(['total_recipients' => count($recipients)]);
        }

        // Her alıcı için SMS log kaydı oluştur ve job dispatch et
        foreach ($recipients as $recipient) {
            $phone = $recipient->phone ?? null;
            if (! $phone) {
                $bulkSms->increment('failed_count');

                continue;
            }

            // SMS log kaydı oluştur
            $smsLog = SmsLog::create([
                'phone' => $phone,
                'message' => $bulkSms->message,
                'sender' => $bulkSms->sender,
                'message_type' => $bulkSms->message_type,
                'message_content_type' => $bulkSms->message_content_type,
                'status' => 'pending',
                'notifiable_type' => get_class($recipient),
                'notifiable_id' => $recipient->id,
                'bulk_sms_id' => $bulkSms->id,
                'sent_by' => $bulkSms->created_by,
            ]);

            // SMS gönder job'ını dispatch et
            SendSmsJob::dispatch(
                phone: $phone,
                message: $bulkSms->message,
                sender: $bulkSms->sender,
                messageType: $bulkSms->message_type,
                messageContentType: $bulkSms->message_content_type,
                smsLogId: $smsLog->id,
                bulkSmsId: $bulkSms->id
            )->onQueue('default');
        }

        // Job tamamlandığında status'u güncellemek için bir callback job dispatch et
        // Bu job tüm SMS'ler gönderildikten sonra çalışacak
        // Not: Gerçek uygulamada bu daha sofistike bir şekilde yapılabilir (event/listener veya job chaining)
    }

    protected function getRecipients(BulkSms $bulkSms): array
    {
        return match ($bulkSms->target_type) {
            'all' => $this->getAllRecipients(),
            'customers' => $this->getAllCustomers(),
            'dealers' => $this->getAllDealers(),
            'both' => array_merge($this->getAllCustomers(), $this->getAllDealers()),
            default => [],
        };
    }

    protected function getAllRecipients(): array
    {
        $recipients = [];

        // Tüm müşteriler
        $customers = Customer::whereNotNull('phone')->get();
        $recipients = array_merge($recipients, $customers->all());

        // Tüm bayiler (dealer_owner ve dealer_staff)
        $dealers = User::query()
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ]);
            })
            ->get();

        $recipients = array_merge($recipients, $dealers->all());

        return $recipients;
    }

    protected function getAllCustomers(): array
    {
        return Customer::whereNotNull('phone')->get()->all();
    }

    protected function getAllDealers(): array
    {
        return User::query()
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ]);
            })
            ->get()
            ->all();
    }
}
