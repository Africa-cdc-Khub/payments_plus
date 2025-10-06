<?php

// define('MERCHANT_ID', 'ECOEGH0002');
// define('PROFILE_ID',  'B001FD3F-4723-48D6-B139-87B8552DE9B1');
// define('ACCESS_KEY',  '8955d2ab178337a88eba9c5044c16c1d');
// define('SECRET_KEY',  'caa42c9a602b41e0a661f4bda2b042ae568a1ca8d7b343d78f148a959cbf1cade402a438de294e1795457092b54e056c20f8e19d6f4444d59c5d365d2dad3fb17ce885074c3248608a2e6cf2f100d0bbb31264bbfc89454aaf6f6c730bc04563ca49a64fac1b44b2aad0e9461210b0e1830de4b3ebce470c8ff084e7cf96d053');

// // DF TEST: 1snn5n9w, LIVE: k8vif92e 
// define('DF_ORG_ID', '1snn5n9w');

// // PAYMENT URL
// define('CYBS_BASE_URL', 'https://testsecureacceptance.cybersource.com');

// define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
// //define('PAYMENT_URL', '/sa-sop/debug.php');



// define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
// define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// Load environment variables
require_once __DIR__ . '/../bootstrap.php';

// CyberSource Configuration from Environment Variables
define('MERCHANT_ID', CYBERSOURCE_MERCHANT_ID);
define('PROFILE_ID', CYBERSOURCE_PROFILE_ID);
define('ACCESS_KEY', CYBERSOURCE_ACCESS_KEY);
define('SECRET_KEY', CYBERSOURCE_SECRET_KEY);

// Device Fingerprint Organization ID
define('DF_ORG_ID', CYBERSOURCE_DF_ORG_ID);

// Payment URLs
define('CYBS_BASE_URL', CYBERSOURCE_BASE_URL);
define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// Debug mode (for testing)
if (APP_DEBUG) {
    // define('PAYMENT_URL', '/sa-sop/debug.php');
}