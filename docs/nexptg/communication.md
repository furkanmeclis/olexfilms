NexPTG API Entegrasyonu - Sunucu Tarafı Teknik Dokümanı

**Versiyon:** 1.0

Bu doküman, NexPTG mobil uygulamasından sunucunuza gelecek olan senkronizasyon isteklerini karşılamak için hazırlamanız gereken uç noktanın (endpoint) teknik gereksinimlerini içerir.

## 1. Bağlantı ve İstek Yöntemi

Uygulama, verileri sunucunuza aktarmak için standart bir **RESTful Web Service** yapısı kullanır.

* **İstek Yöntemi (Method):** `POST`
* **İçerik Tipi (Content-Type):** `application/json`
* **Örnek Endpoint URL:** `https://sizin-domaininiz.com/api/sync`
* **Veri Akışı:** Uygulama sadece son senkronizasyondan bu yana kaydedilen **yeni** verileri gönderir (Incremental sync).

---

## 2. Kimlik Doğrulama (Header Kısmı)

Kullanıcı "username" ve "password" bilgisini uygulamanın arayüzüne girer. Uygulama bu bilgileri **HTTP Header (Başlık)** kısmında **Basic Authentication** standardı ile gönderir. Body kısmında şifre yer almaz.

Sunucunuzda gelen isteğin Header kısmını şu şekilde analiz etmelisiniz:

### Beklenen Header Yapısı

Gelen `POST` isteğinde şu satır bulunacaktır:

```http
Authorization: Basic <Base64_Encoded_String>
```

### Sunucu Tarafında Yapılması Gerekenler:

1. Header'daki `Authorization` parametresini okuyun.
2. `Basic ` kelimesinden sonraki şifreli metni (string) alın.
3. Bu metni **Base64 Decode** işlemine tabi tutun.
4. Decode işlemi sonucunda `kullaniciadi:sifre` formatında bir metin elde edeceksiniz.
5. Bu kullanıcı adı ve şifreyi veritabanınızdan doğrulayın.

**Örnek Senaryo:**

* Kullanıcı Adı: `admin`
* Şifre: `12345`
* Uygulamanın gönderdiği Header: `Authorization: Basic YWRtaW46MTIzNDU=`
* Sizin yapmanız gereken: `YWRtaW46MTIzNDU=` verisini çözüp `admin:12345` olduğunu doğrulamak.

---

## 3. Gelen Veri İçeriği (Body Kısmı)

Kimlik doğrulama başarılı ise, gelen JSON verisini işlemeniz gerekir. JSON verisi tek bir kök obje (`data`) içinde gelir ve iki ana diziye ayrılır: `history` ve `reports`.

### Kök Yapı (Root)

```json
{
  "data": {
    "history": [ ... ],
    "reports": [ ... ]
  }
}
```

### A. "history" Dizisi (Ölçüm Geçmişi)

Raporlardan bağımsız, hızlı ölçüm geçmişini içerir.

| JSON Anahtarı | Veri Tipi | Açıklama                     |
| :------------- | :-------- | :----------------------------- |
| `id`         | Integer   | Geçmiş kaydının ID'si.     |
| `name`       | String    | Kayıt adı (Örn: "General"). |
| `data`       | Array     | Ölçüm noktaları listesi.   |

**`history[].data` içeriği:**

* `value` (Int): Ölçüm değeri (mikron).
* `interpretation` (Int): Değerlendirme kodu (1: Boyalı, 2: Orijinal vb.).
* `type` (String): Yüzey tipi (`"Fe"`: Demir, `"Al"`: Alüminyum, `"Zn"`: Çinko).
* `date` (Timestamp): Unix zaman damgası.

---

### B. "reports" Dizisi (Detaylı Raporlar)

Bu kısım en karmaşık ve detaylı verilerin olduğu yerdir. Bir rapor objesi şunları içerir:

#### 1. Rapor Üst Verileri (Metadata)

| JSON Anahtarı         | Veri Tipi | Açıklama                 |
| :--------------------- | :-------- | :------------------------- |
| `id`                 | Integer   | Rapor numarası.           |
| `name`               | String    | Rapor adı.                |
| `date`               | Timestamp | Rapor oluşturulma tarihi. |
| `calibrationDate`    | Timestamp | Cihaz kalibrasyon tarihi.  |
| `deviceSerialNumber` | String    | Cihaz seri numarası.      |
| `model`              | String    | Araç Modeli.              |
| `brand`              | String    | Araç Markası.            |
| `typeOfBody`         | String    | Kasa Tipi (Örn: "SEDAN"). |
| `vin`                | String    | Şasi Numarası.           |
| `fuelType`           | String    | Yakıt Tipi.               |
| `year`               | String    | Üretim Yılı.            |
| `unitOfMeasure`      | String    | Birim (Örn: "μm").       |
| `comment`            | String    | Kullanıcı yorumu.        |

#### 2. Kaporta Ölçümleri (`data` ve `dataInside`)

Rapor içinde iki ayrı ölçüm grubu vardır:

* **`data`**: Aracın **dış** kaporta ölçümleri.
* **`dataInside`**: Aracın **iç** direk/kapı içi ölçümleri.

Her iki dizi de `placeId` (konum) mantığıyla çalışır:

* **`placeId`**: Bölgeyi belirtir (`"left"`, `"right"`, `"top"`, `"back"`).
* **`data`**: O bölgedeki parçaların listesidir.
  * **`type`**: Parça Adı (Örn: `"LEFT_FRONT_DOOR"` - Sol Ön Kapı, `"HOOD"` - Kaput).
  * **`values`**: O parça üzerinde alınan ölçümler dizisi.

**Ölçüm Değer Objesi (`values` içindeki her bir eleman):**

* `value`: Ölçüm mikron değeri (String veya Int gelebilir).
* `interpretation`: Sonuç kodu.
* `type`: Metal tipi (`Fe`, `Al`, `Fe+Zn`).
* `timestamp`: Ölçüm saati.
* `position`: Parça üzerindeki ölçüm sırası (1, 2, 3...).

#### 3. Lastik Bilgileri (`tires`)

Rapor objesinin içinde `tires` dizisi bulunur.

| JSON Anahtarı | Açıklama                              |
| :------------- | :-------------------------------------- |
| `width`      | Lastik taban genişliği (Örn: "140"). |
| `profile`    | Yanak profili (Örn: "55").             |
| `diameter`   | Jant çapı (Örn: "18").               |
| `maker`      | Marka (Örn: "Apollo").                 |
| `season`     | Mevsim (Summer/Winter).                 |
| `section`    | Konum (Örn: "Left front" - Sol ön).   |
| `value1`     | Diş derinliği ölçümü 1.           |
| `value2`     | Diş derinliği ölçümü 2.           |

---

## 4. Sunucunun Dönmesi Gereken Cevaplar (Response)

Endpoint'iniz işlemi tamamladıktan sonra aşağıdaki HTTP durum kodlarından uygun olanı dönmelidir:

| HTTP Kodu     | Durum                  | Açıklama                                                             |
| :------------ | :--------------------- | :--------------------------------------------------------------------- |
| **200** | **OK**           | **Başarılı.** Veri alındı ve kaydedildi.                    |
| **403** | **Forbidden**    | **Yetki Hatası.** Header'daki kullanıcı adı/şifre yanlış. |
| **400** | **Bad Request**  | İstek formatı hatalı veya JSON bozuk.                               |
| **500** | **Server Error** | Sunucu tarafında bir kod hatası oluştu.                             |

---

## 5. Tam Örnek JSON Payload (Test Verisi)

Sisteminizi test ederken Postman veya benzeri bir araçla Body kısmına yapıştıracağınız veri şudur:

```json
{
  "data": {
    "history": [
      {
        "id": 1,
        "name": "General",
        "data": [
          { "value": 656, "interpretation": 4, "type": "Zn", "date": 1702629359 }
        ]
      }
    ],
    "reports": [
      {
        "id": 56,
        "name": "Rapor 1",
        "date": 1702629560,
        "calibrationDate": 1701264763,
        "deviceSerialNumber": "18416 Professional",
        "model": "Mondeo",
        "brand": "Ford",
        "typeOfBody": "SEDAN",
        "vin": "ABC123456",
        "data": [
          {
            "placeId": "left",
            "data": [
              {
                "type": "LEFT_FRONT_DOOR",
                "values": [
                  { "value": "110", "interpretation": 1, "type": "Al", "timestamp": 1702629434, "position": 1 }
                ]
              }
            ]
          }
        ],
        "dataInside": [],
        "tires": [
           {
            "width": "205",
            "profile": "55",
            "diameter": "16",
            "maker": "Michelin",
            "season": "Summer",
            "section": "Left front",
            "value1": "5.5",
            "value2": "5.4"
           }
        ]
      }
    ]
  }
}
```
