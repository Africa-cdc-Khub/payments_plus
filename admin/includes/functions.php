<?php
/**
 * Admin Panel Helper Functions
 */

require_once __DIR__ . '/../../db_connector.php';

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    $pdo = getConnection();
    
    $stats = [];
    
    // Total registrations
    $stats['total_registrations'] = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    
    // Total revenue (confirmed payments)
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'completed'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
    
    // Pending payments
    $stats['pending_payments'] = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetchColumn();
    
    // Failed payments
    $stats['failed_payments'] = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'failed'")->fetchColumn();
    
    // Individual vs Group registrations
    $stmt = $pdo->query("SELECT registration_type, COUNT(*) as count FROM registrations GROUP BY registration_type");
    $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Package distribution
    $stmt = $pdo->query("SELECT p.type, COUNT(*) as count FROM registrations r 
                         JOIN packages p ON r.package_id = p.id 
                         GROUP BY p.type");
    $stats['by_package_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // African vs Non-African nationals (approximation based on common African countries)
    $africanCountries = ['South Africa', 'Nigeria', 'Kenya', 'Ghana', 'Egypt', 'Ethiopia', 'Tanzania', 'Uganda', 'Zimbabwe', 'Morocco', 'Algeria', 'Tunisia', 'Senegal', 'Cameroon', 'Ivory Coast', 'Angola', 'Sudan', 'Mozambique', 'Madagascar', 'Malawi', 'Zambia', 'Mali', 'Burkina Faso', 'Niger', 'Rwanda', 'Benin', 'Burundi', 'Chad', 'Guinea', 'Sierra Leone', 'Togo', 'Libya', 'Mauritania', 'Eritrea', 'Gambia', 'Botswana', 'Namibia', 'Gabon', 'Lesotho', 'Guinea-Bissau', 'Equatorial Guinea', 'Mauritius', 'Eswatini', 'Djibouti', 'Comoros', 'Cape Verde', 'Sao Tome and Principe', 'Seychelles'];
    
    $placeholders = str_repeat('?,', count($africanCountries) - 1) . '?';
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN country IN ($placeholders) THEN 1 ELSE 0 END) as african,
        SUM(CASE WHEN country NOT IN ($placeholders) OR country IS NULL THEN 1 ELSE 0 END) as non_african
        FROM users");
    $stmt->execute(array_merge($africanCountries, $africanCountries));
    $nationality_stats = $stmt->fetch();
    $stats['by_nationality'] = [
        'African' => $nationality_stats['african'] ?: 0,
        'Non-African' => $nationality_stats['non_african'] ?: 0
    ];
    
    return $stats;
}

/**
 * Get recent registrations
 */
function getRecentRegistrations($limit = 10) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.email, u.country, p.name as package_name, p.type as package_type
                          FROM registrations r
                          JOIN users u ON r.user_id = u.id
                          JOIN packages p ON r.package_id = p.id
                          ORDER BY r.created_at DESC
                          LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get registrations with filters
 */
function getRegistrations($filters = []) {
    $pdo = getConnection();
    
    $where = [];
    $params = [];
    
    if (!empty($filters['search'])) {
        $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR r.payment_reference LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    if (!empty($filters['registration_type'])) {
        $where[] = "r.registration_type = ?";
        $params[] = $filters['registration_type'];
    }
    
    if (!empty($filters['package_type'])) {
        $where[] = "p.type = ?";
        $params[] = $filters['package_type'];
    }
    
    if (!empty($filters['status'])) {
        $where[] = "r.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(r.created_at) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(r.created_at) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT r.*, u.first_name, u.last_name, u.email, u.country, u.nationality, 
            p.name as package_name, p.type as package_type, p.price as package_price
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            JOIN packages p ON r.package_id = p.id
            $whereClause
            ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get payments with filters
 */
function getPayments($filters = []) {
    $pdo = getConnection();
    
    $where = [];
    $params = [];
    
    if (!empty($filters['search'])) {
        $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR p.payment_reference LIKE ? OR p.transaction_uuid LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    if (!empty($filters['payment_status'])) {
        $where[] = "p.payment_status = ?";
        $params[] = $filters['payment_status'];
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(p.created_at) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(p.created_at) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT p.*, r.id as registration_id, u.first_name, u.last_name, u.email, 
            pkg.name as package_name
            FROM payments p
            JOIN registrations r ON p.registration_id = r.id
            JOIN users u ON r.user_id = u.id
            JOIN packages pkg ON r.package_id = pkg.id
            $whereClause
            ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Update payment status
 */
function updatePaymentStatus($paymentId, $status) {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("UPDATE payments SET payment_status = ?, payment_date = NOW() WHERE id = ?");
    $result = $stmt->execute([$status, $paymentId]);
    
    // Also update registration status if payment is completed
    if ($result && $status === 'completed') {
        $payment = $pdo->prepare("SELECT registration_id FROM payments WHERE id = ?");
        $payment->execute([$paymentId]);
        $paymentData = $payment->fetch();
        
        if ($paymentData) {
            $updateReg = $pdo->prepare("UPDATE registrations SET status = 'paid' WHERE id = ?");
            $updateReg->execute([$paymentData['registration_id']]);
        }
    }
    
    return $result;
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Get status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'warning',
        'completed' => 'success',
        'paid' => 'success',
        'failed' => 'danger',
        'cancelled' => 'secondary'
    ];
    
    return $classes[$status] ?? 'secondary';
}


