<?php

namespace App\Services;

use App\Settings\VatanSmsSettings;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VatanSmsService
{
    protected static ?VatanSmsSettings $settings = null;

    public static function init(): void
    {
        if (self::$settings === null) {
            self::$settings = app(VatanSmsSettings::class);
        }
    }

    public static function formatPhoneNumber(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = substr($phone, -10);
        return (substr($phone, 0, 1) === '5') ? $phone : null;
    }

    public static function filterAndFormatPhones(array $phones): array
    {
        $formattedPhones = [];
        $invalidPhones = [];

        foreach ($phones as $phone) {
            $formattedPhone = self::formatPhoneNumber($phone);
            if ($formattedPhone) {
                $formattedPhones[] = $formattedPhone;
            } else {
                $invalidPhones[] = $phone;
            }
        }

        return [$formattedPhones, $invalidPhones];
    }

    public static function sendSms(array $phones, string $message, string $messageType = 'turkce', string $messageContentType = 'bilgi')
    {
        self::init();

        if (!self::$settings->installed) {
            return [
                'success' => false,
                'message' => 'SMS servisi yapılandırılmamış.',
                'invalidPhones' => [],
            ];
        }

        list($formattedPhones, $invalidPhones) = self::filterAndFormatPhones($phones);

        if (empty($formattedPhones)) {
            return [
                'success' => false,
                'message' => 'Geçerli telefon numarası bulunamadı.',
                'invalidPhones' => $invalidPhones,
            ];
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
            'sender' => self::$settings->sender,
            'message_type' => $messageType,
            'message' => $message,
            'message_content_type' => $messageContentType,
            'phones' => $formattedPhones,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$settings->endpoint . '/1toN', $params);

            $responseJson = $response->json();
            $isSuccessful = $response->successful();

            $transaction = [
                'success' => $isSuccessful,
                'response' => $responseJson,
                'invalidPhones' => $invalidPhones,
            ];

            // Cache'i güncelle
            // API yanıtı direkt olarak geliyor: {"id":..., "quantity":..., "amount":...}
            // Veya nested olabilir: {"response": {"quantity":..., "amount":...}}
            $cacheData = null;
            if (isset($responseJson['response']) && is_array($responseJson['response'])) {
                // Nested response yapısı
                $cacheData = $responseJson['response'];
            } elseif (isset($responseJson['quantity']) || isset($responseJson['amount'])) {
                // Direkt response yapısı
                $cacheData = $responseJson;
            }
            
            if ($isSuccessful && $cacheData !== null) {
                self::updateCacheFromResponse($cacheData);
            }

            return $transaction;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMS gönderilirken hata oluştu: ' . $e->getMessage(),
                'response' => null,
                'invalidPhones' => $invalidPhones,
            ];
        }
    }

    public static function sendSingleSms(string $phone, string $message, string $messageType = 'normal', string $messageContentType = 'bilgi')
    {
        return self::sendSms([$phone], $message, $messageType, $messageContentType);
    }

    public static function getSenderNames()
    {
        self::init();

        if (!self::$settings->installed) {
            return null;
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(self::$settings->endpoint . '/senders', $params);

        return $response->json();
    }

    public static function getUserInfo()
    {
        self::init();

        if (!self::$settings->installed) {
            return null;
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$settings->endpoint . '/user/information', $params);

            $userInfo = $response->json();
            $isSuccessful = $response->successful();

            // Cache'i güncelle
            // API yanıtı direkt olarak gelebilir: {"quantity":..., "amount":...}
            // Veya nested olabilir: {"response": {"quantity":..., "amount":...}}
            $cacheData = null;
            if (isset($userInfo['response']) && is_array($userInfo['response'])) {
                // Nested response yapısı
                $cacheData = $userInfo['response'];
            } elseif (isset($userInfo['quantity']) || isset($userInfo['amount'])) {
                // Direkt response yapısı
                $cacheData = $userInfo;
            }
            
            if ($isSuccessful && $cacheData !== null) {
                self::updateCacheFromUserInfo($cacheData);
            }

            return $userInfo;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getReportDetail(int $reportId, int $page = 1, int $pageSize = 20)
    {
        self::init();

        if (!self::$settings->installed) {
            return null;
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
            'report_id' => $reportId,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$settings->endpoint . "/report/detail?page={$page}&pageSize={$pageSize}", $params);

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getReportBetweenDates(string $startDate, string $endDate)
    {
        self::init();

        if (!self::$settings->installed) {
            return null;
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$settings->endpoint . '/report/between', $params);

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getSingleReport(int $reportId)
    {
        self::init();

        if (!self::$settings->installed) {
            return null;
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
            'report_id' => $reportId,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$settings->endpoint . '/report/single', $params);

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function cancelFutureSms(int $reportId)
    {
        self::init();

        if (!self::$settings->installed) {
            return null;
        }

        $params = [
            'api_id' => self::$settings->api_id,
            'api_key' => self::$settings->api_key,
            'id' => $reportId,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::$settings->endpoint . '/cancel/future-sms', $params);

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function shortenUrl($url): bool|string
    {
        $requestUrl = 'https://is.gd/create.php?format=simple&url=' . urlencode($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) || $httpCode != 200) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        return $response;
    }

    protected static function updateCacheFromResponse(array $response): void
    {
        if (isset($response['quantity'])) {
            Cache::forever('sms:remaining', $response['quantity']);
        }

        if (isset($response['amount'])) {
            Cache::forever('sms:credit', $response['amount']);
        }
    }

    protected static function updateCacheFromUserInfo(array $userInfo): void
    {
        if (isset($userInfo['quantity'])) {
            Cache::forever('sms:remaining', $userInfo['quantity']);
        }

        if (isset($userInfo['amount'])) {
            Cache::forever('sms:credit', $userInfo['amount']);
        }
    }
}

