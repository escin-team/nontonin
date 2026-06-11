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

## 🛠️ Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd dramastream
```

### 2. Setup Database

1. Buat database baru:
```sql
CREATE DATABASE streaming_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import schema:
```bash
mysql -u username -p streaming_db < database/schema.sql
```

### 3. Konfigurasi

Edit file `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'streaming_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

define('API_BASE_URL', 'https://api.dramabos.com'); // Ganti dengan API URL sebenarnya
define('API_KEY', 'your_api_key'); // Jika diperlukan

define('BASE_URL', 'http://localhost/streaming/public');
```

### 4. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 public/assets/
```

### 5. Configure Apache

Pastikan mod_rewrite aktif dan `.htaccess` dapat dibaca.

### 6. Akses Aplikasi

Buka browser dan akses: `http://localhost/streaming/public`

**Default Admin Account:**
- Username: `admin`
- Password: `admin123`

⚠️ **PENTING**: Segera ubah password default setelah login pertama kali!

## 📁 Struktur Direktori

```
/project-root
├── /app
│   ├── /controllers      # Controller (AuthController, HomeController, dll)
│   ├── /models           # Model (UserModel, ShowModel, dll)
│   ├── /views            # View files
│   │   ├── /auth         # Login & Register pages
│   │   ├── /layouts      # Main layout template
│   │   ├── /home         # Homepage
│   │   └── /player       # Video player page
│   └── /core             # Core classes (Database, Router, Controller, ApiService)
├── /public               # Document root (akses browser)
│   ├── /assets           # CSS, JS, Images
│   ├── index.php         # Entry point
│   └── .htaccess         # URL rewriting
├── /config               # Configuration files
├── /database             # SQL schema
└── /storage              # Cache files
```

## 🔐 Keamanan

1. **Password Hashing**: Menggunakan `password_hash()` dengan BCRYPT
2. **CSRF Protection**: Token CSRF pada semua form
3. **Prepared Statements**: Mencegah SQL Injection
4. **XSS Protection**: `htmlspecialchars()` pada output
5. **Session Management**: Session timeout dan secure cookies

## ⚠️ Catatan Penting

### PHP 5.6 Compatibility

Kode ini kompatibel dengan PHP 5.6 dengan menghindari fitur-fitur baru:

- ❌ Tidak menggunakan Null Coalescing Operator (`??`)
- ❌ Tidak menggunakan Arrow Functions (`fn()`)
- ✅ Menggunakan `isset()` dan anonymous functions tradisional

### End-of-Life Warning

PHP 5.6 sudah tidak mendapat update keamanan sejak 2018. Sangat direkomendasikan untuk upgrade ke PHP 7.4 atau lebih tinggi.

### API Integration

- Semua request API dilakukan melalui backend (PHP), bukan frontend
- API key tidak pernah terekspos di client-side
- Response API di-cache untuk performa dan limitasi rate

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
