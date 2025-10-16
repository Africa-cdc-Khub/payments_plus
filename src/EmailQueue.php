<?php

namespace Cphia2025;

use Cphia2025\EmailService;

/**
 * Email Queue Class
 * Handles queuing and processing of emails using the EmailService
 */
class EmailQueue
{
    private $emailService;
    private $pdo;

    public function __construct()
    {
        // Initialize EmailService with error handling
        try {
            $this->emailService = new EmailService();
        } catch (Exception $e) {
            error_log("EmailService initialization failed: " . $e->getMessage());
            $this->emailService = null;
        }
        $this->pdo = $this->getConnection();
    }

    /**
     * Get database connection
     */
    private function getConnection()
    {
        require_once __DIR__ . '/../db_connector.php';
        return getConnection();
    }

    /**
     * Add email to queue
     */
    public function addToQueue($toEmail, $toName, $subject, $templateName, $templateData, $emailType, $priority = 5)
    {
        try {
            // Add email to queue instead of sending immediately
            $stmt = $this->pdo->prepare("
                INSERT INTO email_queue 
                (to_email, to_name, subject, template_name, template_data, email_type, priority, status, attempts, max_attempts, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 0, 3, NOW(), NOW())
            ");
            
            $result = $stmt->execute([
                $toEmail,
                $toName,
                $subject,
                $templateName,
                json_encode($templateData),
                $emailType,
                $priority
            ]);
            
            if ($result) {
                $emailId = $this->pdo->lastInsertId();
                error_log("Email queued successfully - ID: $emailId, To: $toEmail, Subject: $subject");
                return $emailId;
            } else {
                error_log("Failed to queue email - To: $toEmail, Subject: $subject");
                return false;
            }
        } catch (Exception $e) {
            error_log("EmailQueue::addToQueue error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email immediately using EmailService
     */
    private function sendEmailImmediately($toEmail, $toName, $subject, $templateName, $templateData, $emailType)
    {
        try {
            // If EmailService is not available, fall back to simple email
            if (!$this->emailService) {
                return $this->sendSimpleEmail($toEmail, $toName, $subject, $templateData);
            }

            // Always use template rendering for consistent variable replacement
            $htmlContent = $this->renderTemplate($templateName, $templateData);
            return $this->emailService->sendEmail($toEmail, $subject, $htmlContent, true);
        } catch (Exception $e) {
            error_log("EmailQueue::sendEmailImmediately error: " . $e->getMessage());
            // Fall back to simple email
            return $this->sendSimpleEmail($toEmail, $toName, $subject, $templateData);
        }
    }

    /**
     * Send simple email as fallback
     */
    private function sendSimpleEmail($toEmail, $toName, $subject, $templateData)
    {
        // Try to render the template first
        $templateName = $templateData['_template_name'] ?? 'individual_receipt';
        $htmlContent = $this->renderTemplate($templateName, $templateData);
        
        if ($htmlContent && strlen($htmlContent) > 100) {
            // Send HTML email with template
            $headers = "From: " . ($templateData['mail_from_address'] ?? 'noreply@cphia2025.com') . "\r\n";
            $headers .= "Reply-To: " . ($templateData['mail_from_address'] ?? 'noreply@cphia2025.com') . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            
            error_log("Sending HTML email to: $toEmail with subject: $subject");
            $result = mail($toEmail, $subject, $htmlContent, $headers);
            error_log("Mail result: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        } else {
            // Fallback to simple text email
            $message = "Hello {$toName},\n\n";
            $message .= "This is a test email from the CPHIA 2025 Registration System.\n\n";
            $message .= "Subject: {$subject}\n";
            $message .= "Registration ID: " . ($templateData['registration_id'] ?? 'N/A') . "\n";
            $message .= "Amount: " . ($templateData['total_amount'] ?? '$0') . "\n\n";
            $message .= "Best regards,\nCPHIA 2025 Team";
            
            $headers = "From: " . ($templateData['mail_from_address'] ?? 'noreply@cphia2025.com') . "\r\n";
            $headers .= "Reply-To: " . ($templateData['mail_from_address'] ?? 'noreply@cphia2025.com') . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            error_log("Sending text email to: $toEmail with subject: $subject");
            $result = mail($toEmail, $subject, $message, $headers);
            error_log("Mail result: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        }
    }

    /**
     * Render template with data
     */
    public function renderTemplate($templateName, $data)
    {
        // Load template from file first, fallback to default
        $template = $this->loadTemplateFromFile($templateName);
        if (!$template) {
            $template = $this->getDefaultTemplate($templateName);
        }
        
        // Handle conditional blocks first (like {{#institution}}...{{/institution}})
        $template = $this->processConditionalBlocks($template, $data);
        
        // Then do simple variable replacement
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->formatArray($value);
            }
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * Process conditional blocks in templates
     */
    private function processConditionalBlocks($template, $data)
    {
        // Handle {{#if field}}...{{/if}} blocks
        $ifPattern = '/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/s';
        $template = preg_replace_callback($ifPattern, function($matches) use ($data) {
            $fieldName = $matches[1];
            $content = $matches[2];
            
            // If the field exists and is not empty, show the content
            if (isset($data[$fieldName]) && !empty($data[$fieldName])) {
                return $content;
            }
            
            // Otherwise, remove the block
            return '';
        }, $template);
        
        // Handle {{#field}}...{{/field}} blocks (without if)
        $fieldPattern = '/\{\{#(\w+)\}\}(.*?)\{\{\/\1\}\}/s';
        $template = preg_replace_callback($fieldPattern, function($matches) use ($data) {
            $fieldName = $matches[1];
            $content = $matches[2];
            
            // Skip if this is already handled by if/each patterns
            if (in_array($fieldName, ['if', 'each'])) {
                return $matches[0];
            }
            
            // If the field exists and is not empty, show the content
            if (isset($data[$fieldName]) && !empty($data[$fieldName])) {
                return $content;
            }
            
            // Otherwise, remove the block
            return '';
        }, $template);
        
        // Handle {{#each field}}...{{/each}} blocks
        $eachPattern = '/\{\{#each\s+(\w+)\}\}(.*?)\{\{\/each\}\}/s';
        $template = preg_replace_callback($eachPattern, function($matches) use ($data) {
            $fieldName = $matches[1];
            $content = $matches[2];
            
            // If the field exists and is an array, process each item
            if (isset($data[$fieldName]) && is_array($data[$fieldName])) {
                $result = '';
                foreach ($data[$fieldName] as $index => $item) {
                    $itemContent = $content;
                    
                    // Replace {{@index}} with the current index
                    $itemContent = str_replace('{{@index}}', $index + 1, $itemContent);
                    
                    // Replace item fields
                    if (is_array($item)) {
                        foreach ($item as $key => $value) {
                            $itemContent = str_replace('{{' . $key . '}}', $value, $itemContent);
                        }
                    }
                    
                    $result .= $itemContent;
                }
                return $result;
            }
            
            // Otherwise, remove the block
            return '';
        }, $template);
        
        return $template;
    }

    /**
     * Load template from file
     */
    private function loadTemplateFromFile($templateName)
    {
        $templatePath = __DIR__ . '/../templates/email/' . $templateName . '.html';
        
        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }
        
        return false;
    }

    /**
     * Format array for display
     */
    private function formatArray($array)
    {
        if (empty($array)) {
            return 'None';
        }
        
        $html = '<ul>';
        foreach ($array as $item) {
            if (is_array($item)) {
                $html .= '<li>' . implode(' - ', $item) . '</li>';
            } else {
                $html .= '<li>' . $item . '</li>';
            }
        }
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Get default template content
     */
    private function getDefaultTemplate($templateName)
    {
        $templates = [
            'registration_confirmation' => $this->getDefaultRegistrationConfirmationTemplate(),
            'payment_link' => $this->getDefaultPaymentLinkTemplate(),
            'payment_confirmation' => $this->getDefaultPaymentConfirmationTemplate(),
            'admin_registration_notification' => $this->getDefaultAdminRegistrationTemplate(),
            'admin_payment_notification' => $this->getDefaultAdminPaymentTemplate(),
            'individual_receipt' => $this->getDefaultIndividualReceiptTemplate(),
            'group_receipt' => $this->getDefaultGroupReceiptTemplate()
        ];

        return $templates[$templateName] ?? '<p>Email template not found: ' . $templateName . '</p>';
    }

    /**
     * Process pending emails in the queue
     */
    public function processQueue($limit = 10)
    {
        try {
            $processed = 0;
            $failed = 0;
            
            // Get pending emails from queue (only those with 0 attempts)
            $stmt = $this->pdo->prepare("
                SELECT * FROM email_queue 
                WHERE status = 'pending' 
                AND attempts = 0
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY priority ASC, created_at ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $emails = $stmt->fetchAll();
            
            foreach ($emails as $email) {
                try {
                    // Mark as processing
                    $updateStmt = $this->pdo->prepare("
                        UPDATE email_queue 
                        SET status = 'processing', attempts = attempts + 1, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$email['id']]);
                    
                    // Decode template data
                    $templateData = json_decode($email['template_data'], true) ?: [];
                    
                    // Add template name to template data for fallback
                    $templateData['_template_name'] = $email['template_name'];
                    
                    // Send email
                    $result = $this->sendEmailImmediately(
                        $email['to_email'],
                        $email['to_name'],
                        $email['subject'],
                        $email['template_name'],
                        $templateData,
                        $email['email_type']
                    );
                    
                    if ($result) {
                        // Mark as sent
                        $updateStmt = $this->pdo->prepare("
                            UPDATE email_queue 
                            SET status = 'sent', sent_at = NOW(), updated_at = NOW()
                            WHERE id = ?
                        ");
                        $updateStmt->execute([$email['id']]);
                        $processed++;
                    } else {
                        // Mark as failed
                        $updateStmt = $this->pdo->prepare("
                            UPDATE email_queue 
                            SET status = 'failed', error_message = 'Email sending failed', updated_at = NOW()
                            WHERE id = ?
                        ");
                        $updateStmt->execute([$email['id']]);
                        $failed++;
                    }
                    
                } catch (Exception $e) {
                    // Mark as failed with error message
                    $updateStmt = $this->pdo->prepare("
                        UPDATE email_queue 
                        SET status = 'failed', error_message = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$e->getMessage(), $email['id']]);
                    $failed++;
                    error_log("EmailQueue::processQueue error for email ID {$email['id']}: " . $e->getMessage());
                }
            }
            
            return [
                'processed' => $processed,
                'failed' => $failed,
                'total' => count($emails)
            ];
            
        } catch (Exception $e) {
            error_log("EmailQueue::processQueue error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add payment reminders to queue
     */
    public function addPaymentReminders()
    {
        try {
            // Find registrations that are pending payment, have amount > 0, and created more than 24 hours ago
            // Exclude voided registrations, delegates, and approved registrations
            $stmt = $this->pdo->prepare("
                SELECT r.id, r.total_amount, r.currency, r.created_at,
                       u.first_name, u.last_name, u.email,
                       p.name as package_name
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                JOIN packages p ON r.package_id = p.id
                WHERE r.payment_status = 'pending'
                AND r.status != 'cancelled'
                AND r.status != 'approved'
                AND r.total_amount > 0
                AND r.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND r.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND r.payment_status != 'voided'
                AND LOWER(p.name) NOT LIKE '%delegate%'
                ORDER BY r.created_at ASC
            ");
            
            $stmt->execute();
            $pendingRegistrations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $remindersAdded = 0;
            
            foreach ($pendingRegistrations as $registration) {
                // Check if we already sent a reminder in the last 7 days
                $checkStmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM email_queue
                    WHERE template_name = 'payment_reminder'
                    AND JSON_EXTRACT(template_data, '$.registration_id') = ?
                    AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND status IN ('pending', 'sent')
                ");
                $checkStmt->execute([$registration['id']]);
                $existingReminder = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($existingReminder['count'] == 0) {
                    // Generate payment link through registration_lookup.php (which will redirect to payment_confirm.php)
                    $paymentLink = rtrim(APP_URL, '/') . "/registration_lookup.php?action=pay&id=" . $registration['id'];
                    
                    // Fix: Remove 262145 prefix from amount and ensure it's clean and numeric
                    $cleanAmount = str_replace('262145', '', $registration['total_amount']);
                    $cleanAmount = (float)$cleanAmount;
                    if ($cleanAmount <= 0 || $cleanAmount > 100000) {
                        error_log("ERROR: Invalid amount in EmailQueue - Registration ID: " . $registration['id'] . ", Amount: " . $registration['total_amount']);
                        continue; // Skip this registration
                    }
                    
                    $templateData = [
                        'user_name' => $registration['first_name'] . ' ' . $registration['last_name'],
                        'registration_id' => $registration['id'],
                        'package_name' => $registration['package_name'],
                        'amount' => number_format($cleanAmount, 2), // Ensure proper formatting
                        'currency' => $registration['currency'],
                        'payment_link' => $paymentLink,
                        'conference_name' => CONFERENCE_NAME,
                        'conference_short_name' => CONFERENCE_SHORT_NAME,
                        'conference_dates' => CONFERENCE_DATES,
                        'conference_location' => CONFERENCE_LOCATION,
                        'conference_venue' => CONFERENCE_VENUE,
                        'logo_url' => EMAIL_LOGO_URL,
                        'support_email' => SUPPORT_EMAIL,
                        'mail_from_address' => MAIL_FROM_ADDRESS
                    ];
                    
                    $result = $this->addToQueue(
                        $registration['email'],
                        $templateData['user_name'],
                        CONFERENCE_SHORT_NAME . " - Payment Reminder #" . $registration['id'],
                        'payment_reminder',
                        $templateData,
                        'payment_reminder',
                        3 // High priority for reminders
                    );
                    
                    if ($result) {
                        $remindersAdded++;
                    }
                }
            }
            
            return $remindersAdded;
            
        } catch (Exception $e) {
            error_log("EmailQueue::addPaymentReminders error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Add admin reminders to queue
     */
    public function addAdminReminders()
    {
        try {
            // Check if there are pending registrations that need admin attention
            // Exclude voided registrations, delegates, and approved registrations
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM registrations r
                JOIN packages p ON r.package_id = p.id
                WHERE r.payment_status = 'pending'
                AND r.status != 'cancelled'
                AND r.status != 'approved'
                AND r.total_amount > 0
                AND r.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
                AND r.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND r.payment_status != 'voided'
                AND LOWER(p.name) NOT LIKE '%delegate%'
            ");
            $stmt->execute();
            $pendingCount = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            if ($pendingCount > 0) {
                // Check if we already sent an admin reminder today
                $checkStmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM email_queue
                    WHERE template_name = 'admin_reminder'
                    AND DATE(created_at) = CURDATE()
                    AND status IN ('pending', 'sent')
                ");
                $checkStmt->execute();
                $existingReminder = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($existingReminder['count'] == 0) {
                    $templateData = [
                        'admin_name' => ADMIN_NAME,
                        'pending_count' => $pendingCount,
                        'conference_name' => CONFERENCE_NAME,
                        'conference_short_name' => CONFERENCE_SHORT_NAME,
                        'admin_dashboard_url' => rtrim(APP_URL, '/') . '/admin',
                        'logo_url' => EMAIL_LOGO_URL,
                        'mail_from_address' => MAIL_FROM_ADDRESS
                    ];
                    
                    $result = $this->addToQueue(
                        ADMIN_EMAIL,
                        ADMIN_NAME,
                        CONFERENCE_SHORT_NAME . " - Daily Admin Reminder - {$pendingCount} Pending Payments",
                        'admin_reminder',
                        $templateData,
                        'admin_reminder',
                        2 // High priority for admin notifications
                    );
                    
                    return $result;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("EmailQueue::addAdminReminders error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset failed emails for retry
     */
    public function resetFailedEmails($hours = 24)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE email_queue 
                SET status = 'pending', 
                    attempts = 0, 
                    error_message = NULL, 
                    updated_at = NOW()
                WHERE status = 'failed' 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND attempts < 3
            ");
            
            $stmt->execute([$hours]);
            $resetCount = $stmt->rowCount();
            
            return $resetCount;
            
        } catch (Exception $e) {
            error_log("EmailQueue::resetFailedEmails error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get email queue statistics
     */
    public function getStats()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    status,
                    email_type,
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM email_queue
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY status, email_type, DATE(created_at)
                ORDER BY date DESC, status, email_type
            ");
            
            $stmt->execute();
            $stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("EmailQueue::getStats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get default registration confirmation template
     */
    private function getDefaultRegistrationConfirmationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{{conference_short_name}} - Registration Confirmation</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background: #1e40af; color: white; padding: 20px; text-align: center;">
                <h1>{{conference_short_name}}</h1>
                <p>{{conference_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Registration Confirmed!</h2>
                <p>Dear {{user_name}},</p>
                <p>Thank you for registering for {{conference_short_name}}!</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Registration Details</h3>
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Package:</strong> {{package_name}}</p>
                    <p><strong>Amount:</strong> ${{amount}}</p>
                    <p><strong>Conference:</strong> {{conference_dates}} at {{conference_venue}}, {{conference_location}}</p>
                </div>
                
                <p>You will receive a payment link shortly to complete your registration.</p>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get default payment link template
     */
    private function getDefaultPaymentLinkTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{{conference_short_name}} - Payment Required</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background: #1e40af; color: white; padding: 20px; text-align: center;">
                <h1>{{conference_short_name}}</h1>
                <p>{{conference_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Payment Required</h2>
                <p>Dear {{user_name}},</p>
                <p>Your registration for {{conference_short_name}} is confirmed, but payment is required to complete your registration.</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Payment Details</h3>
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Amount to Pay:</strong> ${{amount}}</p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{payment_link}}" style="background: #059669; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">Complete Payment</a>
                </div>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get default payment confirmation template
     */
    private function getDefaultPaymentConfirmationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{{conference_short_name}} - Payment Confirmed</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background: #059669; color: white; padding: 20px; text-align: center;">
                <h1>Payment Confirmed!</h1>
                <p>{{conference_short_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Thank You for Your Payment</h2>
                <p>Dear {{user_name}},</p>
                <p>Your payment for {{conference_short_name}} has been successfully processed!</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Payment Details</h3>
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Amount Paid:</strong> ${{amount}}</p>
                    <p><strong>Transaction ID:</strong> {{transaction_id}}</p>
                </div>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get default admin registration template
     */
    private function getDefaultAdminRegistrationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Registration - {{conference_short_name}}</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background: #dc2626; color: white; padding: 20px; text-align: center;">
                <h1>New Registration Alert</h1>
                <p>{{conference_short_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Registration Details</h2>
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Registrant:</strong> {{user_name}} ({{user_email}})</p>
                    <p><strong>Package:</strong> {{package_name}}</p>
                    <p><strong>Amount:</strong> ${{amount}}</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Get default admin payment template
     */
    private function getDefaultAdminPaymentTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Payment Confirmed - {{conference_short_name}}</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background: #059669; color: white; padding: 20px; text-align: center;">
                <h1>Payment Confirmed</h1>
                <p>{{conference_short_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Payment Details</h2>
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Registrant:</strong> {{user_name}} ({{user_email}})</p>
                    <p><strong>Amount:</strong> ${{amount}}</p>
                    <p><strong>Transaction ID:</strong> {{transaction_id}}</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Get default individual receipt template
     */
    private function getDefaultIndividualReceiptTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{{conference_short_name}} - Registration Receipt</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
            <div style="background: #059669; color: white; padding: 20px; text-align: center;">
                <h1>{{conference_short_name}}</h1>
                <p>{{conference_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Registration Receipt</h2>
                <p>Dear {{participant_name}},</p>
                <p>Thank you for your registration! Here are your receipt details:</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Registration Details</h3>
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Package:</strong> {{package_name}}</p>
                    <p><strong>Amount Paid:</strong> {{total_amount}}</p>
                    <p><strong>Payment Date:</strong> {{payment_date}}</p>
                    <p><strong>Organization:</strong> {{organization}}</p>
                    <p><strong>Nationality:</strong> {{nationality}}</p>
                    <p><strong>Phone:</strong> {{phone}}</p>
                </div>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Conference Details</h3>
                    <p><strong>Event:</strong> {{conference_name}}</p>
                    <p><strong>Dates:</strong> {{conference_dates}}</p>
                    <p><strong>Location:</strong> {{conference_location}}</p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <p><strong>QR Code for Check-in:</strong></p>
                    <div style="background: white; padding: 20px; border: 1px solid #ddd; display: inline-block;">
                        <p>QR Code will be displayed here</p>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get default group receipt template
     */
    private function getDefaultGroupReceiptTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>{{conference_short_name}} - Group Registration Receipts</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto;">
            <div style="background: #059669; color: white; padding: 20px; text-align: center;">
                <h1>{{conference_short_name}}</h1>
                <p>{{conference_name}}</p>
            </div>
            
            <div style="padding: 20px; background: #f8fafc;">
                <h2>Group Registration Receipts</h2>
                <p>Dear {{focal_person_name}},</p>
                <p>Thank you for your group registration! Here are the receipt details for all participants:</p>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Registration Summary</h3>
                    <p><strong>Registration ID:</strong> #{{registration_id}}</p>
                    <p><strong>Package:</strong> {{package_name}}</p>
                    <p><strong>Total Amount:</strong> {{total_amount}}</p>
                    <p><strong>Payment Date:</strong> {{payment_date}}</p>
                    <p><strong>Focal Person:</strong> {{focal_person_name}} ({{focal_person_email}})</p>
                </div>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Conference Details</h3>
                    <p><strong>Event:</strong> {{conference_name}}</p>
                    <p><strong>Dates:</strong> {{conference_dates}}</p>
                    <p><strong>Location:</strong> {{conference_location}}</p>
                </div>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Participants ({{participants_count}})</h3>
                    {{participants_list}}
                </div>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>QR Codes for Check-in</h3>
                    <p>Each participant has a unique QR code for conference check-in. Please save these images or print them for easy access during the event.</p>
                    {{qr_codes_display}}
                </div>
                
                <div style="background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0ea5e9;">
                    <h4 style="margin-top: 0; color: #0c4a6e;">Important Information</h4>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Each participant must present their QR code at the conference check-in desk</li>
                        <li>QR codes are unique to each participant and cannot be shared</li>
                        <li>Please arrive at least 30 minutes before your first session</li>
                        <li>If you have any questions, contact us at {{support_email}}</li>
                    </ul>
                </div>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
                <p style="font-size: 12px; margin-top: 10px;">
                    For support, email: {{support_email}}<br>
                    Visit our website for more information about the conference
                </p>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Clean up the email queue by removing old processed emails
     * 
     * @param int $daysOld Number of days old emails to keep (default: 7)
     * @return array Statistics about cleanup
     */
    public function cleanupQueue($daysOld = 7)
    {
        try {
            // Count emails to be deleted
            $countStmt = $this->pdo->prepare("
                SELECT COUNT(*) as count 
                FROM email_queue 
                WHERE status IN ('sent', 'failed') 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $countStmt->execute([$daysOld]);
            $countResult = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $emailsToDelete = $countResult['count'];
            
            if ($emailsToDelete > 0) {
                // Delete old processed emails
                $deleteStmt = $this->pdo->prepare("
                    DELETE FROM email_queue 
                    WHERE status IN ('sent', 'failed') 
                    AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                ");
                $deleteStmt->execute([$daysOld]);
                
                return [
                    'deleted' => $emailsToDelete,
                    'days_old' => $daysOld
                ];
            }
            
            return [
                'deleted' => 0,
                'days_old' => $daysOld
            ];
            
        } catch (Exception $e) {
            error_log("EmailQueue::cleanupQueue error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get queue statistics including attempts breakdown
     * 
     * @return array Queue statistics
     */
    public function getDetailedStats()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    status,
                    attempts,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM email_queue 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY status, attempts, DATE(created_at)
                ORDER BY date DESC, status, attempts
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("EmailQueue::getDetailedStats error: " . $e->getMessage());
            return false;
        }
    }
}
