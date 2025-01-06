<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'], // Bisa menyesuaikan jika hanya ingin mengizinkan metode tertentu seperti ['GET', 'POST', 'PUT']

'allowed_origins' => [
    'http://localhost', // Jika frontend Anda berjalan di localhost
    'https://proyekiii.github.io', // URL frontend GitHub Pages Anda
    'https://proyekiii.github.io/golaris_frontend', // Pastikan URL ini benar sesuai dengan lokasi frontend Anda
],

'allowed_origins_patterns' => [],

'allowed_headers' => ['*'], // Mengizinkan semua header

'exposed_headers' => [],

'max_age' => 0,

'supports_credentials' => true, // Aktifkan jika perlu mengirimkan cookie atau token


];
