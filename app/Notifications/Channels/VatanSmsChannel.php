<?php

namespace App\Notifications\Channels;

use App\Models\SmsLog;
use App\Services\SmsCacheService;
use App\Services\VatanSmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class VatanSmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        $phone = $notifiable->phone ?? null;

        if (! $phone) {
            return;
        }

        // SMS log kaydı oluştur (pending)
        $smsLog = SmsLog::create([
            'phone' => $phone,
            'message' => $message,
            'sender' => app(\App\Settings\VatanSmsSettings::class)->sender ?? '',
            'message_type' => 'normal',
            'message_content_type' => 'bilgi',
            'status' => 'pending',
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'sent_by' => Auth::id(),
        ]);

        // SMS gönder
        $result = VatanSmsService::sendSingleSms($phone, $message);

        // Response'u parse et ve log'u güncelle
        $response = $result['response'] ?? [];
        $responseData = $response['response'] ?? [];

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

        // Cache'i güncelle
        if ($result['success'] && ! empty($responseData)) {
            SmsCacheService::updateSmsInfo($responseData);
        }
    }
}
