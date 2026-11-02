<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Policy Settings
    |--------------------------------------------------------------------------
    */
    'password' => [
        /*
         * Minimum password length
         */
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),

        /*
         * Password expiration in days (0 = disabled)
         */
        'expiration_days' => env('PASSWORD_EXPIRATION_DAYS', 90),

        /*
         * Number of previous passwords to remember (prevent reuse)
         */
        'history_count' => env('PASSWORD_HISTORY_COUNT', 5),

        /*
         * Require uppercase letters
         */
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),

        /*
         * Require lowercase letters
         */
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),

        /*
         * Require numbers
         */
        'require_number' => env('PASSWORD_REQUIRE_NUMBER', true),

        /*
         * Require special characters
         */
        'require_special' => env('PASSWORD_REQUIRE_SPECIAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Lockout Settings
    |--------------------------------------------------------------------------
    */
    'lockout' => [
        /*
         * Maximum login attempts before lockout
         */
        'max_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),

        /*
         * Lockout duration in minutes
         */
        'duration_minutes' => env('LOCKOUT_DURATION_MINUTES', 30),

        /*
         * Enable account lockout
         */
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security Settings
    |--------------------------------------------------------------------------
    */
    'session' => [
        /*
         * Maximum concurrent sessions per user (0 = unlimited)
         */
        'max_concurrent' => env('MAX_CONCURRENT_SESSIONS', 1),

        /*
         * Prevent concurrent sessions
         */
        'prevent_concurrent' => true,

        /*
         * Enable session fingerprinting
         */
        'fingerprint' => true,

        /*
         * Session fingerprint timeout in minutes
         */
        'fingerprint_timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        /*
         * Notify on new device login
         */
        'new_device' => env('NOTIFY_NEW_DEVICE', true),

        /*
         * Notify on new location login
         */
        'new_location' => env('NOTIFY_NEW_LOCATION', true),

        /*
         * Notify on failed login attempts
         */
        'failed_login' => env('NOTIFY_FAILED_LOGIN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        /*
         * Enable security headers middleware
         */
        'enabled' => true,

        /*
         * Content Security Policy
         * Customize based on your needs
         */
        'csp_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'",

        /*
         * HSTS max age in seconds (31536000 = 1 year)
         */
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        /*
         * Enable 2FA
         */
        'enabled' => env('2FA_ENABLED', false),

        /*
         * Force 2FA for admin users
         */
        'force_admin' => false,

        /*
         * Allow remember device
         */
        'remember_device' => true,

        /*
         * Remember device duration in days
         */
        'remember_device_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        /*
         * Enable audit logging
         */
        'enabled' => true,

        /*
         * Events to log
         */
        'events' => [
            'login',
            'logout',
            'failed_login',
            'password_changed',
            'password_reset',
            'profile_updated',
            'role_changed',
            'permission_changed',
            'export_data',
            'delete_data',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist (for admin routes)
    |--------------------------------------------------------------------------
    */
    'ip_whitelist' => [
        /*
         * Enable IP whitelist
         */
        'enabled' => false,

        /*
         * List of allowed IPs
         */
        'ips' => [
            // '192.168.1.1',
        ],
    ],
];

