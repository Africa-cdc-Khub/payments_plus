<?php

namespace App\Services;

use App\Models\OAuthToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Microsoft Exchange OAuth Handler for Laravel
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
    private $tokenModel;

    public function __construct($tenantId = null, $clientId = null, $clientSecret = null, $redirectUri = null, $scope = null, $authMethod = null)
    {
        $this->tenantId = $tenantId ?: config('exchange-email.tenant_id');
        $this->clientId = $clientId ?: config('exchange-email.client_id');
        $this->clientSecret = $clientSecret ?: config('exchange-email.client_secret');
        $this->redirectUri = $redirectUri ?: config('exchange-email.redirect_uri');
        $this->scope = $scope ?: config('exchange-email.scope');
        $this->authMethod = $authMethod ?: config('exchange-email.auth_method', 'client_credentials');
        
        // Load stored tokens from database
        $this->loadStoredTokens();
    }

    /**
     * Get the authorization URL for OAuth flow
     */
    public function getAuthorizationUrl()
    {
        $state = bin2hex(random_bytes(16));
        
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'response_mode' => 'query',
            'state' => $state
        ];

        $authUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/authorize?' . http_build_query($params);
        
        // Store state in session for verification
        session(['oauth_state' => $state]);
        
        return $authUrl;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($code, $state)
    {
        // Verify state parameter
        if (session('oauth_state') !== $state) {
            throw new Exception('Invalid state parameter');
        }

        $tokenUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
        
        $response = Http::asForm()->post($tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'scope' => $this->scope
        ]);

        if ($response->successful() && isset($response['access_token'])) {
            $data = $response->json();
            $this->accessToken = $data['access_token'];
            $this->refreshToken = $data['refresh_token'] ?? null;
            $this->tokenExpiresAt = now()->addSeconds($data['expires_in']);
            
            // Store tokens in database
            $this->storeTokens();
            
            // Clear state from session
            session()->forget('oauth_state');
            
            return true;
        }
        
        throw new Exception('Failed to exchange code for token: ' . $response->body());
    }

    /**
     * Get access token using client credentials flow
     */
    public function getClientCredentialsToken()
    {
        $tokenUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
        
        $response = Http::asForm()->post($tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
            'grant_type' => 'client_credentials'
        ]);

        if ($response->successful() && isset($response['access_token'])) {
            $data = $response->json();
            $this->accessToken = $data['access_token'];
            $this->tokenExpiresAt = now()->addSeconds($data['expires_in'] ?? 3600);
            
            // Store tokens
            $this->storeTokens();
            
            return $this->accessToken;
        }
        
        $error = $response->json('error_description', 'Unknown error');
        throw new Exception('Failed to get client credentials token: ' . $error);
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken()
    {
        if (empty($this->refreshToken)) {
            throw new Exception('No refresh token available');
        }

        $tokenUrl = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
        
        $response = Http::asForm()->post($tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
            'scope' => $this->scope
        ]);

        if ($response->successful() && isset($response['access_token'])) {
            $data = $response->json();
            $this->accessToken = $data['access_token'];
            $this->tokenExpiresAt = now()->addSeconds($data['expires_in']);
            
            // Update refresh token if provided
            if (isset($data['refresh_token'])) {
                $this->refreshToken = $data['refresh_token'];
            }
            
            // Store updated tokens
            $this->storeTokens();
            
            return true;
        }
        
        throw new Exception('Failed to refresh access token: ' . $response->body());
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
                throw new Exception('No access token available. Please complete OAuth flow.');
            }
        }

        // Check if token is expired or will expire in the next 5 minutes
        if ($this->tokenExpiresAt && $this->tokenExpiresAt->lte(now()->addMinutes(5))) {
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
    public function sendEmail($to, $subject, $body, $isHtml = true, $fromEmail = null, $fromName = null, $cc = [], $bcc = [], $attachments = [])
    {
        $accessToken = $this->getValidAccessToken();
        $fromEmail = $fromEmail ?: config('exchange-email.from_email');
        
        $message = [
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
        ];

        // Add CC recipients if provided
        if (!empty($cc)) {
            $message['ccRecipients'] = array_map(function ($email) {
                return ['emailAddress' => ['address' => $email]];
            }, (array)$cc);
        }

        // Add BCC recipients if provided
        if (!empty($bcc)) {
            $message['bccRecipients'] = array_map(function ($email) {
                return ['emailAddress' => ['address' => $email]];
            }, (array)$bcc);
        }

        // Add attachments if provided
        if (!empty($attachments)) {
            $message['attachments'] = $this->prepareAttachments($attachments);
        }

        $emailData = ['message' => $message];
        
        // For client credentials flow, we need to use a specific user's mailbox
        $url = 'https://graph.microsoft.com/v1.0/users/' . $fromEmail . '/sendMail';
        
        $response = Http::withToken($accessToken)
            ->post($url, $emailData);

        if ($response->successful()) {
            Log::info('Email sent successfully', ['to' => $to, 'subject' => $subject]);
            return true;
        }

        Log::error('Failed to send email', [
            'to' => $to,
            'subject' => $subject,
            'status' => $response->status(),
            'error' => $response->body()
        ]);

        throw new Exception('Failed to send email: ' . $response->body());
    }

    /**
     * Prepare attachments for Microsoft Graph API
     */
    private function prepareAttachments(array $attachments)
    {
        $graphAttachments = [];

        foreach ($attachments as $attachment) {
            if (is_string($attachment) && file_exists($attachment)) {
                // File path provided
                $graphAttachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => basename($attachment),
                    'contentType' => mime_content_type($attachment),
                    'contentBytes' => base64_encode(file_get_contents($attachment))
                ];
            } elseif (is_array($attachment) && isset($attachment['content'])) {
                // Content provided directly
                $graphAttachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $attachment['name'] ?? 'attachment.txt',
                    'contentType' => $attachment['type'] ?? 'text/plain',
                    'contentBytes' => base64_encode($attachment['content'])
                ];
            }
        }

        return $graphAttachments;
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
               $this->tokenExpiresAt->gt(now());
    }

    /**
     * Get the current access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Clear stored tokens
     */
    public function clearTokens()
    {
        if ($this->tokenModel) {
            $this->tokenModel->delete();
        }

        $this->accessToken = null;
        $this->refreshToken = null;
        $this->tokenExpiresAt = null;
        $this->tokenModel = null;

        Log::info('OAuth tokens cleared');
    }

    /**
     * Load stored tokens from database
     */
    private function loadStoredTokens()
    {
        try {
            $this->tokenModel = OAuthToken::where('provider', 'microsoft')
                ->where('auth_method', $this->authMethod)
                ->where('is_active', true)
                ->latest()
                ->first();
            
            if ($this->tokenModel) {
                $this->accessToken = $this->tokenModel->access_token;
                $this->refreshToken = $this->tokenModel->refresh_token;
                $this->tokenExpiresAt = $this->tokenModel->expires_at;
            }
        } catch (Exception $e) {
            Log::error('Failed to load OAuth tokens: ' . $e->getMessage());
        }
    }

    /**
     * Store tokens in database
     */
    private function storeTokens()
    {
        try {
            // Deactivate old tokens
            OAuthToken::where('provider', 'microsoft')
                ->where('auth_method', $this->authMethod)
                ->update(['is_active' => false]);

            // Create new token record
            $this->tokenModel = OAuthToken::create([
                'provider' => 'microsoft',
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'expires_at' => $this->tokenExpiresAt,
                'token_type' => 'Bearer',
                'scope' => $this->scope,
                'auth_method' => $this->authMethod,
                'is_active' => true
            ]);

            Log::info('OAuth tokens stored successfully');
        } catch (Exception $e) {
            Log::error('Failed to store OAuth tokens: ' . $e->getMessage());
        }
    }
}

