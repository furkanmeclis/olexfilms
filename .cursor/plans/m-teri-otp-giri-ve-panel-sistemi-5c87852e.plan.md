---
name: Müşteri OTP Giriş ve Panel Sistemi
overview: ""
todos:
  - id: 69f25ae1-cd65-4821-9855-4d04d187efa3
    content: CustomerController oluştur ve OTP login/verify/panel metodlarını ekle
    status: pending
  - id: af6106da-6839-4384-b1fa-ae737587296a
    content: Web route'larını ekle (otp-login, otp-verify, customer/{hash})
    status: pending
  - id: a012c5fe-cf40-44ed-b124-d42020e78d54
    content: CustomerOtpNotification oluştur ve VatanSmsChannel ile entegre et
    status: pending
  - id: 1f0030a6-6e46-46af-94b8-54caad6e43ca
    content: CustomerLogin Livewire component'ini oluştur (telefon ve OTP input'ları)
    status: pending
  - id: 5b298f63-bc0d-47e4-b987-2752fd11728b
    content: CustomerPanel Livewire component'ini oluştur (hizmetler ve garanti listesi)
    status: pending
  - id: 1a5ee6aa-45c3-4ac4-b796-f3d3aee72f51
    content: customer/login.blade.php view'ını oluştur (Tailwind tasarım)
    status: pending
  - id: d3f77d09-220b-4515-8556-fc5dc63bc85a
    content: customer/panel.blade.php view'ını oluştur (Tailwind tasarım, örnek kod benzeri)
    status: pending
  - id: 1dc606c0-3c70-42a8-8239-13e14be26eee
    content: Customer model'ine getServicesWithCars() ve getWarrantiesWithProducts() metodlarını ekle
    status: pending
  - id: 3a097cda-d895-48d5-a16c-f20a68d3fd33
    content: Hash yönetimini cache ile 5 dakika expire olacak şekilde implement et
    status: pending
---

# Müşteri OTP Giriş ve Panel Sistemi

## Genel Bakış

Müşteriler telefon numarası ile giriş yapıp 6 haneli OTP kodu alacak, doğrulama sonrası hash'lenmiş customer ID ile panellerine erişecekler. Hash 5 dakika geçerli olacak ve cache'de tutulacak.

## Yapılacaklar

### 1. Controller ve Route'lar

- `app/Http/Controllers/CustomerController.php` oluştur
- `customerOtpLogin()`: Telefon numarasını al, müşteriyi bul, OTP oluştur ve SMS gönder
- `customerOtpVerify()`: OTP'yi doğrula, hash oluştur ve cache'de 5 dakika tut
- `customerPanel($hash)`: Hash'i cache'den kontrol et, müşteriyi bul ve paneli göster
- `routes/web.php` güncelle
- `POST /customer/otp-login` route'u ekle
- `POST /customer/otp-verify` route'u ekle
- `GET /customer/{hash}` route'u ekle

### 2. OTP Notification

- `app/Notifications/CustomerOtpNotification.php` oluştur
- `toSms()` metodu ile SMS mesajı oluştur: "Merhaba {name}, Tek Kullanimlik Sifreniz: {otp} Gecerlilik Suresi 5 Dakikadir. {sender}"
- VatanSmsChannel kullanarak SMS gönder

### 3. Livewire Component'leri

- `app/Livewire/CustomerLogin.php` oluştur
- Telefon numarası input'u (PhoneInput kullan)
- OTP input'u (6 haneli)
- OTP gönderme ve doğrulama işlemleri
- Hash alındıktan sonra redirect
- `app/Livewire/CustomerPanel.php` oluştur
- Müşteri bilgilerini göster
- Hizmetleri listele (araç bilgileri, tarih)
- Garanti sürelerini göster (ürün adı, kod, tarihler, progress bar)

### 4. Blade View'lar

- `resources/views/customer/login.blade.php` oluştur
- Welcome sayfası benzeri tasarım (Tailwind)
- CustomerLogin Livewire component'ini içer
- `resources/views/customer/panel.blade.php` oluştur
- Örnek React kodundaki tasarıma benzer (Tailwind)
- CustomerPanel Livewire component'ini içer
- Gradient arka plan, kartlar, animasyonlar

### 5. Hash Yönetimi

- OTP doğrulandıktan sonra hash oluştur: `Crypt::encrypt($customerId)`
- Hash'i cache'de 5 dakika tut: `Cache::put("customer_hash_{$hash}", $customerId, now()->addMinutes(5))`
- Panel erişiminde cache'den kontrol et

### 6. Müşteri Panel İçeriği

- Müşteri adı (üst kısımda büyük başlık)
- Hizmetler listesi:
- Araç marka/model/yıl/plaka
- Hizmet tarihi
- Marka logosu
- Garanti süreleri:
- Ürün adı ve kodu
- Başlangıç/bitiş tarihi
- Progress bar (garanti süresine göre)
- Plaka bilgisi

### 7. Helper Metodlar

- `Customer` model'ine `getServicesWithCars()` metodu ekle (hizmetleri araç bilgileriyle birlikte getir)
- `Customer` model'ine `getWarrantiesWithProducts()` metodu ekle (garanti bilgilerini ürün bilgileriyle birlikte getir)

## Teknik Detaylar

- **OTP Süresi**: 5 dakika (cache'de tutulacak)
- **Hash Süresi**: 5 dakika (cache'de tutulacak)
- **SMS Formatı**: "Merhaba {name}, Tek Kullanimlik Sifreniz: {otp} Gecerlilik Suresi 5 Dakikadir. {sender}"
- **Telefon Input**: `Ysfkaya\FilamentPhoneInput\Forms\PhoneInput` kullanılacak
- **Tasarım**: Tailwind CSS, gradient arka planlar, kartlar, animasyonlar
- **Bildirim Sistemi**: Olmayacak (push notification yok)