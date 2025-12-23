<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Cache;

class SmsCacheService
{
    public static function updateSmsInfo(array $response): void
    {
        if (isset($response['quantity'])) {
            Cache::forever('sms:remaining', $response['quantity']);
        }

        if (isset($response['amount'])) {
            Cache::forever('sms:credit', $response['amount']);
        }
    }

    public static function getRemainingSms(): ?int
    {
        return Cache::get('sms:remaining');
    }

    public static function getSmsCredit(): ?float
    {
        return Cache::get('sms:credit');
    }

    public static function getTotalSentToday(): int
    {
        return SmsLog::where('status', 'sent')
            ->whereDate('sent_at', today())
            ->count();
    }

    public static function getTotalSentThisMonth(): int
    {
        return SmsLog::where('status', 'sent')
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->count();
    }

    public static function getSuccessRate(): float
    {
        $total = SmsLog::whereIn('status', ['sent', 'failed'])->count();

        if ($total === 0) {
            return 0.0;
        }

        $success = SmsLog::where('status', 'sent')->count();

        return round(($success / $total) * 100, 2);
    }
}
