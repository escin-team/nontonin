# 📺 DramaStream - Web Streaming Platform

Platform streaming video web untuk Drama China, Anime, Movie, dan lainnya dengan integrasi API.

## 🚀 Fitur Utama

- **Autentikasi User**: Login/Register dengan password hashing (BCRYPT)
- **Integrasi API**: Mengambil metadata dari DramaBos API menggunakan cURL
- **Cache System**: File-based caching untuk mengurangi request API
- **Responsive UI**: Bootstrap 4 dengan tema dark mode
- **Watch History**: Fitur "Lanjutkan Menonton" dengan progress tracking
- **MVC Architecture**: Struktur kode yang terorganisir dan mudah dikembangkan

## 📋 Requirements

- PHP 5.6+ (direkomendasikan PHP 7.4)
- MySQL 5.7+
- Apache dengan mod_rewrite
- cURL extension
- OpenSSL extension

## 🛠️ Instalasi (Plug and Play - Zero Config Upload)

### Langkah 1: Download Source Code

Download ZIP dari repository GitHub atau clone:

```bash
git clone <repository-url>
cd nontonin
```

### Langkah 2: Setup Database

1. Buat database baru di phpMyAdmin atau MySQL:
```sql
CREATE DATABASE streaming_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import file SQL:
   - Buka phpMyAdmin
   - Pilih database `streaming_db`
   - Klik tab "Import"
   - Upload file `database/schema.sql`

### Langkah 3: Upload ke Hosting (AeonFree / Shared Hosting)

**PENTING**: Struktur ini sudah "Root = Web Root". Tidak perlu memindahkan file!

1. Extract semua isi folder dari ZIP
2. Upload **SEMUA** file dan folder langsung ke dalam `htdocs` hosting Anda
3. Struktur harus seperti ini di hosting:
   ```
   htdocs/
   ├── index.php
   ├── .htaccess
   ├── cron_aggregator.php
   ├── README.md
   ├── /app
   ├── /config
   ├── /database
   ├── /storage
   └── /assets
   ```

### Langkah 4: Konfigurasi Database & API

Edit file `config/config.php`:

```php
// Database credentials
define('DB_HOST', 'localhost');          // Ganti dengan host database Anda
define('DB_NAME', 'streaming_db');       // Nama database yang sudah dibuat
define('DB_USER', 'your_username');      // Username database Anda
define('DB_PASS', 'your_password');      // Password database Anda
define('DB_CHARSET', 'utf8');

// DramaBos API Configuration
define('API_BASE_URL', 'https://prod-api.dramabos.live');
define('API_TOKEN', 'YOUR_BEARER_TOKEN_HERE');  // Ganti dengan token API Anda
```

> **Catatan**: `BASE_URL` akan otomatis terdeteksi berdasarkan domain Anda. Tidak perlu diubah manual.

### Langkah 5: Set Permissions (Jika Menggunakan Linux/VPS)

```bash
chmod -R 755 storage/
chmod -R 755 assets/
```

> Untuk shared hosting seperti AeonFree, permissions biasanya sudah otomatis benar.

### Langkah 6: Setup Cron Job (Opsional - Untuk Auto-Update Cache)

Agar cache API terupdate otomatis setiap 6 jam, setup cron job di hosting:

**Via cPanel:**
1. Masuk ke cPanel > Cron Jobs
2. Tambah cron job baru:
   - Common Settings: Once per 6 hours
   - Command: `curl "https://yourdomain.com/cron_aggregator.php?secret_key=CHANGE_THIS_TO_A_STRONG_RANDOM_STRING"`

**Via .htaccess (Alternatif):**
Akses manual via browser minimal sekali sehari:
```
https://yourdomain.com/cron_aggregator.php?secret_key=CHANGE_THIS_TO_A_STRONG_RANDOM_STRING
```

> ⚠️ **PENTING**: Ganti `CHANGE_THIS_TO_A_STRONG_RANDOM_STRING` di `cron_aggregator.php` dengan string random yang kuat!

### Langkah 7: Akses Aplikasi

Buka browser dan akses domain Anda:
```
https://yourdomain.com/
```

**Default Admin Account:**
- Username: `admin`
- Password: `admin123`

⚠️ **PENTING**: Segera ubah password default setelah login pertama kali!

## 📁 Struktur Direktori

```
/ (Root Repository = Web Root / htdocs)
├── index.php             # Entry point aplikasi
├── .htaccess             # URL rewriting & keamanan
├── cron_aggregator.php   # Cron job untuk agregasi API
├── README.md             # Dokumentasi
├── /app                  # Backend MVC
│   ├── /controllers      # Controller (AuthController, HomeController, dll)
│   ├── /models           # Model (UserModel, ShowModel, dll)
│   ├── /views            # View files (templates HTML)
│   └── /core             # Core classes (Database, Router, Controller, ApiService)
├── /config               # Konfigurasi (database, API)
├── /database             # File SQL schema
├── /storage              # Cache files (auto-generated)
│   └── /cache            # API cache JSON files
└── /assets               # Frontend assets
    ├── /css              # Stylesheets
    ├── /js               # JavaScript files
    └── /images           # Images
```

## 🔐 Keamanan

1. **Password Hashing**: Menggunakan `password_hash()` dengan BCRYPT
2. **CSRF Protection**: Token CSRF pada semua form
3. **Prepared Statements**: Mencegah SQL Injection
4. **XSS Protection**: `htmlspecialchars()` pada output
5. **Session Management**: Session timeout dan secure cookies
6. **Directory Protection**: Folder sensitif (`app`, `config`, `database`, `storage`) diblokir dari akses langsung via `.htaccess`

## ⚠️ Catatan Penting

### PHP 5.6 Compatibility

Kode ini 100% kompatibel dengan PHP 5.5/5.6 dengan menghindari fitur-fitur baru:

- ❌ Tidak menggunakan Null Coalescing Operator (`??`) - menggunakan `isset()`
- ❌ Tidak menggunakan Arrow Functions (`fn()`) - menggunakan anonymous function tradisional
- ❌ Tidak menggunakan Short Array Syntax (`[]`) - menggunakan `array()`
- ❌ Tidak menggunakan Scalar Type Declarations
- ✅ Menggunakan `__DIR__` untuk semua path file
- ✅ SSL verification bypassed untuk cURL (`CURLOPT_SSL_VERIFYPEER => false`)

### End-of-Life Warning

PHP 5.6 sudah tidak mendapat update keamanan sejak 2018. Sangat direkomendasikan untuk upgrade ke PHP 7.4 atau lebih tinggi jika hosting mendukung.

### API Integration

- Semua request API dilakukan melalui backend (PHP), bukan frontend
- API key tidak pernah terekspos di client-side
- Response API di-cache untuk performa dan limitasi rate
- Cache disimpan di `/storage/cache/`

### Troubleshooting

**Error 403 Forbidden saat akses folder:**
- Ini normal! Folder `app`, `config`, `database`, `storage` memang diblokir untuk keamanan.

**Error 500 Internal Server Error:**
- Cek versi PHP (harus 5.6+)
- Pastikan mod_rewrite aktif di Apache
- Cek error log di hosting

**Cache tidak terupdate:**
- Pastikan folder `/storage/cache/` memiliki permission 755
- Jalankan cron_aggregator.php manual via browser

## 🎯 Roadmap Pengembangan

### Phase 1: ✅ Setup & Autentikasi
- [x] Setup database PDO
- [x] Login/Register dengan validasi
- [x] Password hashing BCRYPT
- [x] Session management
- [x] CSRF protection

### Phase 2: 🔄 Integrasi API
- [x] ApiService class dengan cURL
- [x] File caching system
- [ ] Endpoint trending dramas
- [ ] Endpoint detail drama
- [ ] Endpoint streaming URL

### Phase 3: ⏳ Frontend & Player
- [x] Homepage dengan grid drama
- [ ] Detail drama page
- [ ] Video player (Video.js/DPlayer)
- [ ] Watch progress tracking
- [ ] Auto-next episode

### Phase 4: 🔮 Fitur Lanjutan
- [ ] Watchlist/Bookmark
- [ ] Search functionality
- [ ] Admin dashboard
- [ ] Multi-source support (Anime, Movie)

## 📝 License

Project ini dibuat untuk tujuan pembelajaran. Pastikan untuk mematuhi hak cipta dan ketentuan layanan dari sumber konten yang digunakan.

## 🤝 Kontribusi

Kontribusi sangat welcome! Silakan fork dan buat pull request.

---

**Dibuat dengan ❤️ untuk komunitas streaming Indonesia**
