<?php

namespace App\Jobs;

use App\Models\BulkSms;
use App\Models\SmsLog;
use App\Services\SmsCacheService;
use App\Services\VatanSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $phone,
        public string $message,
        public ?string $sender = null,
        public string $messageType = 'normal',
        public string $messageContentType = 'bilgi',
        public ?int $smsLogId = null,
        public ?int $bulkSmsId = null,
    ) {
    }

    public function handle(): void
    {
        $sender = $this->sender ?? app(\App\Settings\VatanSmsSettings::class)->sender ?? '';

        // SMS gönder
        $result = VatanSmsService::sendSms(
            [$this->phone],
            $this->message,
            $this->messageType,
            $this->messageContentType
        );

        // Response'u parse et
        // API yanıtı direkt olarak gelebilir: {"id":..., "quantity":..., "amount":...}
        // Veya nested olabilir: {"response": {"id":..., "quantity":..., "amount":...}}
        $response = $result['response'] ?? [];
        $responseData = null;
        
        if (isset($response['response']) && is_array($response['response'])) {
            // Nested response yapısı
            $responseData = $response['response'];
        } elseif (isset($response['id']) || isset($response['quantity']) || isset($response['amount'])) {
            // Direkt response yapısı
            $responseData = $response;
        } else {
            $responseData = [];
        }

        // SMS log kaydını güncelle veya oluştur
        if ($this->smsLogId) {
            $smsLog = SmsLog::find($this->smsLogId);
            if ($smsLog) {
                $smsLog->update([
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'response_id' => $responseData['id'] ?? null,
                    'quantity' => $responseData['quantity'] ?? null,
                    'amount' => $responseData['amount'] ?? null,
                    'number_count' => $responseData['numberCount'] ?? null,
                    'description' => $responseData['description'] ?? null,
                    'response_data' => $response,
                    'invalid_phones' => $result['invalidPhones'] ?? [],
                    'sent_at' => now(),
                ]);
            }
        } else {
            SmsLog::create([
                'phone' => $this->phone,
                'message' => $this->message,
                'sender' => $sender,
                'message_type' => $this->messageType,
                'message_content_type' => $this->messageContentType,
                'status' => $result['success'] ? 'sent' : 'failed',
                'response_id' => $responseData['id'] ?? null,
                'quantity' => $responseData['quantity'] ?? null,
                'amount' => $responseData['amount'] ?? null,
                'number_count' => $responseData['numberCount'] ?? null,
                'description' => $responseData['description'] ?? null,
                'response_data' => $response,
                'invalid_phones' => $result['invalidPhones'] ?? [],
                'bulk_sms_id' => $this->bulkSmsId,
                'sent_at' => now(),
            ]);
        }

        // Cache'i güncelle
        if ($result['success'] && !empty($responseData)) {
            SmsCacheService::updateSmsInfo($responseData);
        }

        // BulkSms kaydını güncelle
        if ($this->bulkSmsId) {
            $bulkSms = BulkSms::find($this->bulkSmsId);
            if ($bulkSms) {
                DB::transaction(function () use ($bulkSms, $result) {
                    if ($result['success']) {
                        $bulkSms->increment('sent_count');
                    } else {
                        $bulkSms->increment('failed_count');
                    }

                    // Tüm SMS'ler gönderildiyse status'u güncelle
                    $totalProcessed = $bulkSms->sent_count + $bulkSms->failed_count;
                    if ($totalProcessed >= $bulkSms->total_recipients) {
                        $bulkSms->update([
                            'status' => $bulkSms->failed_count > 0 ? 'failed' : 'completed',
                            'completed_at' => now(),
                        ]);
                    }
                });
            }
        }
    }
}
