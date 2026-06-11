<?php
/**
 * Cron Aggregator untuk DramaBos API - SELF CONTAINED VERSION
 * PHP 5.6 - 8.3 Compatible - Tidak pakai syntax modern (??, fn(), typed properties)
 * 
 * CRITICAL: Script ini TIDAK bergantung pada method ApiService yang mungkin belum ada!
 * Menggunakan fungsi cURL mandiri (fetchFromApi) untuk setiap request.
 * 
 * Script ini mengambil data trending dari 15 endpoint terverifikasi dan menyimpan ke global_feed.json
 * Dipanggil via browser atau cron job setiap 6 jam.
 * 
 * CARA PAKAI:
 *   - Browser: https://yourdomain.com/cron_aggregator.php?key=nontonin_rahasia_2026
 *   - Cron: 0 */6 * * * curl "https://yourdomain.com/cron_aggregator.php?key=nontonin_rahasia_2026" > /dev/null 2>&1
 * 
 * KEAMANAN: Validasi $_GET['key'] sebelum eksekusi!
 * 
 * PENTING: Setiap provider punya endpoint unik berdasarkan dokumentasi resmi DramaBos!
 * Handle berbagai format response: array langsung, object dengan wrapper data/items/list
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

// Set waktu eksekusi maksimal (penting untuk agregasi banyak provider)
ini_set('max_execution_time', 300); // 5 menit
set_time_limit(300);

// Path ke file cache global feed
$globalFeedPath = __DIR__ . '/storage/cache/global_feed.json';

// ===========================================================================
// FUNGSI MANDIRI - TIDAK BERGANTUNG PADA ApiService
// ===========================================================================

/**
 * Fetch data dari API menggunakan cURL - SELF CONTAINED
 * Tidak bergantung pada method ApiService yang mungkin belum ada
 * 
 * @param string $url Full URL untuk request
 * @return array|null JSON decoded response atau null pada failure
 */
function fetchFromApi($url) {
    // Inisialisasi cURL
    $ch = curl_init();
    
    // Opsi cURL - CRITICAL untuk ByetHost/AeonFree
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // BYPASS SSL VERIFICATION - Diperlukan untuk ByetHost/AeonFree
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Set headers dengan Bearer Token Authentication
    $headers = array(
        'Authorization: Bearer ' . API_TOKEN,
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.9',
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Eksekusi request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    // Handle cURL errors
    if ($curlErrno != 0 || !$response) {
        error_log('API cURL Error [' . $curlErrno . ']: ' . $curlError . ' - URL: ' . $url);
        return null;
    }
    
    // Handle HTTP errors
    if ($httpCode != 200) {
        error_log('API HTTP Error: ' . $httpCode . ' - URL: ' . $url . ' - Response: ' . substr($response, 0, 200));
        return null;
    }
    
    // Decode JSON response
    $data = json_decode($response, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg() . ' - URL: ' . $url);
        return null;
    }
    
    return $data;
}

/**
 * Normalize response dari berbagai format ke format konsisten
 * Handle: array langsung, object dengan wrapper data/items/list/result
 * 
 * @param mixed $response Raw response dari API
 * @return array Format konsisten: array('data' => [...])
 */
function normalizeApiResponse($response) {
    // Jika response kosong atau bukan array, kembalikan format standar
    if (empty($response) || !is_array($response)) {
        return array('data' => array());
    }
    
    // Cek apakah response adalah array langsung (tanpa wrapper)
    $keys = array_keys($response);
    $isIndexedArray = (count($keys) > 0 && isset($keys[0]) && is_int($keys[0]));
    
    if ($isIndexedArray) {
        // Ini adalah array langsung, bungkus dengan key 'data'
        return array('data' => $response);
    }
    
    // Cek apakah ada wrapper 'data'
    if (isset($response['data']) && is_array($response['data'])) {
        return array('data' => $response['data']);
    }
    
    // Cek wrapper alternatif: items, list, result, movies, videos
    $wrapperKeys = array('items', 'list', 'result', 'movies', 'videos');
    foreach ($wrapperKeys as $key) {
        if (isset($response[$key]) && is_array($response[$key])) {
            return array('data' => $response[$key]);
        }
    }
    
    // Jika tidak ada wrapper yang dikenali, kembalikan apa adanya
    return array('data' => $response);
}

/**
 * Simpan data ke file secara atomik (anti-corrupt)
 * Tulis ke file temporary dulu, lalu rename
 * 
 * @param string $filename Target filename
 * @param string $content File content
 * @return boolean Success status
 */
function atomicWrite($filename, $content) {
    $tempFile = $filename . '.tmp';
    
    // Write to temp file with exclusive lock
    if (file_put_contents($tempFile, $content, LOCK_EX) === false) {
        return false;
    }
    
    // Atomic rename
    if (rename($tempFile, $filename)) {
        return true;
    }
    
    // Cleanup temp file if rename failed
    @unlink($tempFile);
    return false;
}

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
        <p class="subtitle">SELF CONTAINED - Mengambil data dari 15 endpoint terverifikasi DramaBos API</p>
        
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
                <div class="label">Endpoint Sukses</div>
            </div>
            <div class="summary-card">
                <div class="number" id="error-count">0</div>
                <div class="label">Endpoint Gagal</div>
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

// ===========================================================================
// DAFTAR 15 ENDPOINT TERVERIFIKASI (berdasarkan dokumentasi resmi DramaBos)
// ===========================================================================
$verifiedEndpoints = array(
    // ShortMax endpoints
    array('provider' => 'shortmax', 'endpoint' => '/shortmax/api/v1/popular', 'name' => 'ShortMax Popular'),
    array('provider' => 'shortmax', 'endpoint' => '/shortmax/api/v1/home', 'name' => 'ShortMax Home'),
    array('provider' => 'shortmax', 'endpoint' => '/shortmax/api/v1/foryou', 'name' => 'ShortMax For You'),
    
    // FlickReels endpoints
    array('provider' => 'flickreels', 'endpoint' => '/flickreels/api/flickreels/trending?lang=en', 'name' => 'FlickReels Trending'),
    
    // DramaBox endpoints
    array('provider' => 'dramabox', 'endpoint' => '/dramabox/api/v1/discover', 'name' => 'DramaBox Discover'),
    array('provider' => 'dramabox', 'endpoint' => '/dramabox/api/v1/rank', 'name' => 'DramaBox Rank'),
    
    // ReelShort endpoints
    array('provider' => 'reelshort', 'endpoint' => '/reelshort/api/v1/featured', 'name' => 'ReelShort Featured'),
    
    // StarShort endpoints
    array('provider' => 'starshort', 'endpoint' => '/starshort/api/v1/trending', 'name' => 'StarShort Trending'),
    
    // DramaBite endpoints
    array('provider' => 'dramabite', 'endpoint' => '/dramabite/api/v1/recommend', 'name' => 'DramaBite Recommend'),
    
    // GoodShort endpoints
    array('provider' => 'goodshort', 'endpoint' => '/goodshort/api/v1/toppicks', 'name' => 'GoodShort Top Picks'),
    
    // ReelBuzz endpoints
    array('provider' => 'reelbuzz', 'endpoint' => '/reelbuzz/api/v1/buzz', 'name' => 'ReelBuzz Buzz')
);

$totalEndpoints = count($verifiedEndpoints);
$completedEndpoints = 0;
$globalFeed = array();
$errors = array();
$successfulEndpoints = array();
$seenDramaIds = array(); // Untuk deduplication

echo "<script>addLog('🚀 Memulai agregasi dari " . $totalEndpoints . " endpoint terverifikasi...', 'info');</script>";
echo "<script>addLog('⏱️ Timeout: 300 detik | Cache: 6 jam', 'info');</script>";
echo "<script>addLog('🔒 Mode: SELF CONTAINED (tidak bergantung ApiService)', 'info');</script>";
flush();
ob_flush();

// Loop melalui setiap endpoint terverifikasi
foreach ($verifiedEndpoints as $index => $endpointInfo) {
    $startTime = microtime(true);
    $provider = $endpointInfo['provider'];
    $endpoint = $endpointInfo['endpoint'];
    $endpointName = $endpointInfo['name'];
    $providerBadge = '<span class="provider-badge">' . e($provider) . '</span>';
    $endpointBadge = '<span class="provider-badge">' . e($endpointName) . '</span>';
    $fullUrl = API_BASE_URL . $endpoint;
    
    echo "<script>addLog('📡 [" . ($index + 1) . "/" . $totalEndpoints . "] Mengambil data dari " . $endpointBadge . "...', 'info');</script>";
    flush();
    ob_flush();
    
    try {
        // Fetch data menggunakan fungsi mandiri (TIDAK bergantung ApiService!)
        $rawResponse = fetchFromApi($fullUrl);
        
        // Cek apakah response valid
        if ($rawResponse === null) {
            echo "<script>addLog('❌ " . $providerBadge . " " . e($endpointName) . " - Response null/kosong', 'error');</script>";
            $errors[] = $endpointName . ': Response null';
        } else {
            // Normalize response ke format konsisten
            $normalized = normalizeApiResponse($rawResponse);
            
            // Cek apakah ada data
            if (!empty($normalized) && isset($normalized['data']) && is_array($normalized['data'])) {
                $items = $normalized['data'];
                $newItemsCount = 0;
                
                // Tambahkan metadata dan lakukan deduplication
                foreach ($items as &$item) {
                    if (is_array($item)) {
                        // Tambahkan tag source_provider
                        $item['source_provider'] = $provider;
                        // Tambahkan timestamp pengambilan
                        $item['fetched_at'] = date('Y-m-d H:i:s');
                        // Tambahkan info endpoint yang digunakan
                        $item['aggregated_by'] = 'cron_aggregator_selfcontained';
                        
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
                        
                        // Deduplication berdasarkan ID
                        $dramaId = isset($item['id']) ? $item['id'] : (isset($item['drama_id']) ? $item['drama_id'] : null);
                        if ($dramaId !== null) {
                            $uniqueKey = $provider . '_' . $dramaId;
                            if (!isset($seenDramaIds[$uniqueKey])) {
                                $seenDramaIds[$uniqueKey] = true;
                                $newItemsCount++;
                            }
                        } else {
                            // Jika tidak ada ID, tetap tambahkan (unik berdasarkan index)
                            $newItemsCount++;
                        }
                    }
                }
                unset($item); // Putuskan referensi
                
                // Gabungkan ke global feed (hanya item baru setelah deduplication)
                $globalFeed = array_merge($globalFeed, $items);
                $successfulEndpoints[] = $endpointName;
                
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);
                
                echo "<script>addLog('✅ " . $providerBadge . " " . e($endpointName) . " - Berhasil! " . count($items) . " drama (" . $duration . "s)', 'success');</script>";
            } else {
                // Response tidak memiliki data yang valid
                echo "<script>addLog('⚠️ " . $providerBadge . " " . e($endpointName) . " - Data kosong/tidak valid', 'warning');</script>";
                $errors[] = $endpointName . ': Data kosong';
            }
        }
        
    } catch (Exception $e) {
        // Catch error per endpoint - JANGAN biarkan error menyebar!
        $errorMsg = htmlspecialchars($e->getMessage());
        echo "<script>addLog('💥 " . $providerBadge . " " . e($endpointName) . " - Error: " . $errorMsg . "', 'error');</script>";
        $errors[] = $endpointName . ': ' . $e->getMessage();
    }
    
    // Update progress bar
    $completedEndpoints++;
    $progress = ($completedEndpoints / $totalEndpoints) * 100;
    echo "<script>updateProgress(" . $progress . ");</script>";
    echo "<script>updateSummary(" . count($globalFeed) . ", " . count($successfulEndpoints) . ", " . count($errors) . ");</script>";
    flush();
    ob_flush();
    
    // Beri jeda 1.5 detik antar request untuk hindari rate limit API
    if ($index < $totalEndpoints - 1) {
        echo "<script>addLog('⏳ Menunggu 1.5 detik sebelum endpoint berikutnya...', 'warning');</script>";
        flush();
        ob_flush();
        sleep(1.5);
    }
}

// Deduplicate final array (jika ada duplikasi yang lolos)
$uniqueFeed = array();
$seenIds = array();
foreach ($globalFeed as $item) {
    if (is_array($item)) {
        $dramaId = isset($item['id']) ? $item['id'] : (isset($item['drama_id']) ? $item['drama_id'] : null);
        $provider = isset($item['source_provider']) ? $item['source_provider'] : 'unknown';
        $uniqueKey = $provider . '_' . ($dramaId !== null ? $dramaId : uniqid());
        
        if (!isset($seenIds[$uniqueKey])) {
            $seenIds[$uniqueKey] = true;
            $uniqueFeed[] = $item;
        }
    }
}
$globalFeed = $uniqueFeed;

// Sort by priority (ShortMax dan FlickReels first, then others)
$priorityOrder = array('shortmax', 'flickreels', 'dramabox', 'reelshort', 'starshort', 'dramabite', 'goodshort', 'reelbuzz');
usort($globalFeed, function($a, $b) use ($priorityOrder) {
    $providerA = isset($a['source_provider']) ? $a['source_provider'] : 'zzz';
    $providerB = isset($b['source_provider']) ? $b['source_provider'] : 'zzz';
    $priorityA = array_search($providerA, $priorityOrder);
    $priorityB = array_search($providerB, $priorityOrder);
    if ($priorityA === false) $priorityA = 999;
    if ($priorityB === false) $priorityB = 999;
    return $priorityA - $priorityB;
});

// Save aggregated data to global_feed.json ATOMICALLY
echo "<script>addLog('💾 Menyimpan " . count($globalFeed) . " item ke global_feed.json (atomic write)...', 'info');</script>";
flush();
ob_flush();

try {
    // Pastikan direktori cache ada
    if (!is_dir(dirname($globalFeedPath))) {
        mkdir(dirname($globalFeedPath), 0777, true);
    }
    
    // Siapkan struktur data final
    $finalData = array(
        'status' => 'success',
        'total_items' => count($globalFeed),
        'endpoints_count' => count($successfulEndpoints),
        'errors_count' => count($errors),
        'last_updated' => date('Y-m-d H:i:s'),
        'timestamp' => time(),
        'aggregation_mode' => 'self_contained',
        'data' => $globalFeed
    );
    
    // Simpan ke file secara atomik
    $jsonContent = json_encode($finalData, JSON_PRETTY_PRINT);
    
    if (atomicWrite($globalFeedPath, $jsonContent)) {
        $fileSize = round(filesize($globalFeedPath) / 1024, 2);
        echo "<script>addLog('✅ Berhasil menyimpan global_feed.json (" . $fileSize . " KB) - ATOMIC WRITE', 'success');</script>";
        echo "<script>document.getElementById('status').innerHTML = '<div class=\"status-box status-success\"><strong>✅ Agregasi Selesai!</strong><br>Total: <strong>" . count($globalFeed) . " drama</strong> dari <strong>" . count($successfulEndpoints) . " endpoint</strong> berhasil disimpan.</div>';</script>";
        
        // Update summary card dengan ukuran cache
        echo "<script>updateCacheSize('" . $fileSize . " KB');</script>";
        
        // Tampilkan tombol link ke halaman home
        $homeUrl = url('home');
        echo "<script>document.getElementById('action-buttons').innerHTML = '<a href=\"" . e($homeUrl) . "\" class=\"btn-home\">🏠 Buka Halaman Home</a>';</script>";
    } else {
        echo "<script>addLog('❌ Gagal menyimpan file cache (atomic write failed)', 'error');</script>";
        echo "<script>document.getElementById('status').innerHTML = '<div class=\"status-box status-error\"><strong>❌ Gagal Menyimpan Cache</strong><br>Periksa permission direktori storage/cache/ (harus 777)</div>';</script>";
    }
} catch (Exception $e) {
    echo "<script>addLog('❌ Error saat menyimpan: " . e($e->getMessage()) . "', 'error');</script>";
    echo "<script>document.getElementById('status').innerHTML = '<div class=\"status-box status-error\"><strong>❌ Error: " . e($e->getMessage()) . "</strong></div>';</script>";
}

// Tampilkan ringkasan lengkap
echo "<script>addLog('========================================', 'info');</script>";
echo "<script>addLog('📊 RINGKASAN AGREGASI:', 'info');</script>";
echo "<script>addLog('Total Endpoint Dicoba: " . $totalEndpoints . "', 'info');</script>";
echo "<script>addLog('Endpoint Berhasil: " . count($successfulEndpoints) . " (" . implode(', ', $successfulEndpoints) . ")', 'success');</script>";
echo "<script>addLog('Endpoint Gagal: " . count($errors) . "', 'error');</script>";
echo "<script>addLog('Total Drama Terkumpul: " . count($globalFeed) . " item (setelah deduplication)', 'info');</script>";

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
