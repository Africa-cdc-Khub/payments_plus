<?php
/**
 * Exchange OAuth 2.0 Client for Microsoft Graph API
 * Handles OAuth 2.0 authentication and token management
 */

namespace Cphia2025;

class ExchangeOAuth
{
    private $tenantId;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scope;
    private $accessToken;
    private $refreshToken;
    private $tokenExpiresAt;

    public function __construct()
    {
        $this->tenantId = EXCHANGE_TENANT_ID ?? null;
        $this->clientId = EXCHANGE_CLIENT_ID ?? null;
        $this->clientSecret = EXCHANGE_CLIENT_SECRET ?? null;
        $this->redirectUri = EXCHANGE_REDIRECT_URI ?? null;
        $this->scope = EXCHANGE_SCOPE ?? 'https://graph.microsoft.com/Mail.Send';
    }

    /**
     * Check if OAuth is properly configured
     */
    public function isConfigured()
    {
        return !empty($this->tenantId) && 
               !empty($this->clientId) && 
               !empty($this->clientSecret);
    }

    /**
     * Check if we have a valid access token
     */
    public function hasValidToken()
    {
        return !empty($this->accessToken) && 
               $this->tokenExpiresAt && 
               time() < $this->tokenExpiresAt;
    }

    /**
     * Load stored tokens from database
     */
    public function loadStoredTokens()
    {
        try {
            require_once __DIR__ . '/../../db_connector.php';
            $pdo = getConnection();
            
            $stmt = $pdo->prepare("
                SELECT access_token, refresh_token, expires_at 
                FROM oauth_tokens 
                WHERE service = 'exchange' AND client_id = ?
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            $stmt->execute([$this->clientId]);
            $token = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($token) {
                $this->accessToken = $token['access_token'];
                $this->refreshToken = $token['refresh_token'];
                $this->tokenExpiresAt = strtotime($token['expires_at']);
            }
        } catch (\Exception $e) {
            error_log('Failed to load OAuth tokens: ' . $e->getMessage());
        }
    }

    /**
     * Get OAuth authorization URL
     */
    public function getAuthorizationUrl()
    {
        if (!$this->isConfigured()) {
            throw new \Exception('OAuth is not configured');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        return 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'response_mode' => 'query',
            'state' => $state
        ]);
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForToken($code, $state)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('OAuth is not configured');
        }

        // Verify state parameter
        if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
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
            $this->tokenExpiresAt = time() + ($response['expires_in'] ?? 3600);
            
            $this->storeTokens();
            return true;
        }

        return false;
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken()
    {
        if (!$this->refreshToken) {
            return false;
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
            $this->refreshToken = $response['refresh_token'] ?? $this->refreshToken;
            $this->tokenExpiresAt = time() + ($response['expires_in'] ?? 3600);
            
            $this->storeTokens();
            return true;
        }

        return false;
    }

    /**
     * Send email using Microsoft Graph API
     */
    public function sendEmail($to, $subject, $body)
    {
        if (!$this->hasValidToken()) {
            // Try to refresh token
            if (!$this->refreshAccessToken()) {
                throw new \Exception('No valid access token available');
            }
        }

        $emailData = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
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

        $url = 'https://graph.microsoft.com/v1.0/me/sendMail';
        $response = $this->makeHttpRequest($url, 'POST', $emailData, [
            'Authorization: Bearer ' . $this->accessToken
        ]);

        return $response !== false;
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

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
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \Exception('HTTP error ' . $httpCode . ': ' . $response);
        }

        return json_decode($response, true);
    }

    /**
     * Store tokens in database
     */
    private function storeTokens()
    {
        try {
            require_once __DIR__ . '/../../db_connector.php';
            $pdo = getConnection();
            
            // Create oauth_tokens table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS oauth_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    service VARCHAR(50) NOT NULL,
                    client_id VARCHAR(255) NOT NULL,
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
}
