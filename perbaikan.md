Berikut adalah **Prompt Master Final** yang dirancang khusus untuk memperbaiki **SELURUH alur aplikasi Nontonin** (Home → Detail → Stream Video) agar bekerja sempurna dengan 32+ provider DramaBos API yang memiliki pola endpoint berbeda-beda.

***

```text
Bertindaklah sebagai Senior PHP Full-Stack Developer yang ahli dalam integrasi API streaming dan legacy system maintenance (PHP 5.6 - 8.3).

# KONTEKS PROYEK

Saya memiliki aplikasi MVC PHP bernama "Nontonin" yang terintegrasi dengan DramaBos API (32+ provider). 
Aplikasi di-hosting di ByetHost/AeonFree dengan **PHP 8.3.19**.

**Alur yang harus bekerja sempurna:**
1. **Homepage** → Menampilkan grid poster dari 32+ provider
2. **Detail Page** → Menampilkan sinopsis + daftar episode
3. **Watch Page** → Memutar video HLS (.m3u8) via DPlayer + hls.js

# MASALAH UTAMA YANG HARUS DIPERBAIKI

## 1. SETIAP PROVIDER PUNYA ENDPOINT UNIK (WAJIB DIBACA: `api-providers.md`)

Tidak ada endpoint universal. Setiap provider punya pola sendiri:

### Provider dengan pola standar (`/{provider}/api/v1/...`):
- reelshort, starshort, dramabite, goodshort, reelbuzz, freereels
- vigloo, dramawave, microdrama, bilitv, netshort, melolo
- velolo, stardusttv, serialplus, dotdrama, rapidtv, shortswave
- dramanova, cubetv, flareflow, moboreels, happyshort, reelife
- pinedrama, flextv, reelala

### Provider dengan pola UNIK (HARUS DITANGANI KHUSUS):

| Provider | Feed | Detail | Episodes | Stream |
|---|---|---|---|---|
| **shortmax** | `/shortmax/api/v1/home` | `/shortmax/api/v1/detail/{id}` | `/shortmax/api/v1/episodes/{id}` | `/shortmax/api/v1/play/{id}/{ep}` |
| **dramabox** | `/dramabox/api/v1/discover` | `/dramabox/api/v1/detail/{id}` | `/dramabox/api/v1/episodes/{id}` | `/dramabox/api/v1/play/{id}/{ep}` |
| **flickreels** | `/flickreels/api/flickreels/trending?lang=en` | `/flickreels/api/flickreels/detail?id={id}` | `/flickreels/api/flickreels/allepisode?id={id}` | `/flickreels/api/flickreels/episode?id={id}&ep={ep}` |
| **idrama** | `/idrama/home?lang=id` | `/idrama/drama/{id}?lang=id` | `/idrama/episodes/{id}?lang=id` | `/idrama/play/{id}/{ep}?lang=id` |

## 2. RESPONSE TIDAK KONSISTEN
- Beberapa provider return **array langsung** `[{...}, {...}]`
- Beberapa return **object wrapper** `{"data": [...]}`, `{"list": [...]}`, `{"items": [...]}`
- WAJIB ada normalisasi response

## 3. PHP 8.3 STRICT MODE
- `htmlspecialchars(null)` → Fatal Error
- Array access tanpa isset → Warning
- `call_user_func("Controller@method")` → Fatal Error

## 4. BYETHOST SPECIFIC
- cURL WAJIB bypass SSL (`CURLOPT_SSL_VERIFYPEER => false`)
- URL dengan double-slash (`//auth/login`) → Error 404
- CDN gambar `cdn.dramabos.live` pakai hotlink protection

# ATURAN KETAT (HARAM DILANGGAR)

1. **PHP 5.6 - 8.3 Compatible:**
   - WAJIB `isset()` bukan `??`
   - WAJIB `array()` bukan `[]`
   - WAJIB `function()` bukan `fn()`
   - DILARANG typed properties & union types

2. **URL Anti-Double-Slash:**
   - SEMUA URL di HTML WAJIB pakai `url('path')`
   - SEMUA redirect WAJIB pakai `redirect('path')`
   - DILARANG `BASE_URL . '/path'`

3. **Output Anti-XSS & Anti-Crash:**
   - SEMUA text output WAJIB pakai `e($string)` (htmlspecialchars + isset)
   - DILARANG `htmlspecialchars($var)` langsung

4. **Image Anti-Hotlink:**
   - SEMUA view WAJIB punya `<meta name="referrer" content="no-referrer">`
   - Fallback gambar: `url('assets/img/no-poster.jpg')`

# TUGAS: Buatkan 7 FILE LENGKAP

## FILE 1: `app/core/ApiService.php` (PENTING - JANTUNG APLIKASI)

Buat class ApiService dengan:

### A. Properties (Mapping Endpoint per Provider)
```php
private $feedMap = array(
    'shortmax'   => '/shortmax/api/v1/home',
    'flickreels' => '/flickreels/api/flickreels/trending?lang=en',
    'dramabox'   => '/dramabox/api/v1/discover',
    'reelshort'  => '/reelshort/api/v1/featured',
    // ... SEMUA 32 provider dari tabel di atas
);

private $detailMap = array(
    'flickreels' => '/flickreels/api/flickreels/detail?id=',
    'idrama'     => '/idrama/drama/{id}?lang=id',
    // default: '/{provider}/api/v1/detail/{id}'
);

private $episodesMap = array(
    'flickreels' => '/flickreels/api/flickreels/allepisode?id=',
    'idrama'     => '/idrama/episodes/{id}?lang=id',
    // default: '/{provider}/api/v1/episodes/{id}'
);

private $streamMap = array(
    'flickreels' => '/flickreels/api/flickreels/episode?id={id}&ep={ep}',
    'idrama'     => '/idrama/play/{id}/{ep}?lang=id',
    // default: '/{provider}/api/v1/play/{id}/{ep}'
);
```

### B. Methods yang WAJIB ADA:
1. `__construct()` - load API_TOKEN & API_BASE_URL
2. `request($endpoint, $cache_time)` - cURL engine dengan bypass SSL + file cache
3. `normalizeResponse($response)` - handle array langsung, data, list, items
4. `getTrending($provider)` - return array dengan key 'data'
5. `getDramaDetail($provider, $drama_id)` - return detail drama
6. `getEpisodes($provider, $drama_id)` - return list episode
7. `getStreamUrl($provider, $drama_id, $episode_num)` - return URL .m3u8
8. `getAllProviders()` - return array 32 provider names

### C. Logic Penting:
- Gunakan `str_replace('{id}', $id, $endpoint)` untuk replace placeholder
- Gunakan `str_replace('{ep}', $ep, $endpoint)` untuk episode number
- Untuk flickreels yang pakai `?id=`, append ID ke akhir string
- Cache: feed=6jam, detail=12jam, episodes=6jam, stream=15menit

## FILE 2: `app/controllers/HomeController.php`

```php
public function index() {
    // 1. Baca dari cache global_feed.json (hasil cron_aggregator)
    // 2. Jika cache kosong/tua, fallback ke API call langsung (ambil 5 provider utama saja)
    // 3. Kirim $dramas ke view 'home/index'
    // 4. Gunakan try-catch untuk graceful degradation
}
```

## FILE 3: `app/controllers/DramaController.php`

```php
public function detail($provider, $id) {
    // 1. Hit getDramaDetail($provider, $id)
    // 2. Hit getEpisodes($provider, $id)
    // 3. Kirim ke view 'drama/detail'
    // 4. Handle jika detail/episodes kosong (tampilkan 404 custom)
}

public function watch($provider, $id, $episode_num = 1) {
    // 1. Hit getStreamUrl($provider, $id, $episode_num)
    // 2. Ambil URL .m3u8 dari response (cek key 'url', 'stream_url', 'hls_url', 'data.url')
    // 3. Kirim ke view 'player/watch'
    // 4. Jika URL kosong, tampilkan error message
}
```

## FILE 4: `app/views/home/index.php`

- Meta tag: `<meta name="referrer" content="no-referrer">`
- Bootstrap 4 Dark Mode
- Grid poster dengan `col-6 col-md-3 col-lg-2`
- SEMUA link pakai `url('drama/' . $provider . '/' . $id)`
- SEMUA text pakai `e($title)`
- Fallback poster: `url('assets/img/no-poster.jpg')`
- Tampilkan badge provider di setiap card

## FILE 5: `app/views/drama/detail.php`

- Meta tag no-referrer
- Layout 2 kolom (cover + info)
- Sinopsis, genre, rating, total episode
- Grid tombol episode dengan link: `url('watch/' . $provider . '/' . $id . '/' . $episode_num)`
- Tombol kembali ke home
- Empty state jika data kosong

## FILE 6: `app/views/player/watch.php` (PALING KRUSIAL)

- Meta tag no-referrer
- Container max-width 900px centered
- **DPlayer + hls.js Integration** yang BENAR:

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dplayer@latest/dist/DPlayer.min.css">
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/dplayer@latest/dist/DPlayer.min.js"></script>

<div id="dplayer"></div>

<script>
const videoUrl = '<?php echo e($videoUrl); ?>';
if (videoUrl) {
    const dp = new DPlayer({
        container: document.getElementById('dplayer'),
        theme: '#bb86fc',
        autoplay: false,
        video: {
            url: videoUrl,
            type: 'hls',
            customType: {
                hls: function(video, player) {
                    if (Hls.isSupported()) {
                        const hls = new Hls();
                        hls.loadSource(video.src);
                        hls.attachMedia(video);
                        hls.on(Hls.Events.MANIFEST_PARSED, function() {
                            console.log('HLS manifest loaded');
                        });
                        hls.on(Hls.Events.ERROR, function(event, data) {
                            console.error('HLS error:', data);
                        });
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        // Safari/iOS native HLS
                        video.src = video.src;
                    }
                }
            }
        }
    });
}
</script>
```

- Tampilkan error message jika videoUrl kosong
- Navigasi episode (prev/next) dengan link `url('watch/' . $provider . '/' . $id . '/' . ($ep-1))`
- Tombol kembali ke detail

## FILE 7: `cron_aggregator.php` (di root htdocs)

- Validasi `$_GET['key']` untuk security
- Loop 32 provider dari `$api->getAllProviders()`
- Hit `getTrending($provider)` untuk setiap provider
- Normalisasi response dengan `normalizeResponse()`
- Deduplicate drama berdasarkan `provider_id`
- Simpan ke `storage/cache/global_feed.json` dengan atomic write
- Tampilkan progress HTML dengan statistik
- `sleep(1)` antar request untuk hindari rate limit
- Set `memory_limit = 512M` dan `max_execution_time = 600`

# FORMAT OUTPUT

Berikan 7 blok kode LENGKAP dengan format:

```php
// File: path/to/file.php
<?php
// kode lengkap, NO placeholder, NO TODO, NO "// ..."
```

# CHECKLIST VALIDASI

□ ApiService punya mapping 32 provider untuk FEED
□ ApiService punya mapping 32 provider untuk DETAIL
□ ApiService punya mapping 32 provider untuk EPISODES  
□ ApiService punya mapping 32 provider untuk STREAM
□ FlickReels pakai `?id=` query string
□ iDrama pakai `?lang=id` dan tanpa `/api/v1/`
□ normalizeResponse() handle 4 tipe response
□ getStreamUrl() bisa ekstrak URL .m3u8 dari berbagai key
□ HomeController baca dari global_feed.json
□ DramaController detail() hit detail + episodes
□ DramaController watch() hit stream URL
□ View home punya meta referrer + grid poster
□ View detail punya meta referrer + episode buttons
□ View watch punya DPlayer + hls.js + custom HLS handler
□ View watch punya navigasi prev/next episode
□ Cron aggregator loop 32 provider dengan deduplicate
□ Semua URL pakai helper `url()`
□ Semua text output pakai helper `e()`
□ Semua cURL pakai `CURLOPT_SSL_VERIFYPEER => false`
□ PHP 5.6 - 8.3 compatible (no ??, no [], no fn)

# PENTING

- JANGAN ada placeholder `// implement later`
- JANGAN ada kode setengah jadi
- Test mental untuk PHP 8.3 strict mode
- Pastikan alur Home → Detail → Watch berjalan mulus
- Inline comments dalam Bahasa Indonesia
- Sertakan contoh response JSON di komentar untuk referensi

Kirimkan 7 file LENGKAP sekarang! Ini adalah kunci agar aplikasi streaming Nontonin berfungsi penuh dengan 32+ provider.
```

***

### 📋 Cara Menggunakan Prompt Ini:

1. **Copy seluruh teks** di dalam kotak di atas
2. **Paste ke AI** (ChatGPT-4, Claude, Qwen-Max, DeepSeek, atau AI coding assistant lainnya)
3. **Sebutkan juga**: *"Baca file `api-providers.md` di repository GitHub saya `escin-team/nontonin` sebagai referensi endpoint yang akurat"*
4. **Tunggu hasilnya** - AI akan memberikan 7 file lengkap

### 🚀 Setelah Dapat Hasil:

Upload ke ByetHost sesuai path:

| File | Path di ByetHost |
|------|------------------|
| `ApiService.php` | `/htdocs/app/core/ApiService.php` |
| `HomeController.php` | `/htdocs/app/controllers/HomeController.php` |
| `DramaController.php` | `/htdocs/app/controllers/DramaController.php` |
| `home/index.php` | `/htdocs/app/views/home/index.php` |
| `drama/detail.php` | `/htdocs/app/views/drama/detail.php` |
| `player/watch.php` | `/htdocs/app/views/player/watch.php` |
| `cron_aggregator.php` | `/htdocs/cron_aggregator.php` |

### ✅ Alur Test Setelah Upload:

1. **Jalankan Cron**: `https://tontonin.byethost17.com/cron_aggregator.php?key=nontonin_rahasia_2026`
   - Harus sukses collect 500+ drama dari 25+ provider
   
2. **Buka Homepage**: `https://tontonin.byethost17.com/`
   - Grid poster drama harus muncul
   - Klik salah satu poster
   
3. **Halaman Detail**: `/drama/flickreels/6031`
   - Sinopsis + daftar episode harus muncul
   - Klik tombol "Ep 1"
   
4. **Halaman Watch**: `/watch/flickreels/6031/1`
   - DPlayer harus muncul
   - Video HLS harus bisa diputar
   - Tombol Next/Prev Episode berfungsi

### 🎯 Hasil yang Diharapkan:

- ✅ **32 Provider** terintegrasi dengan endpoint yang benar
- ✅ **Ribuan Drama** terkumpul di homepage
- ✅ **Detail Drama** lengkap dengan sinopsis & episodes
- ✅ **Video Player** HLS berfungsi dengan DPlayer
- ✅ **Navigasi Episode** (Next/Prev) smooth
- ✅ **Tidak ada double-slash** di URL manapun
- ✅ **Tidak ada Fatal Error** PHP 8.3
- ✅ **Gambar poster** muncul (tidak broken)

**Silakan jalankan prompt ini sekarang! Ini adalah kunci final untuk membuat Nontonin menjadi platform streaming yang benar-benar berfungsi dengan 32+ provider!** 🔥🎬
