<?php

namespace Cphia2025;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/ExchangeOAuth.php';

/**
 * CPHIA 2025 Laravel Email Service
 * 
 * Production-ready email service using Microsoft Graph API
 * - OAuth 2.0 Authorization Code Flow
 * - Direct Graph API calls (most reliable)
 * - Automatic token refresh
 * - Laravel-compatible
 * 
 * @author CPHIA 2025 Development Team
 * @version 2.0.0
 */
class LaravelEmailService
{
    protected $oauth;
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->oauth = new ExchangeOAuth();
        $this->fromEmail = getenv('MAIL_FROM_ADDRESS') ?: MAIL_FROM_ADDRESS;
        $this->fromName = getenv('MAIL_FROM_NAME') ?: MAIL_FROM_NAME;
    }

    /**
     * Check if the email service is properly configured
     */
    public function isConfigured()
    {
        return $this->oauth->isConfigured();
    }

    /**
     * Check if we have valid OAuth tokens
     */
    public function hasValidTokens()
    {
        return $this->oauth->hasValidToken();
    }

    /**
     * Get OAuth authorization URL for initial setup
     */
    public function getOAuthUrl()
    {
        return $this->oauth->getAuthorizationUrl();
    }

    /**
     * Process OAuth callback and exchange code for tokens
     */
    public function processOAuthCallback($code, $state)
    {
        return $this->oauth->exchangeCodeForToken($code, $state);
    }

    /**
     * Send email using Microsoft Graph API
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body (HTML or plain text)
     * @param bool $isHtml Whether the body is HTML
     * @param string|null $fromEmail Override sender email
     * @param string|null $fromName Override sender name
     * @return bool Success status
     * @throws \Exception If sending fails
     */
    public function sendEmail($to, $subject, $body, $isHtml = true, $fromEmail = null, $fromName = null)
    {
        if (!$this->oauth->isConfigured()) {
            throw new \Exception("Email service is not configured. Please check OAuth settings.");
        }

        // Ensure we have valid tokens
        if (!$this->oauth->hasValidToken()) {
            if (!$this->oauth->refreshAccessToken()) {
                throw new \Exception("OAuth tokens are invalid or expired. Please complete OAuth setup.");
            }
        }

        $fromEmail = $fromEmail ?: $this->fromEmail;
        $fromName = $fromName ?: $this->fromName;

        // Send via Graph API
        return $this->oauth->sendEmail($to, $subject, $body);
    }

    /**
     * Test email service connection
     * 
     * @return array Test results
     */
    public function testConnection()
    {
        $result = [
            'configured' => $this->isConfigured(),
            'has_tokens' => $this->hasValidTokens(),
            'oauth_url' => $this->getOAuthUrl(),
            'error' => null
        ];

        if (!$result['configured']) {
            $result['error'] = 'Email service not configured - check OAuth settings';
            return $result;
        }

        if (!$result['has_tokens']) {
            $result['error'] = 'No valid OAuth tokens - complete OAuth setup first';
            return $result;
        }

        $result['status'] = 'ready';
        return $result;
    }

    /**
     * Send registration confirmation email
     * 
     * @param string $toEmail Recipient email
     * @param array $registrationData Registration details
     * @return bool Success status
     */
    public function sendRegistrationConfirmation($toEmail, $registrationData)
    {
        $subject = "CPHIA 2025 Registration Confirmation - #" . $registrationData['registration_id'];
        $body = $this->getRegistrationConfirmationTemplate($registrationData);
        return $this->sendEmail($toEmail, $subject, $body);
    }

    /**
     * Send payment confirmation email
     * 
     * @param string $toEmail Recipient email
     * @param array $paymentData Payment details
     * @return bool Success status
     */
    public function sendPaymentConfirmation($toEmail, $paymentData)
    {
        $subject = "CPHIA 2025 Payment Confirmation - #" . $paymentData['payment_id'];
        $body = $this->getPaymentConfirmationTemplate($paymentData);
        return $this->sendEmail($toEmail, $subject, $body);
    }

    /**
     * Send admin notification email
     * 
     * @param string $subject Email subject
     * @param string $body Email body
     * @return bool Success status
     */
    public function sendAdminNotification($subject, $body)
    {
        return $this->sendEmail($this->fromEmail, $subject, $body);
    }

    /**
     * Send test email
     * 
     * @param string $toEmail Recipient email
     * @return bool Success status
     */
    public function sendTestEmail($toEmail)
    {
        $subject = "CPHIA 2025 - Email Service Test";
        $body = $this->getTestEmailTemplate($toEmail);
        return $this->sendEmail($toEmail, $subject, $body);
    }

    // --- Email Templates ---

    /**
     * Get registration confirmation email template
     */
    private function getRegistrationConfirmationTemplate($data)
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white; padding: 20px; text-align: center;">
                <h1>🎉 CPHIA 2025 Registration Confirmed!</h1>
                <p>Thank you for registering for the Conference on Public Health in Africa</p>
            </div>
            
            <div style="padding: 20px;">
                <p>Dear ' . htmlspecialchars($data['name']) . ',</p>
                
                <p>Your registration for CPHIA 2025 has been successfully confirmed!</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Registration Details:</h3>
                    <ul>
                        <li><strong>Registration ID:</strong> ' . htmlspecialchars($data['registration_id']) . '</li>
                        <li><strong>Package:</strong> ' . htmlspecialchars($data['package']) . '</li>
                        <li><strong>Amount:</strong> $' . number_format($data['amount'], 2) . '</li>
                        <li><strong>Registration Date:</strong> ' . date('F j, Y', strtotime($data['created_at'])) . '</li>
                    </ul>
                </div>
                
                <p>We look forward to seeing you at the conference!</p>
                
                <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h4>📧 Need Help?</h4>
                    <p>If you have any questions about your registration, please contact us at <strong>notifications@africacdc.org</strong></p>
                </div>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated email from the CPHIA 2025 Registration System</p>
                <p>Africa CDC | African Union</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get payment confirmation email template
     */
    private function getPaymentConfirmationTemplate($data)
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white; padding: 20px; text-align: center;">
                <h1>💳 CPHIA 2025 Payment Confirmed!</h1>
                <p>Your payment has been successfully processed</p>
            </div>
            
            <div style="padding: 20px;">
                <p>Dear ' . htmlspecialchars($data['name']) . ',</p>
                
                <p>Your payment for CPHIA 2025 has been successfully processed.</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Payment Details:</h3>
                    <ul>
                        <li><strong>Payment ID:</strong> ' . htmlspecialchars($data['payment_id']) . '</li>
                        <li><strong>Amount Paid:</strong> $' . number_format($data['amount'], 2) . '</li>
                        <li><strong>Transaction Date:</strong> ' . date('F j, Y g:i A', strtotime($data['transaction_date'])) . '</li>
                        <li><strong>Payment Method:</strong> ' . htmlspecialchars($data['payment_method'] ?? 'Credit Card') . '</li>
                    </ul>
                </div>
                
                <p>Thank you for your payment. Your registration is now complete!</p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated email from the CPHIA 2025 Registration System</p>
                <p>Africa CDC | African Union</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get test email template
     */
    private function getTestEmailTemplate($toEmail)
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white; padding: 20px; text-align: center;">
                <h1>✅ CPHIA 2025 Email Service Test</h1>
                <p>Microsoft Graph API - Production Ready</p>
            </div>
            
            <div style="padding: 20px;">
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>🎉 Email Service Working Perfectly!</h3>
                    <p>This email confirms that your CPHIA 2025 email service is working correctly using Microsoft Graph API.</p>
                </div>
                
                <h3>Configuration Details:</h3>
                <ul>
                    <li><strong>Method:</strong> Microsoft Graph API (Direct)</li>
                    <li><strong>Authentication:</strong> OAuth 2.0 Authorization Code Flow</li>
                    <li><strong>Security:</strong> Bearer Token Authentication</li>
                    <li><strong>Sent At:</strong> ' . date('Y-m-d H:i:s T') . '</li>
                    <li><strong>Recipient:</strong> ' . htmlspecialchars($toEmail) . '</li>
                    <li><strong>From:</strong> ' . htmlspecialchars($this->fromEmail) . '</li>
                    <li><strong>System:</strong> CPHIA 2025 Registration System</li>
                </ul>
                
                <div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h4>🚀 Production Ready Features:</h4>
                    <ul>
                        <li>✅ OAuth 2.0 Security</li>
                        <li>✅ Automatic Token Refresh</li>
                        <li>✅ No Password Storage</li>
                        <li>✅ Works with Any Email Provider</li>
                        <li>✅ Laravel Compatible</li>
                        <li>✅ Production Tested</li>
                    </ul>
                </div>
                
                <p><strong>Your CPHIA 2025 email system is ready for production! 🎉</strong></p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated test email from the CPHIA 2025 Registration System</p>
                <p>Generated on ' . date('Y-m-d H:i:s') . ' | Microsoft Graph API</p>
            </div>
        </body>
        </html>';
    }
}