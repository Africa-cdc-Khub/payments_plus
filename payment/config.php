<?php

define('MERCHANT_ID', 'ECOEGH0002');
define('PROFILE_ID',  'B001FD3F-4723-48D6-B139-87B8552DE9B1');
define('ACCESS_KEY',  '8955d2ab178337a88eba9c5044c16c1d');
define('SECRET_KEY',  'caa42c9a602b41e0a661f4bda2b042ae568a1ca8d7b343d78f148a959cbf1cade402a438de294e1795457092b54e056c20f8e19d6f4444d59c5d365d2dad3fb17ce885074c3248608a2e6cf2f100d0bbb31264bbfc89454aaf6f6c730bc04563ca49a64fac1b44b2aad0e9461210b0e1830de4b3ebce470c8ff084e7cf96d053');

// DF TEST: 1snn5n9w, LIVE: k8vif92e 
define('DF_ORG_ID', '1snn5n9w');

// PAYMENT URL
define('CYBS_BASE_URL', 'https://testsecureacceptance.cybersource.com');

define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
//define('PAYMENT_URL', '/sa-sop/debug.php');



define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// EOF