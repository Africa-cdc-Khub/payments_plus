<?php

namespace Cphia2025;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $templatePath;
    private $logoUrl;

    public function __construct()
    {
        $this->templatePath = EMAIL_TEMPLATE_PATH;
        $this->logoUrl = EMAIL_LOGO_URL;
        $this->initializeMailer();
    }

    private function initializeMailer()
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = MAIL_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = MAIL_USERNAME;
            $this->mailer->Password = MAIL_PASSWORD;
            $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
            $this->mailer->Port = MAIL_PORT;
            $this->mailer->CharSet = 'UTF-8';

            // Recipients
            $this->mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $this->mailer->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        } catch (Exception $e) {
            error_log("EmailService initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Send registration confirmation to registrant
     */
    public function sendRegistrationConfirmation($userEmail, $userName, $registrationId, $packageName, $amount, $participants = [])
    {
        if (!ENABLE_EMAIL_NOTIFICATIONS) {
            return true;
        }

        $subject = CONFERENCE_SHORT_NAME . " - Registration Confirmation";
        $template = $this->loadTemplate('registration_confirmation');
        
        $data = [
            'user_name' => $userName,
            'registration_id' => $registrationId,
            'package_name' => $packageName,
            'amount' => $amount,
            'participants' => $participants,
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'conference_venue' => CONFERENCE_VENUE,
            'logo_url' => $this->logoUrl
        ];

        $htmlContent = $this->renderTemplate($template, $data);

        return $this->sendEmail($userEmail, $subject, $htmlContent);
    }

    /**
     * Send payment link to registrant
     */
    public function sendPaymentLink($userEmail, $userName, $registrationId, $amount, $paymentLink)
    {
        if (!ENABLE_EMAIL_NOTIFICATIONS || !ENABLE_PAYMENT_EMAILS) {
            return true;
        }

        $subject = CONFERENCE_SHORT_NAME . " - Payment Required";
        $template = $this->loadTemplate('payment_link');
        
        $data = [
            'user_name' => $userName,
            'registration_id' => $registrationId,
            'amount' => $amount,
            'payment_link' => $paymentLink,
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'conference_venue' => CONFERENCE_VENUE,
            'logo_url' => $this->logoUrl
        ];

        $htmlContent = $this->renderTemplate($template, $data);

        return $this->sendEmail($userEmail, $subject, $htmlContent);
    }

    /**
     * Send payment confirmation to registrant
     */
    public function sendPaymentConfirmation($userEmail, $userName, $registrationId, $amount, $transactionId, $participants = [])
    {
        if (!ENABLE_EMAIL_NOTIFICATIONS) {
            return true;
        }

        $subject = CONFERENCE_SHORT_NAME . " - Payment Confirmed";
        $template = $this->loadTemplate('payment_confirmation');
        
        $data = [
            'user_name' => $userName,
            'registration_id' => $registrationId,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'participants' => $participants,
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'conference_dates' => CONFERENCE_DATES,
            'conference_location' => CONFERENCE_LOCATION,
            'conference_venue' => CONFERENCE_VENUE,
            'logo_url' => $this->logoUrl
        ];

        $htmlContent = $this->renderTemplate($template, $data);

        return $this->sendEmail($userEmail, $subject, $htmlContent);
    }

    /**
     * Send admin notification for new registration
     */
    public function sendAdminRegistrationNotification($registrationId, $userName, $userEmail, $packageName, $amount, $registrationType, $participants = [])
    {
        if (!ENABLE_EMAIL_NOTIFICATIONS || !ADMIN_NOTIFICATIONS) {
            return true;
        }

        $subject = "New " . CONFERENCE_SHORT_NAME . " Registration - #{$registrationId}";
        $template = $this->loadTemplate('admin_registration_notification');
        
        $data = [
            'registration_id' => $registrationId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'package_name' => $packageName,
            'amount' => $amount,
            'registration_type' => $registrationType,
            'participants' => $participants,
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'admin_name' => ADMIN_NAME,
            'logo_url' => $this->logoUrl
        ];

        $htmlContent = $this->renderTemplate($template, $data);

        return $this->sendEmail(ADMIN_EMAIL, $subject, $htmlContent);
    }

    /**
     * Send admin notification for payment confirmation
     */
    public function sendAdminPaymentNotification($registrationId, $userName, $userEmail, $amount, $transactionId)
    {
        if (!ENABLE_EMAIL_NOTIFICATIONS || !ADMIN_NOTIFICATIONS) {
            return true;
        }

        $subject = "Payment Confirmed - " . CONFERENCE_SHORT_NAME . " Registration #{$registrationId}";
        $template = $this->loadTemplate('admin_payment_notification');
        
        $data = [
            'registration_id' => $registrationId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'conference_name' => CONFERENCE_NAME,
            'conference_short_name' => CONFERENCE_SHORT_NAME,
            'admin_name' => ADMIN_NAME,
            'logo_url' => $this->logoUrl
        ];

        $htmlContent = $this->renderTemplate($template, $data);

        return $this->sendEmail(ADMIN_EMAIL, $subject, $htmlContent);
    }

    /**
     * Load email template
     */
    private function loadTemplate($templateName)
    {
        $templateFile = $this->templatePath . $templateName . '.html';
        
        if (file_exists($templateFile)) {
            return file_get_contents($templateFile);
        }
        
        // Fallback to default templates
        return $this->getDefaultTemplate($templateName);
    }

    /**
     * Render template with data
     */
    private function renderTemplate($template, $data)
    {
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
     * Send email
     */
    private function sendEmail($to, $subject, $htmlContent)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlContent;
            $this->mailer->AltBody = strip_tags($htmlContent);

            $result = $this->mailer->send();
            
            if (APP_DEBUG) {
                error_log("Email sent successfully to: {$to}");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
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

        return $templates[$templateName] ?? '';
    }

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
                <img src="{{logo_url}}" alt="{{conference_short_name}}" style="max-height: 60px; margin-bottom: 10px;">
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
                <p>If you have any questions, please contact us at ' . MAIL_FROM_ADDRESS . '</p>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

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
                <img src="{{logo_url}}" alt="{{conference_short_name}}" style="max-height: 60px; margin-bottom: 10px;">
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
                    <p><strong>Conference:</strong> {{conference_dates}} at {{conference_venue}}, {{conference_location}}</p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{payment_link}}" style="background: #059669; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">Complete Payment</a>
                </div>
                
                <p><em>This payment link will expire in 7 days.</em></p>
                <p>If you have any questions, please contact us at ' . MAIL_FROM_ADDRESS . '</p>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

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
                <img src="{{logo_url}}" alt="{{conference_short_name}}" style="max-height: 60px; margin-bottom: 10px;">
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
                    <p><strong>Conference:</strong> {{conference_dates}} at {{conference_venue}}, {{conference_location}}</p>
                </div>
                
                <h3>What\'s Next?</h3>
                <ul>
                    <li>You will receive conference materials closer to the event date</li>
                    <li>Check your email for regular updates about the conference</li>
                    <li>Contact us if you have any questions</li>
                </ul>
                
                <p>If you have any questions, please contact us at ' . MAIL_FROM_ADDRESS . '</p>
            </div>
            
            <div style="text-align: center; padding: 20px; background: #e2e8f0; color: #666;">
                <p>Best regards,<br>{{conference_short_name}} Team</p>
            </div>
        </body>
        </html>';
    }

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
                    <p><strong>Type:</strong> {{registration_type}}</p>
                    <p><strong>Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
                </div>
                
                <p>Please review this registration in your admin panel.</p>
            </div>
        </body>
        </html>';
    }

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
                    <p><strong>Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
                </div>
                
                <p>Payment has been successfully processed.</p>
            </div>
        </body>
        </html>';
    }
}
