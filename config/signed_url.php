<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Signed URL Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk fitur signed URL login
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Expiry Times
    |--------------------------------------------------------------------------
    |
    | Waktu kadaluarsa default untuk signed URL dalam hari
    |
    */
    'expiry' => [
        'default' => env('SIGNED_URL_DEFAULT_EXPIRY', 3), // 3 hari untuk login umum
        'pembayaran' => env('SIGNED_URL_PEMBAYARAN_EXPIRY', 1), // 1 hari untuk pembayaran
        'admin' => env('SIGNED_URL_ADMIN_EXPIRY', 7), // 7 hari untuk admin
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Batasan rate limiting untuk mencegah abuse
    |
    */
    'rate_limiting' => [
        'login_attempts_per_hour' => env('SIGNED_URL_LOGIN_ATTEMPTS_PER_HOUR', 10),
        'create_attempts_per_hour' => env('SIGNED_URL_CREATE_ATTEMPTS_PER_HOUR', 5),
        'pembayaran_attempts_per_hour' => env('SIGNED_URL_PEMBAYARAN_ATTEMPTS_PER_HOUR', 3),
        'whatsapp_attempts_per_hour' => env('SIGNED_URL_WHATSAPP_ATTEMPTS_PER_HOUR', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Domains
    |--------------------------------------------------------------------------
    |
    | Domain yang diizinkan untuk redirect URL
    |
    */
    'allowed_domains' => [
        env('APP_URL'),
        parse_url(env('APP_URL'), PHP_URL_HOST),
        // Tambahkan domain lain jika diperlukan
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan keamanan untuk signed URL
    |
    */
    'security' => [
        'require_https' => env('SIGNED_URL_REQUIRE_HTTPS', true),
        'log_all_attempts' => env('SIGNED_URL_LOG_ALL_ATTEMPTS', true),
        'log_successful_logins' => env('SIGNED_URL_LOG_SUCCESSFUL_LOGINS', true),
        'log_failed_attempts' => env('SIGNED_URL_LOG_FAILED_ATTEMPTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Integration
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk integrasi WhatsApp
    |
    */
    'whatsapp' => [
        'enabled' => env('SIGNED_URL_WHATSAPP_ENABLED', true),
        'include_security_warning' => env('SIGNED_URL_WHATSAPP_SECURITY_WARNING', true),
        'include_expiry_info' => env('SIGNED_URL_WHATSAPP_EXPIRY_INFO', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi logging untuk signed URL
    |
    */
    'logging' => [
        'channel' => env('SIGNED_URL_LOG_CHANNEL', 'daily'),
        'level' => env('SIGNED_URL_LOG_LEVEL', 'info'),
        'include_stack_trace' => env('SIGNED_URL_LOG_STACK_TRACE', false),
    ],

]; 