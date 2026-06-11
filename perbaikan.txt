Bertindaklah sebagai Senior PHP Code Auditor. Saya memiliki proyek MVC PHP (Nontonin) yang mengalami masalah fatal di Shared Hosting (ByetHost/AeonFree). 

MASALAH UTAMA: "Double-Slash" (//) pada URL.
Karena `BASE_URL` seringkali memiliki garis miring di belakang (contoh: `https://domain.com/`), dan di file View/Controller saya menambahkan garis miring lagi (contoh: `BASE_URL . '/auth/login'`), URL menjadi `https://domain.com//auth/login`. Server ByetHost membenci ini dan langsung memblokir dengan Error 404.

TUGAS ANDA:
Saya tidak ingin Anda memperbaiki file satu per satu secara manual. Tugas Anda adalah:
1. Buatkan 2 Fungsi Helper Global yang aman untuk PHP 5.6 hingga PHP 8.3 (`url()` untuk HTML/View, dan `redirect()` untuk Controller).
2. Berikan "Cheat Sheet" atau Pola **Find & Replace (Termasuk Regex untuk VS Code)** agar saya bisa merombak RATUSAN baris kode di seluruh folder `app/views/`, `app/controllers/`, dan `public/` hanya dalam 1 menit.

⛔ ATURAN KETAT:
- Helper harus menggunakan `isset()`, `rtrim()`, dan `ltrim()` untuk memastikan TIDAK ADA double slash (`//`) dan TIDAK ADA missing slash, apapun kondisi `BASE_URL` di config.
- Kompatibel dengan PHP 5.6 hingga 8.3.

🎯 OUTPUT YANG HARUS ANDA BERIKAN:

### BAGIAN 1: Kode Helper Global
Berikan kode PHP untuk ditambahkan di `config/config.php` (atau `app/core/Helpers.php`).
- Fungsi `url($path = '')`: Mengamankan pemanggilan URL di HTML (href, src, action).
- Fungsi `redirect($path = '')`: Mengamankan `header('Location: ...')` di Controller.

### BAGIAN 2: Panduan Massal "Find & Replace" untuk VS Code
Berikan daftar pola pencarian (Search) dan pengganti (Replace) yang harus saya ketik di VS Code (Ctrl+Shift+H) untuk merombak seluruh file View (HTML) dan Controller.
Sertakan pola Regex jika diperlukan untuk menangkap variasi penulisan.

Contoh kasus yang harus di-cover oleh panduan Find & Replace Anda:
1. Tag `<a href="<?php echo BASE_URL; ?>/...">`
2. Tag `<form action="<?php echo BASE_URL; ?>/...">`
3. Tag `<link href="<?php echo BASE_URL; ?>/assets/...">`
4. Tag `<script src="<?php echo BASE_URL; ?>/assets/...">`
5. Tag `<img src="<?php echo BASE_URL; ?>/...">`
6. Pemanggilan di Controller: `header('Location: ' . BASE_URL . '/...');`

### BAGIAN 3: Checklist Audit untuk Junior Programmer
Berikan checklist 5 poin tempat-tempat "tersembunyi" yang sering lupa diperbaiki oleh junior programmer terkait URL (misalnya: di dalam file JavaScript, di dalam pagination, atau di form CSRF).

FORMAT OUTPUT:
Berikan jawaban secara terstruktur, praktis, dan langsung bisa saya praktekan di Text Editor saya untuk membersihkan seluruh proyek dari masalah Double-Slash ini.
