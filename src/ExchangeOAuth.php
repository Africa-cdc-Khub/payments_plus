<?php

namespace Cphia2025;

/**
 * Microsoft Exchange OAuth Handler
 * Handles OAuth 2.0 authentication with Microsoft Graph API
 * Based on modern OAuth 2.0 best practices for Office 365
 */
class ExchangeOAuth
{
    private $tenantId;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scope;
    private $authMethod;
    private $accessToken;
    private $refreshToken;
    private $tokenExpiresAt;

    public function __construct($tenantId = null, $clientId = null, $clientSecret = null, $redirectUri = null, $scope = null, $authMethod = null)
    {
        $this->tenantId = $tenantId ?: EXCHANGE_TENANT_ID;
        $this->clientId = $clientId ?: EXCHANGE_CLIENT_ID;
        $this->clientSecret = $clientSecret ?: EXCHANGE_CLIENT_SECRET;
        $this->redirectUri = $redirectUri ?: EXCHANGE_REDIRECT_URI;
        $this->scope = $scope ?: EXCHANGE_SCOPE;
        $this->authMethod = $authMethod ?: (defined('EXCHANGE_AUTH_METHOD') ? EXCHANGE_AUTH_METHOD : 'client_credentials');
        
        // Load stored tokens from database
        $this->loadStoredTokens();
    }

    /**
     * Get the authorization URL for OAuth flow
     */
    public function getAuthorizationUrl()
    {
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'response_mode' => 'query',
            'state' => bin2hex(random_bytes(16))
        ];

        $authUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/authorize?' . http_build_query($params);
        
        // Store state for verification
        $_SESSION['oauth_state'] = $params['state'];
        
        return $authUrl;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($code, $state)
    {
        // Verify state parameter
        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            throw new \Exception('Invalid state parameter');
        }

        $tokenUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'scope' => $this->scope
        ];

        $response = $this->makeHttpRequest($tokenUrl, 'POST', $data);
        
        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
            $this->refreshToken = $response['refresh_token'] ?? null;
            $this->tokenExpiresAt = time() + $response['expires_in'];
            
            // Store tokens in database
            $this->storeTokens();
            
            return true;
        }
        
        throw new \Exception('Failed to exchange code for token: ' . json_encode($response));
    }

    /**
     * Get access token using client credentials flow
     */
    public function getClientCredentialsToken()
    {
        $tokenUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
            'grant_type' => 'client_credentials'
        ];

        $response = $this->makeTokenRequest($tokenUrl, $data);
        
        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
            $this->tokenExpiresAt = time() + ($response['expires_in'] ?? 3600);
            
            // Store tokens
            $this->storeTokens();
            
            return $this->accessToken;
        }
        
        throw new \Exception('Failed to get client credentials token: ' . ($response['error_description'] ?? 'Unknown error'));
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken()
    {
        if (empty($this->refreshToken)) {
            throw new \Exception('No refresh token available');
        }

        $tokenUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
            'scope' => $this->scope
        ];

        $response = $this->makeHttpRequest($tokenUrl, 'POST', $data);
        
        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
            $this->tokenExpiresAt = time() + $response['expires_in'];
            
            // Update refresh token if provided
            if (isset($response['refresh_token'])) {
                $this->refreshToken = $response['refresh_token'];
            }
            
            // Store updated tokens
            $this->storeTokens();
            
            return true;
        }
        
        throw new \Exception('Failed to refresh access token: ' . json_encode($response));
    }

    /**
     * Get valid access token (refresh if needed)
     */
    public function getValidAccessToken()
    {
        if (empty($this->accessToken)) {
            if ($this->authMethod === 'client_credentials') {
                return $this->getClientCredentialsToken();
            } else {
                throw new \Exception('No access token available. Please complete OAuth flow.');
            }
        }

        // Check if token is expired or will expire in the next 5 minutes
        if ($this->tokenExpiresAt <= (time() + 300)) {
            if ($this->authMethod === 'client_credentials') {
                return $this->getClientCredentialsToken();
            } else {
                $this->refreshAccessToken();
            }
        }

        return $this->accessToken;
    }

    /**
     * Send email using Microsoft Graph API
     */
    public function sendEmail($to, $subject, $body, $isHtml = true)
    {
        $accessToken = $this->getValidAccessToken();
        
        $emailData = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => $isHtml ? 'HTML' : 'Text',
                    'content' => $body
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $to
                        ]
                    ]
                ]
            ]
        ];

        // For client credentials flow, we need to use a specific user's mailbox
        // This requires the application to have been granted permission to send as that user
        $url = 'https://graph.microsoft.com/v1.0/users/' . $this->getFromEmail() . '/sendMail';
        
        $response = $this->makeHttpRequest($url, 'POST', $emailData, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        return isset($response['id']) || empty($response);
    }

    /**
     * Get the from email address for sending
     */
    private function getFromEmail()
    {
        return defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'noreply@cphia2025.com';
    }

    /**
     * Check if OAuth is configured and tokens are available
     */
    public function isConfigured()
    {
        return !empty($this->tenantId) && 
               !empty($this->clientId) && 
               !empty($this->clientSecret);
    }

    /**
     * Check if we have valid tokens
     */
    public function hasValidToken()
    {
        return !empty($this->accessToken) && 
               $this->tokenExpiresAt && 
               time() < $this->tokenExpiresAt;
    }

    /**
     * Get the current access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Load stored tokens from database
     */
    public function loadStoredTokens()
    {
        try {
            require_once __DIR__ . '/../db_connector.php';
            $pdo = getConnection();
            
            $stmt = $pdo->prepare("SELECT access_token, refresh_token, expires_at FROM oauth_tokens WHERE service = 'exchange' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute();
            $token = $stmt->fetch();
            
            if ($token) {
                $this->accessToken = $token['access_token'];
                $this->refreshToken = $token['refresh_token'];
                $this->tokenExpiresAt = strtotime($token['expires_at']);
            }
        } catch (\Exception $e) {
            // Tokens not found or error loading
            error_log('Failed to load OAuth tokens: ' . $e->getMessage());
        }
    }

    /**
     * Store tokens in database
     */
    private function storeTokens()
    {
        try {
            require_once __DIR__ . '/../db_connector.php';
            $pdo = getConnection();
            
            // Create oauth_tokens table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS oauth_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    service VARCHAR(50) NOT NULL,
                    access_token TEXT NOT NULL,
                    refresh_token TEXT,
                    expires_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_service (service)
                )
            ");
            
            // Insert or update tokens
            $stmt = $pdo->prepare("
                INSERT INTO oauth_tokens (service, client_id, access_token, refresh_token, expires_at) 
                VALUES ('exchange', ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                expires_at = VALUES(expires_at)
            ");
            
            $stmt->execute([
                $this->clientId,
                $this->accessToken,
                $this->refreshToken,
                date('Y-m-d H:i:s', $this->tokenExpiresAt)
            ]);
        } catch (\Exception $e) {
            error_log('Failed to store OAuth tokens: ' . $e->getMessage());
        }
    }

    /**
     * Make HTTP request
     */
    private function makeHttpRequest($url, $method = 'GET', $data = null, $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                if (is_array($data)) {
                    // For OAuth token requests, use form-encoded data
                    if (strpos($url, '/oauth2/v2.0/token') !== false) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                    } else {
                        // For other requests, use JSON
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        $headers[] = 'Content-Type: application/json';
                    }
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL error: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new \Exception('HTTP error ' . $httpCode . ': ' . json_encode($decodedResponse));
        }
        
        return $decodedResponse ?: $response;
    }

    /**
     * Make token request (wrapper for makeHttpRequest)
     */
    private function makeTokenRequest($url, $data)
    {
        return $this->makeHttpRequest($url, 'POST', $data);
    }
}
