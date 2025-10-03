<?php

namespace Cphia2025;

/**
 * Enhanced Exchange Email Service
 * 
 * General-purpose email service using Microsoft Graph API with multiple authentication methods:
 * - OAuth 2.0 Authorization Code Flow (user-based)
 * - OAuth 2.0 Client Credentials Flow (application-based)
 * - Automatic token refresh with background support
 * - Comprehensive error handling and logging
 * - Laravel-compatible
 * - Production-ready with monitoring
 * 
 * @author SendMail ExchangeEmailService
 * @version 2.0.0
 */
class ExchangeEmailService
{
    protected $oauth;
    protected $fromEmail;
    protected $fromName;
    protected $tenantId;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scope;
    protected $authMethod;
    protected $debugMode;

    public function __construct($config = [])
    {
        // Load configuration from array or environment
        $this->tenantId = $config['tenant_id'] ?? getenv('EXCHANGE_TENANT_ID');
        $this->clientId = $config['client_id'] ?? getenv('EXCHANGE_CLIENT_ID');
        $this->clientSecret = $config['client_secret'] ?? getenv('EXCHANGE_CLIENT_SECRET');
        $this->redirectUri = $config['redirect_uri'] ?? getenv('EXCHANGE_REDIRECT_URI');
        $this->scope = $config['scope'] ?? getenv('EXCHANGE_SCOPE') ?: 'https://graph.microsoft.com/.default';
        $this->fromEmail = $config['from_email'] ?? getenv('MAIL_FROM_ADDRESS');
        $this->fromName = $config['from_name'] ?? getenv('MAIL_FROM_NAME');
        $this->authMethod = $config['auth_method'] ?? getenv('EXCHANGE_AUTH_METHOD') ?: 'authorization_code';
        $this->debugMode = $config['debug'] ?? getenv('EXCHANGE_DEBUG') === 'true';

        // Initialize OAuth handler
        $this->oauth = new \Cphia2025\ExchangeOAuth(
            $this->tenantId,
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->scope,
            $this->authMethod
        );
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
     * Get OAuth authorization URL for initial setup (Authorization Code Flow only)
     */
    public function getOAuthUrl()
    {
        return $this->oauth->getAuthorizationUrl();
    }

    /**
     * Process OAuth callback and exchange code for tokens (Authorization Code Flow only)
     */
    public function processOAuthCallback($code, $state)
    {
        return $this->oauth->exchangeCodeForToken($code, $state);
    }

    /**
     * Initialize Client Credentials Flow (for automatic email sending)
     */
    public function initializeClientCredentials()
    {
        try {
            return $this->oauth->getClientCredentialsToken();
        } catch (\Exception $e) {
            $this->logError('Failed to initialize client credentials: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using Microsoft Graph API with automatic authentication
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body (HTML or plain text)
     * @param bool $isHtml Whether the body is HTML
     * @param string|null $fromEmail Override sender email
     * @param string|null $fromName Override sender name
     * @param array $cc Optional CC recipients
     * @param array $bcc Optional BCC recipients
     * @param array $attachments Optional file attachments
     * @return bool Success status
     * @throws \Exception If sending fails
     */
    public function sendEmail($to, $subject, $body, $isHtml = true, $fromEmail = null, $fromName = null, $cc = [], $bcc = [], $attachments = [])
    {
        if (!$this->oauth->isConfigured()) {
            throw new \Exception("Email service is not configured. Please check OAuth settings.");
        }

        // For client credentials flow, ensure we have a token
        if ($this->authMethod === 'client_credentials' && !$this->oauth->hasValidToken()) {
            if (!$this->initializeClientCredentials()) {
                throw new \Exception("Failed to initialize client credentials. Please check Azure app permissions.");
            }
        }

        $fromEmail = $fromEmail ?: $this->fromEmail;
        $fromName = $fromName ?: $this->fromName;

        try {
            $this->logInfo("Sending email to: {$to}, Subject: {$subject}");
            
            $result = $this->oauth->sendEmail($to, $subject, $body, $isHtml, $fromEmail, $fromName, $cc, $bcc, $attachments);
            
            if ($result) {
                $this->logInfo("Email sent successfully to: {$to}");
            } else {
                $this->logError("Failed to send email to: {$to}");
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logError("Email sending failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email to multiple recipients with error handling
     * 
     * @param array $recipients Array of email addresses
     * @param string $subject Email subject
     * @param string $body Email body
     * @param bool $isHtml Whether the body is HTML
     * @param string|null $fromEmail Override sender email
     * @param string|null $fromName Override sender name
     * @return array Results with success/failure counts
     */
    public function sendBulkEmail($recipients, $subject, $body, $isHtml = true, $fromEmail = null, $fromName = null)
    {
        $results = [
            'total' => count($recipients),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            try {
                $this->sendEmail($recipient, $subject, $body, $isHtml, $fromEmail, $fromName);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][$recipient] = $e->getMessage();
                $this->logError("Failed to send email to {$recipient}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Test email service connection and authentication
     * 
     * @return array Test results
     */
    public function testConnection()
    {
        $result = [
            'configured' => $this->isConfigured(),
            'has_tokens' => $this->hasValidTokens(),
            'auth_method' => $this->authMethod,
            'oauth_url' => null,
            'error' => null,
            'status' => 'unknown'
        ];

        if (!$result['configured']) {
            $result['error'] = 'Email service not configured - check OAuth settings';
            $result['status'] = 'not_configured';
            return $result;
        }

        // For authorization code flow, provide OAuth URL
        if ($this->authMethod === 'authorization_code') {
            $result['oauth_url'] = $this->getOAuthUrl();
        }

        if (!$result['has_tokens']) {
            if ($this->authMethod === 'client_credentials') {
                // Try to get client credentials token
                try {
                    if ($this->initializeClientCredentials()) {
                        $result['has_tokens'] = true;
                        $result['status'] = 'ready';
                    } else {
                        $result['error'] = 'Failed to get client credentials token - check Azure app permissions';
                        $result['status'] = 'auth_failed';
                    }
                } catch (\Exception $e) {
                    $result['error'] = 'Client credentials error: ' . $e->getMessage();
                    $result['status'] = 'auth_failed';
                }
            } else {
                $result['error'] = 'No valid OAuth tokens - complete OAuth setup first';
                $result['status'] = 'no_tokens';
            }
            return $result;
        }

        $result['status'] = 'ready';
        return $result;
    }

    /**
     * Send test email with comprehensive testing
     * 
     * @param string $toEmail Recipient email
     * @return array Test results
     */
    public function sendTestEmail($toEmail)
    {
        $testResults = [
            'success' => false,
            'error' => null,
            'details' => []
        ];

        try {
            // Test connection first
            $connectionTest = $this->testConnection();
            $testResults['details']['connection'] = $connectionTest;

            if ($connectionTest['status'] !== 'ready') {
                $testResults['error'] = 'Connection test failed: ' . $connectionTest['error'];
                return $testResults;
            }

            // Send test email
            $subject = "Enhanced Exchange Email Service Test - " . date('Y-m-d H:i:s');
            $body = $this->getTestEmailTemplate($toEmail);
            
            $testResults['success'] = $this->sendEmail($toEmail, $subject, $body);
            
            if ($testResults['success']) {
                $testResults['details']['email_sent'] = true;
                $testResults['details']['recipient'] = $toEmail;
                $testResults['details']['timestamp'] = date('Y-m-d H:i:s');
            }

        } catch (\Exception $e) {
            $testResults['error'] = $e->getMessage();
            $testResults['details']['exception'] = $e->getMessage();
        }

        return $testResults;
    }

    /**
     * Send HTML email with template
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $template Template name or HTML content
     * @param array $data Template data
     * @param string|null $fromEmail Override sender email
     * @param string|null $fromName Override sender name
     * @return bool Success status
     */
    public function sendTemplateEmail($to, $subject, $template, $data = [], $fromEmail = null, $fromName = null)
    {
        $body = $this->renderTemplate($template, $data);
        return $this->sendEmail($to, $subject, $body, true, $fromEmail, $fromName);
    }

    /**
     * Render email template with enhanced data handling
     * 
     * @param string $template Template name or HTML content
     * @param array $data Template data
     * @return string Rendered HTML
     */
    protected function renderTemplate($template, $data = [])
    {
        // If template is HTML content, return as is
        if (strpos($template, '<html') !== false || strpos($template, '<body') !== false) {
            return $template;
        }

        // Load template from file or use built-in templates
        $templates = [
            'welcome' => $this->getWelcomeTemplate(),
            'notification' => $this->getNotificationTemplate(),
            'confirmation' => $this->getConfirmationTemplate(),
            'test' => $this->getTestEmailTemplate($data['email'] ?? 'user@example.com')
        ];

        $html = $templates[$template] ?? $template;

        // Replace placeholders with data (enhanced with array support)
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $html = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html);
        }

        return $html;
    }

    /**
     * Get service status and health information
     * 
     * @return array Service status
     */
    public function getServiceStatus()
    {
        $status = [
            'service' => 'Enhanced Exchange Email Service',
            'version' => '2.0.0',
            'configured' => $this->isConfigured(),
            'has_tokens' => $this->hasValidTokens(),
            'auth_method' => $this->authMethod,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'debug_mode' => $this->debugMode,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'unknown'
        ];

        if (!$status['configured']) {
            $status['status'] = 'not_configured';
        } elseif (!$status['has_tokens']) {
            $status['status'] = 'no_tokens';
        } else {
            $status['status'] = 'ready';
        }

        return $status;
    }

    /**
     * Refresh OAuth tokens manually
     * 
     * @return bool Success status
     */
    public function refreshTokens()
    {
        try {
            return $this->oauth->refreshAccessToken();
        } catch (\Exception $e) {
            $this->logError('Token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear stored tokens
     */
    public function clearTokens()
    {
        $this->oauth->clearTokens();
        $this->logInfo('Tokens cleared');
    }

    /**
     * Log information message
     */
    protected function logInfo($message)
    {
        if ($this->debugMode) {
            error_log('[ExchangeEmailService] INFO: ' . $message);
        }
    }

    /**
     * Log error message
     */
    protected function logError($message)
    {
        error_log('[ExchangeEmailService] ERROR: ' . $message);
    }

    // --- Enhanced Email Templates ---

    /**
     * Get enhanced test email template
     */
    protected function getTestEmailTemplate($toEmail)
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; text-align: center;">
                <h1>✅ Enhanced Exchange Email Service Test</h1>
                <p>Microsoft Graph API - Production Ready v2.0</p>
            </div>
            
            <div style="padding: 20px;">
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>🎉 Enhanced Email Service Working Perfectly!</h3>
                    <p>This email confirms that your Enhanced Exchange Email Service is working correctly using Microsoft Graph API.</p>
                </div>
                
                <h3>Configuration Details:</h3>
                <ul>
                    <li><strong>Method:</strong> Microsoft Graph API (Enhanced)</li>
                    <li><strong>Authentication:</strong> ' . ucfirst(str_replace('_', ' ', $this->authMethod)) . '</li>
                    <li><strong>Security:</strong> Bearer Token Authentication</li>
                    <li><strong>Sent At:</strong> ' . date('Y-m-d H:i:s T') . '</li>
                    <li><strong>Recipient:</strong> ' . htmlspecialchars($toEmail) . '</li>
                    <li><strong>From:</strong> ' . htmlspecialchars($this->fromEmail) . '</li>
                    <li><strong>Service:</strong> Enhanced Exchange Email Service v2.0</li>
                </ul>
                
                <div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h4>🚀 Enhanced Features:</h4>
                    <ul>
                        <li>✅ Multiple OAuth Flows (Authorization Code + Client Credentials)</li>
                        <li>✅ Automatic Token Refresh</li>
                        <li>✅ Background Refresh Support</li>
                        <li>✅ Comprehensive Error Handling</li>
                        <li>✅ Production Monitoring</li>
                        <li>✅ Laravel Compatible</li>
                        <li>✅ Debug Mode Support</li>
                        <li>✅ Bulk Email Support</li>
                    </ul>
                </div>
                
                <p><strong>Your Enhanced Exchange Email Service is ready for production! 🎉</strong></p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated test email from the Enhanced Exchange Email Service v2.0</p>
                <p>Generated on ' . date('Y-m-d H:i:s') . ' | Microsoft Graph API</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get welcome email template
     */
    protected function getWelcomeTemplate()
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 20px; text-align: center;">
                <h1>Welcome to {{app_name}}!</h1>
                <p>Thank you for joining us</p>
            </div>
            
            <div style="padding: 20px;">
                <p>Dear {{name}},</p>
                
                <p>Welcome to {{app_name}}! We are excited to have you on board.</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Getting Started:</h3>
                    <ul>
                        <li>Complete your profile setup</li>
                        <li>Explore our features</li>
                        <li>Contact support if you need help</li>
                    </ul>
                </div>
                
                <p>If you have any questions, please do not hesitate to contact us.</p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated email from {{app_name}}</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get notification email template
     */
    protected function getNotificationTemplate()
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 20px; text-align: center;">
                <h1>📢 {{title}}</h1>
            </div>
            
            <div style="padding: 20px;">
                <p>Dear {{name}},</p>
                
                <p>{{message}}</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Details:</h3>
                    <p>{{details}}</p>
                </div>
                
                <p>Thank you for your attention.</p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated notification from {{app_name}}</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Get confirmation email template
     */
    protected function getConfirmationTemplate()
    {
        return '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 20px; text-align: center;">
                <h1>✅ {{title}}</h1>
                <p>Your action has been confirmed</p>
            </div>
            
            <div style="padding: 20px;">
                <p>Dear {{name}},</p>
                
                <p>{{message}}</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Confirmation Details:</h3>
                    <ul>
                        <li><strong>Reference ID:</strong> {{reference_id}}</li>
                        <li><strong>Date:</strong> {{date}}</li>
                        <li><strong>Status:</strong> {{status}}</li>
                    </ul>
                </div>
                
                <p>Thank you for using {{app_name}}!</p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;">
                <p>This is an automated confirmation from {{app_name}}</p>
            </div>
        </body>
        </html>';
    }
}
