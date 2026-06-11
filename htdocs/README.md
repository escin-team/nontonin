# DramaStream - PHP 5.5/5.6 Compatible Streaming Platform

## 📋 Deskripsi
DramaStream (nontonin) adalah platform web streaming MVC yang dibangun dengan **Vanilla PHP** dan dioptimalkan untuk hosting gratis seperti **AeonFree** yang hanya mendukung **PHP 5.5 - 5.6**.

## ⚠️ ATURAN KETAT PHP 5.5/5.6
Proyek ini mengikuti aturan ketat untuk kompatibilitas PHP legacy:

| Fitur Modern | Pengganti PHP 5.5/5.6 |
|--------------|----------------------|
| `??` (Null Coalescing) | `isset($var) ? $var : 'default'` |
| `fn()` (Arrow Functions) | `function() use () {}` |
| `int $id`, `: array` | Tanpa type declarations |
| `random_bytes()` | `openssl_random_pseudo_bytes()` atau `hash('sha256', uniqid())` |
| `new class {}` | Class biasa dengan nama |
| Composer PSR-4 | Manual `spl_autoload_register` |
| `[...$arr]` | `array_merge()` |

## 📁 Struktur Folder (Flat Structure di htdocs)
```
htdocs/
├── index.php              # Entry point utama
├── .htaccess              # Apache rewrite rules
├── database.sql           # Database schema
├── config/
│   └── config.php         # Konfigurasi database & aplikasi
├── app/
│   ├── core/
│   │   ├── Router.php     # URL routing system
│   │   ├── Database.php   # PDO singleton connection
│   │   ├── ApiService.php # cURL API client + caching
│   │   └── Controller.php # Base controller
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   └── DramaController.php
│   ├── models/
│   │   └── UserModel.php
│   └── views/
│       ├── layouts/
│       │   └── main.php
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── home/
│       │   └── index.php
│       ├── drama/
│       │   ├── detail.php
│       │   └── watch.php
│       └── errors/
│           └── 404.php
├── storage/
│   └── cache/             # API response cache
└── assets/                # CSS, JS, images
```

## 🚀 Instalasi di AeonFree Hosting

### 1. Upload File
- Upload SEMUA isi folder `htdocs/` ke folder `public_html/` atau `htdocs/` di hosting Anda
- Pastikan struktur folder tetap sama

### 2. Setup Database
1. Buka phpMyAdmin di hosting Anda
2. Buat database baru (misal: `streaming_db`)
3. Import file `database.sql`
4. Catat kredensial database

### 3. Konfigurasi
Edit file `config/config.php`:
```php
define('DB_HOST', 'localhost');        // Host database (biasanya localhost)
define('DB_NAME', 'nama_database_anda');
define('DB_USER', 'username_db_anda');
define('DB_PASS', 'password_db_anda');
define('BASE_URL', 'http://domain-anda.aeonspace.com');
```

### 4. Set Permission
Pastikan folder `storage/cache` memiliki permission **755** atau **777**:
```bash
chmod 755 storage/cache
```

### 5. Akses Aplikasi
Buka browser dan akses: `http://domain-anda.aeonspace.com`

## 🔐 Default Login Admin
- **Username:** `admin`
- **Password:** `admin123`
- **Email:** `admin@dramastream.local`

⚠️ **SEGERA UBAH PASSWORD SETELAH LOGIN PERTAMA!**

## 🛠️ Fitur Core System

### 1. Router (`app/core/Router.php`)
- Support parameterized routes: `/drama/{slug}`
- Support Controller@Method syntax: `'HomeController@index'`
- Support closure/callback routes
- Auto 404 handling

### 2. Database (`app/core/Database.php`)
- Singleton pattern
- PDO dengan error mode exception
- Prepared statements untuk keamanan SQL injection

### 3. API Service (`app/core/ApiService.php`)
- cURL HTTP client
- **SSL bypass** (`CURLOPT_SSL_VERIFYPEER => false`) untuk kompatibilitas AeonFree
- File-based caching di `/storage/cache/`
- Cache duration configurable

### 4. Controller (`app/core/Controller.php`)
- Method `view()` untuk render template
- Method `model()` untuk load model
- CSRF token generation dengan `openssl_random_pseudo_bytes()`
- CSRF verification dengan timing-safe comparison (fallback PHP 5.5)

## 🔒 Keamanan

### CSRF Protection
Semua form POST dilengkapi CSRF token:
```php
// Generate token
$csrfToken = $this->generateCsrfToken();

// Verify token
if (!$this->verifyCsrfToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

### Password Hashing
Menggunakan `password_hash()` dan `password_verify()` (tersedia sejak PHP 5.5):
```php
$hashed = password_hash($password, PASSWORD_DEFAULT);
$valid = password_verify($password, $hashed);
```

### SQL Injection Prevention
Semua query menggunakan prepared statements:
```php
$stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute(array($userId));
```

## 📝 Contoh Membuat Controller Baru

```php
<?php
class SearchController extends Controller {
    
    public function index() {
        $this->requireLogin();
        
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        $results = array();
        
        if (!empty($query)) {
            $results = $this->api->searchDramas($query);
        }
        
        $this->view('search/results', array(
            'page_title' => 'Search',
            'query' => $query,
            'results' => $results
        ));
    }
}
```

## 📝 Contoh Membuat Route Baru

Di `index.php`:
```php
// GET route dengan parameter
$router->get('/search', 'SearchController@index');

// POST route
$router->post('/api/save', 'ApiController@save');

// Route dengan multiple parameters
$router->get('/drama/{slug}/episode/{episodeId}', 'DramaController@watch');
```

## 🐛 Troubleshooting

### Error: "Database connection failed"
- Periksa kredensial di `config/config.php`
- Pastikan database sudah dibuat
- Pastikan user database memiliki akses

### Error: "Class not found"
- Pastikan nama file sesuai dengan nama class (case-sensitive)
- Cek path autoloader di `index.php`

### Cache tidak tersimpan
- Check permission folder `storage/cache` (harus 755 atau 777)
- Pastikan `CACHE_DIR` terdefinisi di `config.php`

### SSL/Certificate Error saat call API
- Sudah dihandle otomatis dengan `CURLOPT_SSL_VERIFYPEER => false`
- Jika masih error, cek apakah `curl` extension aktif di PHP

## 📄 License
Free to use for personal and educational purposes.

## 👨‍💻 Developer
Built with ❤️ using Vanilla PHP 5.5/5.6 for AeonFree Hosting compatibility.
