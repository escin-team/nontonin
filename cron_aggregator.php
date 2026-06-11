<?php
/**
 * Cron Aggregator for DramaBos API
 * PHP 5.6 Compatible - No modern syntax allowed
 * 
 * This file should be called via browser or cron job every 6 hours.
 * It fetches data from multiple providers and saves to global_feed.json
 * 
 * Usage: 
 *   - Browser: https://yourdomain.com/cron_aggregator.php
 *   - Cron: 0 */6 * * * curl https://yourdomain.com/cron_aggregator.php > /dev/null 2>&1
 */

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/ApiService.php';

// Set execution time limit (important for aggregation)
ini_set('max_execution_time', 300); // 5 minutes
set_time_limit(300);

// Disable output buffering for progress display
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('implicit_flush', 'On');
ob_end_clean();
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Aggregator - Progress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            transition: width 0.3s ease;
            text-align: center;
            line-height: 30px;
            color: white;
            font-weight: bold;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .log {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        .log-item { margin: 5px 0; }
        .log-success { color: #28a745; }
        .log-error { color: #dc3545; }
        .log-info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 API Aggregator - DramaBos</h1>
        <p>Mengambil data dari multiple provider dan menyimpan ke cache global...</p>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progress" style="width: 0%;">0%</div>
        </div>
        
        <div id="status"></div>
        <div class="log" id="log"></div>
    </div>
    
    <script>
        function addLog(message, type) {
            var log = document.getElementById('log');
            var item = document.createElement('div');
            item.className = 'log-item log-' + type;
            var time = new Date().toLocaleTimeString();
            item.textContent = '[' + time + '] ' + message;
            log.appendChild(item);
            log.scrollTop = log.scrollHeight;
        }
        
        function updateProgress(percent) {
            document.getElementById('progress').style.width = percent + '%';
            document.getElementById('progress').textContent = Math.round(percent) + '%';
        }
    </script>
    
<?php
flush();
ob_flush();

// List of providers to aggregate (5-10 main providers)
$providers = array(
    'dramabox',
    'shortmax',
    'reelshort',
    'starshort',
    'dramabite',
    'freereels',
    'fundrama',
    'microdrama',
    'vigloo',
    'bilitv'
);

$totalProviders = count($providers);
$completedProviders = 0;
$globalFeed = array();
$errors = array();
$apiService = new ApiService();

echo "<script>addLog('Memulai agregasi dari " . $totalProviders . " provider...', 'info');</script>";
flush();
ob_flush();

// Loop through each provider
foreach ($providers as $index => $provider) {
    $startTime = microtime(true);
    
    echo "<script>addLog('Mengambil data dari provider: " . htmlspecialchars($provider) . "...', 'info');</script>";
    flush();
    ob_flush();
    
    try {
        // Try 'feed' endpoint first, fallback to 'trending' if needed
        $feedType = 'feed';
        $data = $apiService->getProviderFeed($provider, $feedType, true, 21600);
        
        // If feed returns empty, try trending
        if (empty($data) || (isset($data['data']) && empty($data['data']))) {
            echo "<script>addLog('Feed kosong, mencoba endpoint trending...', 'info');</script>";
            flush();
            ob_flush();
            
            $feedType = 'trending';
            $data = $apiService->getProviderFeed($provider, $feedType, true, 21600);
        }
        
        if (!empty($data)) {
            // Extract items from response (handle different API response structures)
            $items = array();
            
            if (isset($data['data']) && is_array($data['data'])) {
                $items = $data['data'];
            } elseif (isset($data['items']) && is_array($data['items'])) {
                $items = $data['items'];
            } elseif (isset($data['list']) && is_array($data['list'])) {
                $items = $data['list'];
            } elseif (is_array($data)) {
                $items = $data;
            }
            
            // Add source_provider tag to each item
            foreach ($items as &$item) {
                if (is_array($item)) {
                    $item['source_provider'] = $provider;
                    $item['feed_type'] = $feedType;
                    $item['fetched_at'] = date('Y-m-d H:i:s');
                }
            }
            unset($item); // Break reference
            
            // Merge into global feed
            $globalFeed = array_merge($globalFeed, $items);
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $itemCount = count($items);
            
            echo "<script>addLog('✓ Berhasil mengambil " . $itemCount . " item dari " . $provider . " (" . $duration . " detik)', 'success');</script>";
        } else {
            echo "<script>addLog('✗ Gagal mengambil data dari " . $provider . " (response kosong)', 'error');</script>";
            $errors[] = $provider . ': Response kosong';
        }
    } catch (Exception $e) {
        echo "<script>addLog('✗ Error pada " . $provider . ": " . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
        $errors[] = $provider . ': ' . $e->getMessage();
    }
    
    // Update progress
    $completedProviders++;
    $progress = ($completedProviders / $totalProviders) * 100;
    echo "<script>updateProgress(" . $progress . ");</script>";
    flush();
    ob_flush();
    
    // Sleep between requests to avoid rate limiting (2 seconds)
    if ($index < $totalProviders - 1) {
        echo "<script>addLog('Menunggu 2 detik sebelum request berikutnya...', 'info');</script>";
        flush();
        ob_flush();
        sleep(2);
    }
}

// Save aggregated data to global_feed.json
echo "<script>addLog('Menyimpan " . count($globalFeed) . " item ke global_feed.json...', 'info');</script>";
flush();
ob_flush();

try {
    // Ensure cache directory exists
    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0755, true);
    }
    
    // Prepare final data structure
    $finalData = array(
        'status' => 'success',
        'total_items' => count($globalFeed),
        'providers_count' => $totalProviders - count($errors),
        'errors_count' => count($errors),
        'last_updated' => date('Y-m-d H:i:s'),
        'timestamp' => time(),
        'data' => $globalFeed
    );
    
    // Save to file
    $cacheFile = CACHE_PATH . 'global_feed.json';
    $jsonContent = json_encode($finalData, JSON_PRETTY_PRINT);
    
    if (file_put_contents($cacheFile, $jsonContent)) {
        $fileSize = round(filesize($cacheFile) / 1024, 2);
        echo "<script>addLog('✓ Berhasil menyimpan global_feed.json (" . $fileSize . " KB)', 'success');</script>";
        echo "<script>document.getElementById('status').innerHTML = '<div class=\"status success\"><strong>✅ Agregasi Selesai!</strong><br>Total: " . count($globalFeed) . " item dari " . ($totalProviders - count($errors)) . " provider berhasil.</div>';</script>";
    } else {
        echo "<script>addLog('✗ Gagal menyimpan file cache', 'error');</script>";
        echo "<script>document.getElementById('status').innerHTML = '<div class=\"status error\"><strong>❌ Gagal Menyimpan Cache</strong></div>';</script>";
    }
} catch (Exception $e) {
    echo "<script>addLog('✗ Error saat menyimpan: " . htmlspecialchars($e->getMessage()) . "', 'error');</script>";
    echo "<script>document.getElementById('status').innerHTML = '<div class=\"status error\"><strong>❌ Error: " . htmlspecialchars($e->getMessage()) . "</strong></div>';</script>";
}

// Display summary
echo "<script>addLog('========================================', 'info');</script>";
echo "<script>addLog('RINGKASAN:', 'info');</script>";
echo "<script>addLog('Total Provider: " . $totalProviders . "', 'info');</script>";
echo "<script>addLog('Berhasil: " . ($totalProviders - count($errors)) . "', 'success');</script>";
echo "<script>addLog('Gagal: " . count($errors) . "', 'error');</script>";
echo "<script>addLog('Total Item: " . count($globalFeed) . "', 'info');</script>";

if (!empty($errors)) {
    echo "<script>addLog('----------------------------------------', 'info');</script>";
    echo "<script>addLog('ERROR DETAILS:', 'error');</script>";
    foreach ($errors as $error) {
        echo "<script>addLog('- " . htmlspecialchars($error) . "', 'error');</script>";
    }
}

echo "<script>addLog('========================================', 'info');</script>";
echo "<script>addLog('Selesai! Refresh halaman Home untuk melihat data terbaru.', 'success');</script>";

?>
    </div>
</body>
</html>
