<?php
/**
 * Cron Aggregator untuk DramaBos API
 * PHP 5.6 - 8.3 Compatible - Tidak pakai syntax modern (??, fn(), typed properties)
 * 
 * Script ini mengambil data trending dari 7 provider terverifikasi dan menyimpan ke global_feed.json
 * Dipanggil via browser atau cron job setiap 6 jam.
 * 
 * CARA PAKAI:
 *   - Browser: https://yourdomain.com/cron_aggregator.php?key=nontonin_rahasia_2026
 *   - Cron: 0 */6 * * * curl "https://yourdomain.com/cron_aggregator.php?key=nontonin_rahasia_2026" > /dev/null 2>&1
 * 
 * KEAMANAN: Validasi $_GET['key'] sebelum eksekusi!
 * 
 * PENTING: Setiap provider punya endpoint unik berdasarkan dokumentasi resmi DramaBos!
 */

// Define secret key untuk proteksi akses cron
define('CRON_SECRET_KEY', 'nontonin_rahasia_2026');

// Validasi secret key dari parameter GET
if (isset($_GET['key'])) {
    if ($_GET['key'] !== CRON_SECRET_KEY) {
        header('HTTP/1.0 403 Forbidden');
        die('❌ Akses Ditolak: Secret key tidak valid!');
    }
} else {
    // Jika tidak ada key, cek apakah dijalankan dari CLI
    if (php_sapi_name() !== 'cli') {
        header('HTTP/1.0 403 Forbidden');
        die('❌ Akses Ditolak: Secret key diperlukan! Buka dengan ?key=nontonin_rahasia_2026');
    }
}

// Load konfigurasi
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/ApiService.php';

// Set waktu eksekusi maksimal (penting untuk agregasi banyak provider)
ini_set('max_execution_time', 300); // 5 menit
set_time_limit(300);

// Path ke file cache global feed
$globalFeedPath = __DIR__ . '/storage/cache/global_feed.json';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cron Aggregator - Nontonin</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .progress-container {
            background: #f0f0f0;
            border-radius: 20px;
            padding: 5px;
            margin: 25px 0;
        }
        .progress-bar {
            width: 100%;
            height: 35px;
            background: linear-gradient(90deg, #11998e, #38ef7d);
            border-radius: 15px;
            overflow: hidden;
            text-align: center;
            line-height: 35px;
            color: white;
            font-weight: bold;
            font-size: 16px;
            transition: width 0.4s ease;
        }
        .status-box {
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 8px;
            font-size: 14px;
        }
        .status-success { 
            background: linear-gradient(135deg, #d4edda, #c3e6cb); 
            color: #155724; 
            border-left: 4px solid #28a745;
        }
        .status-error { 
            background: linear-gradient(135deg, #f8d7da, #f5c6cb); 
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .status-info { 
            background: linear-gradient(135deg, #d1ecf1, #bee5eb); 
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .log-container {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
            max-height: 450px;
            overflow-y: auto;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .log-item { 
            margin: 8px 0; 
            padding: 5px 10px;
            border-radius: 4px;
        }
        .log-success { 
            color: #4ade80; 
            background: rgba(74, 222, 128, 0.1);
        }
        .log-error { 
            color: #f87171; 
            background: rgba(248, 113, 113, 0.1);
        }
        .log-info { 
            color: #60a5fa; 
            background: rgba(96, 165, 250, 0.1);
        }
        .log-warning { 
            color: #fbbf24; 
            background: rgba(251, 191, 36, 0.1);
        }
        .provider-badge {
            display: inline-block;
            padding: 3px 10px;
            background: #667eea;
            color: white;
            border-radius: 12px;
            font-size: 12px;
            margin-right: 8px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .summary-card .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .btn-home {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Cron Aggregator - Nontonin</h1>
        <p class="subtitle">Mengambil data trending dari 7 provider terverifikasi DramaBos API</p>
        
        <div class="progress-container">
            <div class="progress-bar" id="progress" style="width: 0%;">0%</div>
        </div>
        
        <div id="status"></div>
        
        <div class="summary-grid" id="summary" style="display:none;">
            <div class="summary-card">
                <div class="number" id="total-items">0</div>
                <div class="label">Total Drama</div>
            </div>
            <div class="summary-card">
                <div class="number" id="success-count">0</div>
                <div class="label">Provider Sukses</div>
            </div>
            <div class="summary-card">
                <div class="number" id="error-count">0</div>
                <div class="label">Provider Gagal</div>
            </div>
            <div class="summary-card">
                <div class="number" id="cache-size">-</div>
                <div class="label">Ukuran Cache</div>
            </div>
        </div>
        
        <div class="log-container" id="log"></div>
        
        <div id="action-buttons"></div>
    </div>
    
    <script>
        function addLog(message, type) {
            var log = document.getElementById('log');
            var item = document.createElement('div');
            item.className = 'log-item log-' + type;
            var time = new Date().toLocaleTimeString('id-ID');
            item.innerHTML = '<span style="opacity:0.7">[' + time + ']</span> ' + message;
            log.appendChild(item);
            log.scrollTop = log.scrollHeight;
        }
        
        function updateProgress(percent) {
            var bar = document.getElementById('progress');
            bar.style.width = percent + '%';
            bar.textContent = Math.round(percent) + '%';
        }
        
        function updateSummary(items, success, errors) {
            document.getElementById('summary').style.display = 'grid';
            document.getElementById('total-items').textContent = items;
            document.getElementById('success-count').textContent = success;
            document.getElementById('error-count').textContent = errors;
        }
        
        function updateCacheSize(size) {
            document.getElementById('cache-size').textContent = size;
        }
    </script>
    
<?php
flush();
ob_flush();

// Daftar 7 provider terverifikasi dengan endpoint unik berdasarkan dokumentasi resmi
$verifiedProviders = array(
    'dramabox',   // /dramabox/api/v1/discover
    'shortmax',   // /shortmax/api/v1/popular
    'reelshort',  // /reelshort/api/v1/featured
    'starshort',  // /starshort/api/v1/trending
    'dramabite',  // /dramabite/api/v1/recommend
    'flickreels', // /flickreels/api/flickreels/trending?lang=en
    'goodshort'   // /goodshort/api/v1/toppicks
);

$totalProviders = count($verifiedProviders);
$completedProviders = 0;
$globalFeed = array();
$errors = array();
$successfulProviders = array();
$apiService = new ApiService();

echo "<script>addLog('🚀 Memulai agregasi dari " . $totalProviders . " provider terverifikasi...', 'info');</script>";
echo "<script>addLog('⏱️ Timeout: 300 detik | Cache: 6 jam', 'info');</script>";
flush();
ob_flush();

// Loop melalui setiap provider terverifikasi
foreach ($verifiedProviders as $index => $provider) {
    $startTime = microtime(true);
    $providerBadge = '<span class="provider-badge">' . e($provider) . '</span>';
    
    echo "<script>addLog('📡 Mengambil data dari " . $providerBadge . "...', 'info');</script>";
    flush();
    ob_flush();
    
    try {
        // Ambil trending menggunakan method getTrending yang sudah mapping endpoint per provider
        $result = $apiService->getTrending($provider, 21600);
        
        // Cek apakah result valid dan punya data
        if (!empty($result) && isset($result['data']) && is_array($result['data'])) {
            $items = $result['data'];
            
            // Tambahkan metadata ke setiap item
            foreach ($items as &$item) {
                if (is_array($item)) {
                    // Tambahkan tag source_provider
                    $item['source_provider'] = $provider;
                    // Tambahkan timestamp pengambilan
                    $item['fetched_at'] = date('Y-m-d H:i:s');
                    // Tambahkan info endpoint yang digunakan
                    $item['aggregated_by'] = 'cron_aggregator';
                    
                    // Normalisasi field title jika ada variasi
                    if (!isset($item['title']) && isset($item['name'])) {
                        $item['title'] = $item['name'];
                    }
                    
                    // Normalisasi field cover jika ada variasi
                    if (!isset($item['cover']) && isset($item['poster'])) {
                        $item['cover'] = $item['poster'];
                    }
                    
                    // Bersihkan URL cover dari spasi trailing
                    if (isset($item['cover']) && is_string($item['cover'])) {
                        $item['cover'] = trim($item['cover']);
                    }
                }
            }
            unset($item); // Putuskan referensi
            
            // Gabungkan ke global feed
            $globalFeed = array_merge($globalFeed, $items);
            $successfulProviders[] = $provider;
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $itemCount = count($items);
            
            echo "<script>addLog('✅ " . $providerBadge . " Berhasil! " . $itemCount . " drama (" . $duration . "s)', 'success');</script>";
        } else {
            // Response kosong atau tidak valid
            echo "<script>addLog('❌ " . $providerBadge . " Response kosong atau tidak valid', 'error');</script>";
            $errors[] = $provider . ': Response kosong';
        }
        
    } catch (Exception $e) {
        // Catch error per provider - JANGAN biarkan error menyebar!
        $errorMsg = htmlspecialchars($e->getMessage());
        echo "<script>addLog('💥 " . $providerBadge . " Error: " . $errorMsg . "', 'error');</script>";
        $errors[] = $provider . ': ' . $e->getMessage();
    }
    
    // Update progress bar
    $completedProviders++;
    $progress = ($completedProviders / $totalProviders) * 100;
    echo "<script>updateProgress(" . $progress . ");</script>";
    echo "<script>updateSummary(" . count($globalFeed) . ", " . count($successfulProviders) . ", " . count($errors) . ");</script>";
    flush();
    ob_flush();
    
    // Beri jeda 2 detik antar request untuk hindari rate limit API
    if ($index < $totalProviders - 1) {
        echo "<script>addLog('⏳ Menunggu 2 detik sebelum provider berikutnya...', 'warning');</script>";
        flush();
        ob_flush();
        sleep(2);
    }
}

// Save aggregated data to global_feed.json
echo "<script>addLog('💾 Menyimpan " . count($globalFeed) . " item ke global_feed.json...', 'info');</script>";
flush();
ob_flush();

try {
    // Pastikan direktori cache ada
    if (!is_dir(dirname($globalFeedPath))) {
        mkdir(dirname($globalFeedPath), 0755, true);
    }
    
    // Siapkan struktur data final
    $finalData = array(
        'status' => 'success',
        'total_items' => count($globalFeed),
        'providers_count' => count($successfulProviders),
        'errors_count' => count($errors),
        'last_updated' => date('Y-m-d H:i:s'),
        'timestamp' => time(),
        'data' => $globalFeed
    );
    
    // Simpan ke file
    $cacheFile = $globalFeedPath;
    $jsonContent = json_encode($finalData, JSON_PRETTY_PRINT);
    
    if (file_put_contents($cacheFile, $jsonContent)) {
        $fileSize = round(filesize($cacheFile) / 1024, 2);
        echo "<script>addLog('✅ Berhasil menyimpan global_feed.json (" . $fileSize . " KB)', 'success');</script>";
        echo "<script>document.getElementById('status').innerHTML = '<div class=\"status-box status-success\"><strong>✅ Agregasi Selesai!</strong><br>Total: <strong>" . count($globalFeed) . " drama</strong> dari <strong>" . count($successfulProviders) . " provider</strong> berhasil disimpan.</div>';</script>";
        
        // Update summary card dengan ukuran cache
        echo "<script>updateCacheSize('" . $fileSize . " KB');</script>";
        
        // Tampilkan tombol link ke halaman home
        $homeUrl = url('home');
        echo "<script>document.getElementById('action-buttons').innerHTML = '<a href=\"" . e($homeUrl) . "\" class=\"btn-home\">🏠 Buka Halaman Home</a>';</script>";
    } else {
        echo "<script>addLog('❌ Gagal menyimpan file cache', 'error');</script>";
        echo "<script>document.getElementById('status').innerHTML = '<div class=\"status-box status-error\"><strong>❌ Gagal Menyimpan Cache</strong><br>Periksa permission direktori storage/cache/</div>';</script>";
    }
} catch (Exception $e) {
    echo "<script>addLog('❌ Error saat menyimpan: " . e($e->getMessage()) . "', 'error');</script>";
    echo "<script>document.getElementById('status').innerHTML = '<div class=\"status-box status-error\"><strong>❌ Error: " . e($e->getMessage()) . "</strong></div>';</script>";
}

// Tampilkan ringkasan lengkap
echo "<script>addLog('========================================', 'info');</script>";
echo "<script>addLog('📊 RINGKASAN AGREGASI:', 'info');</script>";
echo "<script>addLog('Total Provider Dicoba: " . $totalProviders . "', 'info');</script>";
echo "<script>addLog('Provider Berhasil: " . count($successfulProviders) . " (" . implode(', ', $successfulProviders) . ")', 'success');</script>";
echo "<script>addLog('Provider Gagal: " . count($errors) . "', 'error');</script>";
echo "<script>addLog('Total Drama Terkumpul: " . count($globalFeed) . " item', 'info');</script>";

if (!empty($errors)) {
    echo "<script>addLog('----------------------------------------', 'info');</script>";
    echo "<script>addLog('⚠️ DETAIL ERROR:', 'error');</script>";
    foreach ($errors as $error) {
        echo "<script>addLog('- " . e($error) . "', 'error');</script>";
    }
}

echo "<script>addLog('========================================', 'info');</script>";
echo "<script>addLog('✅ Selesai! Refresh halaman Home untuk melihat data terbaru.', 'success');</script>";

?>
    </div>
</body>
</html>
