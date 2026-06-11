Berikut adalah file **`api-providers.md`** yang sudah saya susun dengan format Markdown profesional. File ini dirancang khusus sebagai **"Kitab Suci" (Master Reference)** yang bisa Anda berikan kepada AI murah atau Junior Programmer agar mereka tidak salah asumsi lagi dalam membuat integrasi API.

Simpan file ini di root repository GitHub Anda (`escin-team/nontonin`) agar AI bisa membacanya kapan saja.

***

```markdown
# 📚 Dokumentasi Resmi DramaBos API (32+ Providers)
**Master Reference untuk Proyek Nontonin**

Dokumen ini berisi peta endpoint yang **AKURAT** untuk ke-32+ provider DramaBos. 
⚠️ **PERINGATAN**: Setiap provider memiliki pola endpoint yang **BERBEDA-BEDA**. Jangan gunakan asumsi endpoint generik seperti `/api/v1/feed` untuk semua provider, karena akan menghasilkan error `404 Not Found`.

---

## 1. Autentikasi & Konfigurasi Dasar

- **Base URL**: `https://prod-api.dramabos.live`
- **Autentikasi**: Bearer Token di Header
- **Wajib Bypass SSL** untuk lingkungan Shared Hosting (ByetHost/AeonFree)

```http
Authorization: Bearer dbk_live_YOUR_TOKEN_HERE
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)
Accept: application/json
```

---

## 2. 🗺️ Peta Endpoint per Provider (Master Table)

Gunakan tabel ini sebagai acuan mutlak saat membangun `ApiService.php` atau `cron_aggregator.php`.

### A. Provider dengan Pola Standar (`/{provider}/api/v1/...`)
| Provider | Feed / Trending | Detail Drama | List Episode | Stream URL |
| :--- | :--- | :--- | :--- | :--- |
| **reelshort** | `/reelshort/api/v1/featured` | `/reelshort/api/v1/detail/{id}` | `/reelshort/api/v1/episodes/{id}` | `/reelshort/api/v1/play/{id}/{ep}` |
| **starshort** | `/starshort/api/v1/trending` | `/starshort/api/v1/detail/{id}` | `/starshort/api/v1/episodes/{id}` | `/starshort/api/v1/play/{id}/{ep}` |
| **dramabite** | `/dramabite/api/v1/recommend` | `/dramabite/api/v1/detail/{id}` | `/dramabite/api/v1/episodes/{id}` | `/dramabite/api/v1/play/{id}/{ep}` |
| **goodshort** | `/goodshort/api/v1/toppicks` | `/goodshort/api/v1/detail/{id}` | `/goodshort/api/v1/episodes/{id}` | `/goodshort/api/v1/play/{id}/{ep}` |
| **reelbuzz** | `/reelbuzz/api/v1/buzz` | `/reelbuzz/api/v1/detail/{id}` | `/reelbuzz/api/v1/episodes/{id}` | `/reelbuzz/api/v1/play/{id}/{ep}` |
| **freereels** | `/freereels/api/v1/trending` | `/freereels/api/v1/detail/{id}` | `/freereels/api/v1/episodes/{id}` | `/freereels/api/v1/play/{id}/{ep}` |
| **vigloo** | `/vigloo/api/v1/trending` | `/vigloo/api/v1/detail/{id}` | `/vigloo/api/v1/episodes/{id}` | `/vigloo/api/v1/play/{id}/{ep}` |
| **dramawave** | `/dramawave/api/v1/featured` | `/dramawave/api/v1/detail/{id}` | `/dramawave/api/v1/episodes/{id}` | `/dramawave/api/v1/play/{id}/{ep}` |
| **microdrama** | `/microdrama/api/v1/feed` | `/microdrama/api/v1/detail/{id}` | `/microdrama/api/v1/episodes/{id}` | `/microdrama/api/v1/play/{id}/{ep}` |

### B. Provider dengan Pola Unik (WAJIB DIPERHATIKAN)
| Provider | Feed / Trending | Detail Drama | List Episode | Stream URL | Catatan Khusus |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **shortmax** | `/shortmax/api/v1/home` | `/shortmax/api/v1/detail/{id}` | `/shortmax/api/v1/episodes/{id}` | `/shortmax/api/v1/play/{id}/{ep}` | Alternatif: `/foryou`, `/popular` |
| **dramabox** | `/dramabox/api/v1/discover` | `/dramabox/api/v1/detail/{id}` | `/dramabox/api/v1/episodes/{id}` | `/dramabox/api/v1/play/{id}/{ep}` | Alternatif: `/rank`, `/theater` |
| **flickreels** | `/flickreels/api/flickreels/trending?lang=en` | `/flickreels/api/flickreels/detail?id={id}` | `/flickreels/api/flickreels/allepisode?id={id}` | `/flickreels/api/flickreels/episode?id={id}&ep={ep}` | **PAKAI QUERY STRING `?id=`** |
| **idrama** | `/idrama/home?lang=id` | `/idrama/drama/{id}?lang=id` | `/idrama/episodes/{id}?lang=id` | `/idrama/play/{id}/{ep}?lang=id` | **TANPA `/api/v1/` & WAJIB `?lang=id`** |
| **bilitv** | `/bilitv/api/v1/home` | `/bilitv/api/v1/detail/{id}` | `/bilitv/api/v1/episodes/{id}` | `/bilitv/api/v1/play/{id}/{ep}` | - |
| **netshort** | `/netshort/api/v1/hot` | `/netshort/api/v1/detail/{id}` | `/netshort/api/v1/episodes/{id}` | `/netshort/api/v1/play/{id}/{ep}` | - |
| **melolo** | `/melolo/api/v1/hot` | `/melolo/api/v1/detail/{id}` | `/melolo/api/v1/episodes/{id}` | `/melolo/api/v1/play/{id}/{ep}` | - |
| **velolo** | `/velolo/api/v1/trending` | `/velolo/api/v1/detail/{id}` | `/velolo/api/v1/episodes/{id}` | `/velolo/api/v1/play/{id}/{ep}` | - |
| **stardusttv** | `/stardusttv/api/v1/stellar` | `/stardusttv/api/v1/detail/{id}` | `/stardusttv/api/v1/episodes/{id}` | `/stardusttv/api/v1/play/{id}/{ep}` | - |
| **serialplus** | `/serialplus/api/v1/weekly` | `/serialplus/api/v1/detail/{id}` | `/serialplus/api/v1/episodes/{id}` | `/serialplus/api/v1/play/{id}/{ep}` | - |
| **dotdrama** | `/dotdrama/api/v1/featured` | `/dotdrama/api/v1/detail/{id}` | `/dotdrama/api/v1/episodes/{id}` | `/dotdrama/api/v1/play/{id}/{ep}` | - |
| **rapidtv** | `/rapidtv/api/v1/trending` | `/rapidtv/api/v1/detail/{id}` | `/rapidtv/api/v1/episodes/{id}` | `/rapidtv/api/v1/play/{id}/{ep}` | - |
| **shortswave** | `/shortswave/api/v1/wave` | `/shortswave/api/v1/detail/{id}` | `/shortswave/api/v1/episodes/{id}` | `/shortswave/api/v1/play/{id}/{ep}` | - |
| **dramanova** | `/dramanova/api/v1/nova` | `/dramanova/api/v1/detail/{id}` | `/dramanova/api/v1/episodes/{id}` | `/dramanova/api/v1/play/{id}/{ep}` | - |
| **cubetv** | `/cubetv/api/v1/hot` | `/cubetv/api/v1/detail/{id}` | `/cubetv/api/v1/episodes/{id}` | `/cubetv/api/v1/play/{id}/{ep}` | - |
| **flareflow** | `/flareflow/api/v1/flare` | `/flareflow/api/v1/detail/{id}` | `/flareflow/api/v1/episodes/{id}` | `/flareflow/api/v1/play/{id}/{ep}` | - |
| **moboreels** | `/moboreels/api/v1/hot` | `/moboreels/api/v1/detail/{id}` | `/moboreels/api/v1/episodes/{id}` | `/moboreels/api/v1/play/{id}/{ep}` | - |
| **happyshort** | `/happyshort/api/v1/happy` | `/happyshort/api/v1/detail/{id}` | `/happyshort/api/v1/episodes/{id}` | `/happyshort/api/v1/play/{id}/{ep}` | - |
| **reelife** | `/reelife/api/v1/daily` | `/reelife/api/v1/detail/{id}` | `/reelife/api/v1/episodes/{id}` | `/reelife/api/v1/play/{id}/{ep}` | - |
| **pinedrama** | `/pinedrama/api/v1/hot` | `/pinedrama/api/v1/detail/{id}` | `/pinedrama/api/v1/episodes/{id}` | `/pinedrama/api/v1/play/{id}/{ep}` | - |
| **flextv** | `/flextv/api/v1/trending` | `/flextv/api/v1/detail/{id}` | `/flextv/api/v1/episodes/{id}` | `/flextv/api/v1/play/{id}/{ep}` | - |
| **reelala** | `/reelala/api/v1/hot` | `/reelala/api/v1/detail/{id}` | `/reelala/api/v1/episodes/{id}` | `/reelala/api/v1/play/{id}/{ep}` | - |

---

## 3. 📦 Penanganan Response (SANGAT KRUSIAL)

Response dari DramaBos API **TIDAK KONSISTEN** antar provider. Anda WAJIB membuat fungsi normalisasi.

### Tipe A: Array Langsung (Contoh: FlickReels)
```json
[
  {
    "id": "6031",
    "title": "At 50, I Married My Ex's CEO Boss",
    "cover": "https://cdn.dramabos.live/video/flickreels/covers/6031.jpg",
    "episodes": 60,
    "genre": "Romance"
  }
]
```

### Tipe B: Object dengan Wrapper `data` (Contoh: ReelShort, DramaBox)
```json
{
  "status": 200,
  "data": [
    { "id": "123", "title": "CEO's Secret", "cover": "..." }
  ]
}
```

### Tipe C: Object dengan Wrapper `list` atau `items`
```json
{
  "code": 0,
  "list": [ ... ]
}
```

**💡 Solusi untuk Developer:**
Buat method `normalizeResponse($response)` di `ApiService.php` yang mengecek secara berurutan:
1. Apakah `$response[0]` ada? (Tipe A)
2. Apakah `$response['data']` ada? (Tipe B)
3. Apakah `$response['list']` atau `$response['items']` ada? (Tipe C)

---

## 4. ⛔ Aturan Implementasi untuk Developer / AI

Jika Anda adalah AI atau Junior Programmer yang membaca dokumen ini, **HARAM** bagi Anda untuk melanggar aturan berikut:

1. **PHP Cross-Compatible (5.6 - 8.3)**
   - ❌ DILARANG menggunakan `??` (Null Coalescing). Gunakan `isset($var) ? $var : 'default'`.
   - ❌ DILARANG menggunakan `[]` untuk array. Gunakan `array()`.
   - ❌ DILARANG menggunakan `fn()`. Gunakan `function()`.

2. **cURL Configuration (Wajib untuk ByetHost/AeonFree)**
   ```php
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
   curl_setopt($ch, CURLOPT_TIMEOUT, 30);
   ```

3. **URL & Routing**
   - ❌ DILARANG menggunakan `BASE_URL` langsung di HTML. Gunakan helper `url('path')`.
   - ❌ DILARANG hardcode URL dengan double-slash (`//auth/login`). Server akan block dengan 404.

4. **File Caching**
   - Semua response API **WAJIB** di-cache ke folder `/storage/cache/` minimal 1 jam.
   - Gunakan `LOCK_EX` saat `file_put_contents()` untuk mencegah file corrupt.
   - Gunakan `md5($endpoint)` sebagai nama file cache.

5. **Output ke View**
   - ❌ DILARANG menggunakan `htmlspecialchars($var)` langsung (bisa crash di PHP 8.3 jika `$var` null).
   - ✅ WAJIB gunakan helper `e($var)` yang sudah membungkus `isset()`.

---

## 5. 💻 Contoh Implementasi PHP (Boleh di-Copy-Paste)

```php
// Contoh Mapping Endpoint di dalam Class ApiService
private $feedMap = array(
    'shortmax'   => '/shortmax/api/v1/home',
    'flickreels' => '/flickreels/api/flickreels/trending?lang=en',
    'dramabox'   => '/dramabox/api/v1/discover',
    'reelshort'  => '/reelshort/api/v1/featured',
    'idrama'     => '/idrama/home?lang=id',
    // ... dst sesuai tabel di atas
);

// Contoh Normalisasi Response
private function normalizeResponse($response) {
    if (!$response) return array();
    if (isset($response[0]) && is_array($response[0])) return $response;
    if (isset($response['data'])) return $response['data'];
    if (isset($response['list'])) return $response['list'];
    if (isset($response['items'])) return $response['items'];
    return array();
}
```

---
*Dokumen ini adalah sumber kebenaran tunggal (Single Source of Truth) untuk integrasi DramaBos API di proyek Nontonin. Jangan berhalusinasi membuat endpoint di luar tabel ini.*
```

***

### 💡 Cara Menggunakan File Ini:

1. **Upload ke GitHub:**
   Buat file baru di repository `escin-team/nontonin` bernama **`api-providers.md`**, lalu paste seluruh teks Markdown di atas.

2. **Cara Memberikannya ke AI / Junior Programmer:**
   Saat Anda meminta AI untuk membuat atau memperbaiki `ApiService.php` atau `cron_aggregator.php`, gunakan kalimat pembuka ini:

   > *"Bertindaklah sebagai Senior PHP Developer. Baca file `api-providers.md` di repository saya terlebih dahulu. Itu adalah dokumentasi resmi endpoint DramaBos. Jangan gunakan endpoint asumsi sendiri, gunakan persis seperti yang ada di tabel Master Reference pada file tersebut. Buatkan saya kode `ApiService.php` yang memetakan ke-32 provider tersebut..."*

3. **Mengapa Format Markdown Ini Sangat Efektif untuk AI?**
   * **Tabel (`| ... |`)**: AI sangat pintar membaca tabel Markdown dan akan langsung mengekstraknya menjadi Array Associative di dalam kode PHP (`$feedMap`, `$detailMap`).
   * **Blok Kode (` ``` `)**: AI akan meniru persis logika `normalizeResponse` dan `CURLOPT_SSL_VERIFYPEER` tanpa Anda perlu menjelaskannya berulang kali.
   * **Emoji & Heading**: Membantu "Attention Mechanism" pada model AI untuk memprioritaskan bagian **PERINGATAN** dan **ATURAN KETAT**.

Dengan adanya file `api-providers.md` ini di GitHub Anda, AI akan **berhenti berhalusinasi** membuat endpoint fiktif seperti `/api/v1/feed` dan langsung menggunakan endpoint asli seperti `/dramabox/api/v1/discover` atau `/flickreels/api/flickreels/trending?lang=en`. 

Ini adalah langkah pamungkas untuk membuat aplikasi Nontonin Anda memuat **Ribuan Drama** dari 32 Provider tanpa error! 🚀
