<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exchange Email Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Exchange Email Service using Microsoft Graph API
    | with support for multiple authentication methods and background refresh
    |
    */

    // Microsoft Graph OAuth Configuration
    'tenant_id' => defined('EXCHANGE_TENANT_ID') ? EXCHANGE_TENANT_ID : (getenv('EXCHANGE_TENANT_ID') ?: ''),
    'client_id' => defined('EXCHANGE_CLIENT_ID') ? EXCHANGE_CLIENT_ID : (getenv('EXCHANGE_CLIENT_ID') ?: ''),
    'client_secret' => defined('EXCHANGE_CLIENT_SECRET') ? EXCHANGE_CLIENT_SECRET : (getenv('EXCHANGE_CLIENT_SECRET') ?: ''),
    'redirect_uri' => defined('EXCHANGE_REDIRECT_URI') ? EXCHANGE_REDIRECT_URI : (getenv('EXCHANGE_REDIRECT_URI') ?: 'http://localhost:8000/oauth/callback'),
    'scope' => defined('EXCHANGE_SCOPE') ? EXCHANGE_SCOPE : (getenv('EXCHANGE_SCOPE') ?: 'https://graph.microsoft.com/.default'),

    // Authentication Method
    // Options: 'authorization_code', 'client_credentials'
    'auth_method' => defined('EXCHANGE_AUTH_METHOD') ? EXCHANGE_AUTH_METHOD : (getenv('EXCHANGE_AUTH_METHOD') ?: 'client_credentials'),

    // Email Configuration
    'from_email' => defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : (getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com'),
    'from_name' => defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (getenv('MAIL_FROM_NAME') ?: 'Exchange Email Service'),

    // Token Storage Configuration (file-based)
    'token_storage' => [
        'type' => 'file',
        'path' => 'tokens/oauth_tokens.json',
        'permissions' => 0644,
    ],

    // OAuth Configuration
    'oauth' => [
        'authorize_url' => 'https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/authorize',
        'token_url' => 'https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/token',
        'graph_url' => 'https://graph.microsoft.com/v1.0',
    ],

    // Background Refresh Configuration
    'refresh' => [
        'enabled' => filter_var(getenv('EXCHANGE_REFRESH_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'interval' => (int)(getenv('EXCHANGE_REFRESH_INTERVAL') ?: '30'), // minutes
        'buffer' => (int)(getenv('EXCHANGE_REFRESH_BUFFER') ?: '5'), // minutes before expiry
        'log_file' => getenv('EXCHANGE_LOG_FILE') ?: 'logs/exchange-email.log',
        'max_log_size' => (int)(getenv('EXCHANGE_MAX_LOG_SIZE') ?: '1048576'), // 1MB
    ],

    // Email Templates
    'templates' => [
        'welcome' => 'welcome',
        'notification' => 'notification',
        'confirmation' => 'confirmation',
        'test' => 'test',
    ],

    // Default Settings
    'defaults' => [
        'is_html' => true,
        'timeout' => 30,
        'retry_attempts' => 3,
        'debug' => filter_var(getenv('EXCHANGE_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],

    // Production Settings
    'production' => [
        'enabled' => filter_var(getenv('EXCHANGE_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'monitoring' => filter_var(getenv('EXCHANGE_MONITORING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'health_check' => filter_var(getenv('EXCHANGE_HEALTH_CHECK') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    // Rate Limiting
    'rate_limiting' => [
        'enabled' => filter_var(getenv('EXCHANGE_RATE_LIMITING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'max_emails_per_minute' => (int)(getenv('EXCHANGE_MAX_EMAILS_PER_MINUTE') ?: '60'),
        'max_emails_per_hour' => (int)(getenv('EXCHANGE_MAX_EMAILS_PER_HOUR') ?: '1000'),
    ],

    // Error Handling
    'error_handling' => [
        'retry_on_failure' => filter_var(getenv('EXCHANGE_RETRY_ON_FAILURE') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'max_retries' => (int)(getenv('EXCHANGE_MAX_RETRIES') ?: '3'),
        'retry_delay' => (int)(getenv('EXCHANGE_RETRY_DELAY') ?: '5'), // seconds
        'fallback_method' => getenv('EXCHANGE_FALLBACK_METHOD') ?: 'smtp',
    ],

    // Logging Configuration
    'logging' => [
        'enabled' => filter_var(getenv('EXCHANGE_LOGGING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'level' => getenv('EXCHANGE_LOG_LEVEL') ?: 'info',
        'channels' => [
            'file' => [
                'driver' => 'single',
                'path' => 'logs/exchange-email.log',
                'level' => 'debug',
            ],
            'daily' => [
                'driver' => 'daily',
                'path' => 'logs/exchange-email.log',
                'level' => 'debug',
                'days' => 14,
            ],
        ],
    ],

    // Monitoring Configuration
    'monitoring' => [
        'enabled' => filter_var(getenv('EXCHANGE_MONITORING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'health_check_interval' => (int)(getenv('EXCHANGE_HEALTH_CHECK_INTERVAL') ?: '5'), // minutes
        'alert_on_failure' => filter_var(getenv('EXCHANGE_ALERT_ON_FAILURE') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'alert_email' => getenv('EXCHANGE_ALERT_EMAIL') ?: '',
        'metrics' => [
            'track_send_attempts' => true,
            'track_success_rate' => true,
            'track_response_times' => true,
            'track_token_refreshes' => true,
        ],
    ],

    // Security Configuration
    'security' => [
        'encrypt_tokens' => filter_var(getenv('EXCHANGE_ENCRYPT_TOKENS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'token_rotation' => filter_var(getenv('EXCHANGE_TOKEN_ROTATION') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'audit_logging' => filter_var(getenv('EXCHANGE_AUDIT_LOGGING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'ip_whitelist' => getenv('EXCHANGE_IP_WHITELIST') ?: '',
    ],

    // Performance Configuration
    'performance' => [
        'connection_pooling' => filter_var(getenv('EXCHANGE_CONNECTION_POOLING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'async_sending' => filter_var(getenv('EXCHANGE_ASYNC_SENDING') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'batch_processing' => filter_var(getenv('EXCHANGE_BATCH_PROCESSING') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'cache_tokens' => filter_var(getenv('EXCHANGE_CACHE_TOKENS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],
];
