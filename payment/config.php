<?php
require_once __DIR__ . '/../bootstrap.php';

define('MERCHANT_ID', CYBERSOURCE_MERCHANT_ID);
define('PROFILE_ID',  CYBERSOURCE_PROFILE_ID);
define('ACCESS_KEY',  CYBERSOURCE_ACCESS_KEY);
define('SECRET_KEY',  CYBERSOURCE_SECRET_KEY);

// DF TEST: 1snn5n9w, LIVE: k8vif92e 
define('DF_ORG_ID', CYBERSOURCE_DF_ORG_ID);

// PAYMENT URL
define('CYBS_BASE_URL', CYBERSOURCE_BASE_URL);

define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
//define('PAYMENT_URL', '/sa-sop/debug.php');



define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// Load environment variables
//require_once __DIR__ . '/../bootstrap.php';

// CyberSource Configuration from Environment Variables
// define('MERCHANT_ID', CYBERSOURCE_MERCHANT_ID);
// define('PROFILE_ID', CYBERSOURCE_PROFILE_ID);
// define('ACCESS_KEY', CYBERSOURCE_ACCESS_KEY);
// define('SECRET_KEY', CYBERSOURCE_SECRET_KEY);

// // Device Fingerprint Organization ID
// define('DF_ORG_ID', CYBERSOURCE_DF_ORG_ID);

// // Payment URLs
// define('CYBS_BASE_URL', CYBERSOURCE_BASE_URL);
// define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
// define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
// define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// // Debug mode (for testing)
// if (APP_DEBUG) {
//     // define('PAYMENT_URL', '/sa-sop/debug.php');
// }