<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Microsoft Exchange / Office 365 Email Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for sending emails via Microsoft Graph API with OAuth 2.0
    | Supports both Authorization Code Flow and Client Credentials Flow
    |
    */

    // Azure AD / Microsoft 365 Configuration
    'tenant_id' => env('EXCHANGE_TENANT_ID'),
    'client_id' => env('EXCHANGE_CLIENT_ID'),
    'client_secret' => env('EXCHANGE_CLIENT_SECRET'),
    'redirect_uri' => env('EXCHANGE_REDIRECT_URI', env('APP_URL') . '/exchange-email/oauth/callback'),

    // OAuth Configuration
    'scope' => env('EXCHANGE_SCOPE', 'https://graph.microsoft.com/Mail.Send'),
    
    /**
     * Authentication Method:
     * - 'authorization_code': User-based OAuth flow (requires user interaction)
     * - 'client_credentials': App-only flow (automatic, requires app permissions)
     */
    'auth_method' => env('EXCHANGE_AUTH_METHOD', 'client_credentials'),

    // Email Configuration
    'from_email' => env('MAIL_FROM_ADDRESS'),
    'from_name' => env('MAIL_FROM_NAME', 'CPHIA 2025'),

    // Debugging
    'debug' => env('EXCHANGE_DEBUG', false),
    'log_file' => storage_path('logs/exchange-email.log'),
    'max_log_size' => env('EXCHANGE_MAX_LOG_SIZE', 10485760), // 10MB

    // Token Storage
    'token_table' => 'oauth_tokens',
    'token_refresh_interval' => env('EXCHANGE_TOKEN_REFRESH_INTERVAL', 3000), // 50 minutes (tokens expire in 60)

    // Email Queue Configuration
    'queue_enabled' => env('EXCHANGE_QUEUE_ENABLED', true),
    'queue_table' => 'email_queue',
    'max_attempts' => env('EXCHANGE_MAX_ATTEMPTS', 3),
    'retry_delay' => env('EXCHANGE_RETRY_DELAY', 300), // 5 minutes

    // Rate Limiting
    'rate_limit' => env('EXCHANGE_RATE_LIMIT', 30), // Max emails per minute
    'batch_size' => env('EXCHANGE_BATCH_SIZE', 10), // Emails per batch

];

