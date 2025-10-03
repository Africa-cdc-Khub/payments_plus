<?php
/**
 * CyberSource Response Handler
 * Redirects to our custom payment response handler
 */

// Redirect to our custom response handler
header('Location: ../payment_response.php?' . http_build_query($_REQUEST));
exit;