<?php

namespace Cphia2025;

use PDO;

class EmailQueue
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getConnection();
    }

    /**
     * Add email to queue
     */
    public function addToQueue($toEmail, $toName, $subject, $templateName, $templateData = [], $emailType = 'registration_confirmation', $priority = 5, $scheduledAt = null)
    {
        $sql = "INSERT INTO email_queue (to_email, to_name, subject, template_name, template_data, email_type, priority, scheduled_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $scheduledAt = $scheduledAt ?: date('Y-m-d H:i:s');
        
        return $stmt->execute([
            $toEmail,
            $toName,
            $subject,
            $templateName,
            json_encode($templateData),
            $emailType,
            $priority,
            $scheduledAt
        ]);
    }

    /**
     * Get pending emails for processing
     */
    public function getPendingEmails($limit = 50)
    {
        $sql = "SELECT * FROM email_queue 
                WHERE status = 'pending' 
                AND scheduled_at <= NOW() 
                AND attempts < max_attempts
                ORDER BY priority ASC, created_at ASC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark email as processing
     */
    public function markAsProcessing($id)
    {
        $sql = "UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Mark email as sent
     */
    public function markAsSent($id)
    {
        $sql = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed($id, $errorMessage = null)
    {
        $sql = "UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$errorMessage, $id]);
    }

    /**
     * Reset failed emails for retry
     */
    public function resetFailedEmails($olderThanHours = 24)
    {
        $sql = "UPDATE email_queue 
                SET status = 'pending', attempts = 0, error_message = NULL 
                WHERE status = 'failed' 
                AND attempts < max_attempts 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$olderThanHours]);
    }

    /**
     * Get email statistics
     */
    public function getStats()
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    email_type,
                    DATE(created_at) as date
                FROM email_queue 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY status, email_type, DATE(created_at)
                ORDER BY date DESC, status, email_type";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clean old sent emails (older than 30 days)
     */
    public function cleanOldEmails($days = 30)
    {
        $sql = "DELETE FROM email_queue 
                WHERE status = 'sent' 
                AND sent_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$days]);
    }

    /**
     * Add reminder emails for pending payments
     */
    public function addPaymentReminders()
    {
        // Get registrations with pending payments older than 24 hours
        $sql = "SELECT r.*, u.first_name, u.last_name, u.email, p.name as package_name, p.price
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                JOIN packages p ON r.package_id = p.id
                WHERE r.status = 'pending'
                AND r.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND r.id NOT IN (
                    SELECT DISTINCT registration_id 
                    FROM email_queue 
                    WHERE email_type = 'reminder' 
                    AND status IN ('pending', 'processing', 'sent')
                    AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $added = 0;
        foreach ($registrations as $registration) {
            $paymentToken = generatePaymentToken($registration['id']);
            $baseUrl = APP_URL . dirname($_SERVER['PHP_SELF']);
            $paymentLink = $baseUrl . "/checkout.php?token=" . $paymentToken;
            
            $templateData = [
                'user_name' => $registration['first_name'] . ' ' . $registration['last_name'],
                'registration_id' => $registration['id'],
                'package_name' => $registration['package_name'],
                'amount' => $registration['total_amount'],
                'payment_link' => $paymentLink,
                'conference_name' => CONFERENCE_NAME,
                'conference_short_name' => CONFERENCE_SHORT_NAME,
                'conference_dates' => CONFERENCE_DATES,
                'conference_location' => CONFERENCE_LOCATION,
                'conference_venue' => CONFERENCE_VENUE,
                'logo_url' => EMAIL_LOGO_URL
            ];
            
            $this->addToQueue(
                $registration['email'],
                $registration['first_name'] . ' ' . $registration['last_name'],
                CONFERENCE_SHORT_NAME . " - Payment Reminder #" . $registration['id'],
                'payment_reminder',
                $templateData,
                'reminder',
                3, // Higher priority for reminders
                date('Y-m-d H:i:s', strtotime('+1 hour')) // Send in 1 hour
            );
            
            $added++;
        }
        
        return $added;
    }

    /**
     * Add admin reminder emails
     */
    public function addAdminReminders()
    {
        // Get pending registrations for admin notification
        $sql = "SELECT COUNT(*) as count
                FROM registrations 
                WHERE status = 'pending'
                AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $templateData = [
                'pending_count' => $result['count'],
                'conference_name' => CONFERENCE_NAME,
                'conference_short_name' => CONFERENCE_SHORT_NAME,
                'admin_name' => ADMIN_NAME,
                'logo_url' => EMAIL_LOGO_URL
            ];
            
            return $this->addToQueue(
                ADMIN_EMAIL,
                ADMIN_NAME,
                CONFERENCE_SHORT_NAME . " - Daily Reminder: " . $result['count'] . " Pending Registrations",
                'admin_daily_reminder',
                $templateData,
                'reminder',
                2, // High priority for admin reminders
                date('Y-m-d H:i:s', strtotime('+30 minutes')) // Send in 30 minutes
            );
        }
        
        return false;
    }
}
