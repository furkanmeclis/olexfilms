<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\VatanSmsService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index($hash)
    {
        $customerId = Crypt::decrypt($hash);
        $customer = Customer::find($customerId);
        request()->session()->put('manifest', $customerId);
        if (!$customer) {
            return redirect()->route('home');
        }
        $services = $customer->getServices();
        $products = [];
        foreach ($services as $service) {
            $products = array_merge($products, collect($service["products"])->map(function ($product) {
                return $product;
            })->toArray());
        }
        return Inertia::render('Customer/NewDesign', [
            'customerB' => $customer,
            'hash' => Crypt::encrypt($customerId),
            'services' => $services,
            "products" => $products
        ]);
    }

    public function index2($customerId): \Inertia\Response
    {
        $customer = Customer::find($customerId);
        request()->session()->put('manifest', $customerId);
        return Inertia::render('Customer/Index', [
            'customerB' => $customer,
            'hash' => Crypt::encrypt($customerId),
        ]);
    }

    public function update($hash)
    {
        try {
            $customerId = Crypt::decrypt($hash);
            $customer = Customer::find($customerId);
            if ($customer) {
                $action = request()->get('action');
                if ($action == "settings") {
                    $default = [
                        "sms" => false,
                        "email" => false,
                    ];
                    $notificationSettings = json_decode(request()->get('settings'), true);
                    if ($notificationSettings) {
                        foreach ($notificationSettings as $key) {
                            if (array_key_exists($key, $default)) {
                                $default[$key] = true;
                            }
                        }
                    }
                    $customer->notification_settings = $default;
                    if ($customer->save()) {
                        return response()->json(['message' => 'Müşteri Tercihleri Güncellendi', 'status' => true, 'customer' => $customer]);
                    } else {
                        return response()->json(['message' => 'Müşteri Tercihleri Güncellenemedi', 'status' => false]);
                    }
                } else {
                    return response()->json(['message' => 'Geçersiz İşlem', 'status' => false]);
                }
            } else {
                return response()->json(['message' => 'Müşteri Bulunamadı Lütfen URL İle Oynamayınız.', 'status' => false]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Müşteri Bulunamadı Lütfen URL İle Oynamayınız.', 'status' => false, 'error' => $e->getMessage()]);
        }
    }

    public function customerOtpLogin(): \Illuminate\Http\JsonResponse
    {
        $normalizedPhone = VatanSmsService::formatPhoneNumber(request()->get('phone'));
        if ($normalizedPhone != null) {
            $customers = Customer::where('id', ">", 0)->get(['id', 'phone', 'name']);
            $customer = null;
            foreach ($customers as $c) {
                if (VatanSmsService::formatPhoneNumber($c->phone) == $normalizedPhone) {
                    $customer = $c;
                    break;
                }
            }
            if ($customer == null) {
                return response()->json(['message' => 'Müşteri Bulunamadı', 'status' => false]);
            }
            $otp = rand(100000, 999999);
            $sms = "Merhaba " . $customer->name . ", Tek Kullanımlık Şifreniz: " . $otp . " Geçerlilik Süresi 5 Dakikadır.";
            VatanSmsService::sendSingleSms($customer->phone, $sms);
            $otpCacheKey = "otp_" . $customer->id;
            cache([$otpCacheKey => $otp], now()->addMinutes(5));
            return response()->json(['message' => 'Tek Kullanımlık Şifre Gönderildi', 'status' => true,
                'customer_id' => $customer->id
            ]);
        } else {
            return response()->json(['message' => 'Sadece Bireysel Telefon Numaraları Desteklenmektedir.', 'status' => false]);
        }
    }

    public function customerOtpVerify(): \Illuminate\Http\JsonResponse
    {
        $customerId = request()->get('customer_id');
        $otp = request()->get('otp');
        $otpCacheKey = "otp_" . $customerId;
        $cachedOtp = cache($otpCacheKey);
        if ($cachedOtp == $otp) {
            cache()->forget($otpCacheKey);
            return response()->json([
                'message' => 'Giriş Başarılı',
                'status' => true,
                'hash' => Crypt::encrypt($customerId)
            ]);
        } else {
            return response()->json(['message' => 'Tek Kullanımlık Şifre Hatalı', 'status' => false,
                "request" => request()->all(),
                "cache" => cache()->get($otpCacheKey)
            ]);
        }
    }
}

