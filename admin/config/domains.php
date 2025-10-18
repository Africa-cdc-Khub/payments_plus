<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used for links in admin emails (login, password reset, etc.)
    | Example: http://admin.cphia2025.com or http://localhost:8000/admin
    |
    */

    'admin_url' => env('APP_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Parent Application URL  
    |--------------------------------------------------------------------------
    |
    | This URL points to the main registration website.
    | Used in emails for registration links, contact info, etc.
    | Example: http://cphia2025.com or http://localhost
    |
    */

    'parent_url' => env('PARENT_APP_URL', env('APP_URL', 'http://localhost')),

    /*
    |--------------------------------------------------------------------------
    | Email Contact Information
    |--------------------------------------------------------------------------
    |
    | Default contact information used in email templates
    |
    */

    'contact' => [
        'email' => env('CONTACT_EMAIL', 'info@cphia2025.com'),
        'website' => env('PARENT_APP_URL', 'http://localhost'),
    ],

];

