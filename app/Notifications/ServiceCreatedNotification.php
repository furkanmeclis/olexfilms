<?php

namespace App\Notifications;

use App\Models\Service;
use App\Notifications\Channels\VatanSmsChannel;
use Illuminate\Notifications\Notification;

class ServiceCreatedNotification extends Notification
{
    protected Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function via($notifiable): array
    {
        return [VatanSmsChannel::class];
    }

    public function toSms($notifiable): string
    {
        $service = $this->service;
        
        // İlişkileri yükle
        $service->load(['customer', 'carBrand', 'carModel', 'dealer']);
        
        $customerName = $service->customer->name ?? 'Değerli Müşterimiz';
        $serviceNo = $service->service_no ?? 'N/A';
        
        // Araç bilgileri
        $carInfo = [];
        if ($service->carBrand) {
            $carInfo[] = $service->carBrand->name;
        }
        if ($service->carModel) {
            $carInfo[] = $service->carModel->name;
        }
        if ($service->plate) {
            $carInfo[] = $service->plate;
        }
        $carInfoStr = !empty($carInfo) ? implode(' ', $carInfo) : '';
        
        // Bayi bilgisi
        $dealerInfo = '';
        if ($service->dealer) {
            $dealerInfo = " {$service->dealer->name} bayisi.";
        }
        
        // SMS mesajını oluştur
        $message = "Sayın {$customerName}, hizmet kaydınız oluşturuldu. Hizmet No: {$serviceNo}.";
        
        if ($carInfoStr) {
            $message .= " Araç: {$carInfoStr}.";
        }
        
        if ($dealerInfo) {
            $message .= $dealerInfo;
        }
        
        return $message;
    }
}

