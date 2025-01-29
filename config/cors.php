<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini menentukan pengaturan CORS untuk aplikasi Anda.
    | Pastikan Anda menyesuaikan `allowed_origins` agar mencakup semua
    | domain frontend yang valid, termasuk localhost dan deployment URL.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Tentukan endpoint mana saja yang diizinkan untuk CORS

    'allowed_methods' => ['*'], // Mengizinkan semua metode HTTP (GET, POST, PUT, DELETE, dll.)

    'allowed_origins' => [
        'http://localhost',              // Jika frontend berjalan di localhost
        'http://127.0.0.1:5502',        // Jika menggunakan Live Server di VS Code
        'http://localhost:3000',        // Jika frontend berjalan di port 3000
        'https://proyekiii.github.io',  // URL GitHub Pages utama
        'https://proyekiii.github.io/golaris_frontend', // URL spesifik di GitHub Pages
        'http://127.0.0.1:5503',   
    ],

    'allowed_origins_patterns' => [], // Gunakan pola regex jika perlu mencocokkan banyak domain

    'allowed_headers' => ['*'], // Mengizinkan semua header yang diminta oleh frontend

    'exposed_headers' => [], // Header tambahan yang boleh diakses oleh frontend

    'max_age' => 0, // Cache waktu respons preflight dalam detik

    'supports_credentials' => true, // Izinkan penggunaan cookie atau kredensial lainnya
];
