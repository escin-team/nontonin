<?php
/**
 * Home Controller
 * Menampilkan halaman home dengan drama trending dari multiple provider DramaBos
 * PHP 5.6 - 8.3 Compatible
 * 
 * PENTING: Setiap provider punya endpoint unik! Tidak lagi pakai pattern generik.
 * Menggunakan try-catch per provider agar error tidak menyebar ke provider lain.
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/ApiService.php';

class HomeController extends Controller {
    private $apiService;
    
    // Daftar provider utama yang endpoint-nya sudah terverifikasi berdasarkan dokumentasi resmi
    private $verifiedProviders = array(
        'dramabox',   // /dramabox/api/v1/discover
        'shortmax',   // /shortmax/api/v1/popular
        'reelshort',  // /reelshort/api/v1/featured
        'starshort',  // /starshort/api/v1/trending
        'dramabite',  // /dramabite/api/v1/recommend
        'flickreels'  // /flickreels/api/flickreels/trending?lang=en
    );
    
    public function __construct() {
        parent::__construct();
        $this->apiService = new ApiService();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Display homepage - Mengambil trending dari 6-7 provider terverifikasi
     * - Loop melalui setiap provider dengan try-catch individual
     * - Gabungkan semua drama ke array besar
     * - Tambahkan field source_provider ke setiap drama
     * - Shuffle untuk variasi dan ambil 60 teratas
     * - Kirim ke view dengan variable $dramas dan $title
     */
    public function index() {
        $allDramas = array();
        $successfulProviders = array();
        $failedProviders = array();
        
        // Judul halaman
        $title = 'Nontonin - Streaming Drama Asia Terbaru';
        
        // Loop melalui 6 provider terverifikasi
        foreach ($this->verifiedProviders as $provider) {
            try {
                // Ambil trending dari provider (cache 6 jam = 21600 detik)
                $result = $this->apiService->getTrending($provider, 21600);
                
                // Cek apakah result ada dan punya data
                if (!empty($result) && isset($result['data']) && is_array($result['data'])) {
                    $dramas = $result['data'];
                    
                    // Tambahkan field source_provider ke setiap drama
                    foreach ($dramas as &$drama) {
                        if (is_array($drama)) {
                            // Tambahkan tag source_provider
                            $drama['source_provider'] = $provider;
                            
                            // Pastikan ID ada untuk URL generation
                            if (!isset($drama['id']) || empty($drama['id'])) {
                                if (isset($drama['drama_id']) && !empty($drama['drama_id'])) {
                                    $drama['id'] = $drama['drama_id'];
                                }
                            }
                            
                            // Normalisasi field title jika ada variasi
                            if (!isset($drama['title']) && isset($drama['name'])) {
                                $drama['title'] = $drama['name'];
                            }
                            
                            // Normalisasi field cover jika ada variasi
                            if (!isset($drama['cover']) && isset($drama['poster'])) {
                                $drama['cover'] = $drama['poster'];
                            }
                            
                            // Bersihkan URL cover dari spasi di akhir (issue umum dari API)
                            if (isset($drama['cover']) && is_string($drama['cover'])) {
                                $drama['cover'] = trim($drama['cover']);
                            }
                        }
                    }
                    unset($drama); // Putuskan referensi
                    
                    // Gabungkan ke array besar
                    $allDramas = array_merge($allDramas, $dramas);
                    $successfulProviders[] = $provider;
                    
                    // Catat progress ke error log untuk debugging
                    error_log('HomeController: Berhasil ambil ' . count($dramas) . ' drama dari ' . $provider);
                } else {
                    // Response kosong atau tidak valid
                    error_log('HomeController: Response kosong dari ' . $provider);
                    $failedProviders[] = $provider . ' (response kosong)';
                }
                
            } catch (Exception $e) {
                // Catch error per provider - JANGAN biarkan error menyebar!
                error_log('HomeController: Error pada provider ' . $provider . ' - ' . $e->getMessage());
                $failedProviders[] = $provider . ' (' . $e->getMessage() . ')';
                continue; // Lanjut ke provider berikutnya
            }
        }
        
        // Shuffle array untuk variasi konten dari berbagai provider
        if (!empty($allDramas)) {
            shuffle($allDramas);
            
            // Ambil 60 drama teratas
            $allDramas = array_slice($allDramas, 0, 60);
        }
        
        // Catat ringkasan ke error log
        error_log('HomeController: Total ' . count($allDramas) . ' drama dari ' . count($successfulProviders) . ' provider berhasil');
        if (!empty($failedProviders)) {
            error_log('HomeController: Provider gagal: ' . implode(', ', $failedProviders));
        }
        
        // Kirim data ke view
        $this->view('home/index', array(
            'dramas' => $allDramas,
            'title' => $title,
            'total_dramas' => count($allDramas),
            'successful_providers' => $successfulProviders,
            'failed_providers' => $failedProviders
        ));
    }
}
