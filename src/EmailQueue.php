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
            // For now, send emails immediately instead of queuing
            // This maintains compatibility while using the existing EmailService
            return $this->sendEmailImmediately($toEmail, $toName, $subject, $templateName, $templateData, $emailType);
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

            // Map email types to EmailService methods
            switch ($emailType) {
                case 'registration_confirmation':
                    return $this->emailService->sendRegistrationConfirmation(
                        $toEmail,
                        $toName,
                        $templateData['registration_id'] ?? '',
                        $templateData['package_name'] ?? '',
                        $templateData['amount'] ?? 0,
                        $templateData['participants'] ?? []
                    );

                case 'payment_link':
                    return $this->emailService->sendPaymentLink(
                        $toEmail,
                        $toName,
                        $templateData['registration_id'] ?? '',
                        $templateData['amount'] ?? 0,
                        $templateData['payment_link'] ?? ''
                    );

                case 'payment_confirmation':
                    return $this->emailService->sendPaymentConfirmation(
                        $toEmail,
                        $toName,
                        $templateData['registration_id'] ?? '',
                        $templateData['amount'] ?? 0,
                        $templateData['transaction_id'] ?? '',
                        $templateData['participants'] ?? []
                    );

                case 'admin_registration':
                    return $this->emailService->sendAdminRegistrationNotification(
                        $templateData['registration_id'] ?? '',
                        $templateData['user_name'] ?? '',
                        $templateData['user_email'] ?? '',
                        $templateData['package_name'] ?? '',
                        $templateData['amount'] ?? 0,
                        $templateData['registration_type'] ?? 'individual',
                        $templateData['participants'] ?? []
                    );

                case 'admin_payment':
                    return $this->emailService->sendAdminPaymentNotification(
                        $templateData['registration_id'] ?? '',
                        $templateData['user_name'] ?? '',
                        $templateData['user_email'] ?? '',
                        $templateData['amount'] ?? 0,
                        $templateData['transaction_id'] ?? ''
                    );

                default:
                    // Fallback to generic email sending
                    $htmlContent = $this->renderTemplate($templateName, $templateData);
                    return $this->emailService->sendEmail($toEmail, $subject, $htmlContent);
            }
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
        // Simple fallback email sending
        $message = "Hello {$toName},\n\n";
        $message .= "This is a test email from the CPHIA 2025 Registration System.\n\n";
        $message .= "Subject: {$subject}\n";
        $message .= "Registration ID: " . ($templateData['registration_id'] ?? 'N/A') . "\n";
        $message .= "Amount: $" . ($templateData['amount'] ?? '0') . "\n\n";
        $message .= "Best regards,\nCPHIA 2025 Team";
        
        $headers = "From: noreply@cphia2025.com\r\n";
        $headers .= "Reply-To: noreply@cphia2025.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return mail($toEmail, $subject, $message, $headers);
    }

    /**
     * Render template with data
     */
    private function renderTemplate($templateName, $data)
    {
        // Simple template rendering - you can enhance this
        $template = $this->getDefaultTemplate($templateName);
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->formatArray($value);
            }
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
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
            'admin_payment_notification' => $this->getDefaultAdminPaymentTemplate()
        ];

        return $templates[$templateName] ?? '<p>Email template not found: ' . $templateName . '</p>';
    }

    /**
     * Add payment reminders to queue
     */
    public function addPaymentReminders()
    {
        // This would typically query the database for pending payments
        // For now, return 0 to indicate no reminders added
        return 0;
    }

    /**
     * Add admin reminders to queue
     */
    public function addAdminReminders()
    {
        // This would typically check for admin notifications needed
        // For now, return false to indicate no reminders needed
        return false;
    }

    /**
     * Reset failed emails for retry
     */
    public function resetFailedEmails($hours = 24)
    {
        // This would typically reset failed emails in the database
        // For now, return 0 to indicate no emails reset
        return 0;
    }

    /**
     * Get email queue statistics
     */
    public function getStats()
    {
        // This would typically return statistics from the database
        // For now, return empty array
        return [];
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
}
