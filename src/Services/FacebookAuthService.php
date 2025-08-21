<?php

namespace Tong\FacebookSdk\Services;

use Tong\FacebookSdk\Exceptions\FacebookApiException;

class FacebookAuthService
{
    protected FacebookService $facebookService;

    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    /**
     * Get the OAuth login URL
     */
    public function getLoginUrl(array $permissions = [], string $state = null): string
    {
        $params = [
            'client_id' => $this->facebookService->getAppId(),
            'redirect_uri' => config('facebook-sdk.redirect_uri'),
            'scope' => implode(',', $permissions ?: config('facebook-sdk.default_permissions')),
            'response_type' => 'code',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        $oauthUrl = config('facebook-sdk.oauth_url', 'https://www.facebook.com/dialog/oauth');
        
        return $oauthUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessTokenFromCode(string $code): array
    {
        $params = [
            'client_id' => $this->facebookService->getAppId(),
            'client_secret' => $this->facebookService->getAppSecret(),
            'redirect_uri' => config('facebook-sdk.redirect_uri'),
            'code' => $code,
        ];

        $response = $this->facebookService->get('oauth/access_token', $params);
        
        if (isset($response['error'])) {
            throw new FacebookApiException(
                $response['error']['message'] ?? 'Failed to get access token',
                $response['error']['code'] ?? 0
            );
        }

        return $response;
    }

    /**
     * Get user information using access token
     */
    public function getUser(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        $response = $this->facebookService->get('me', $params);
        
        if (isset($response['error'])) {
            throw new FacebookApiException(
                $response['error']['message'] ?? 'Failed to get user information',
                $response['error']['code'] ?? 0
            );
        }

        return $response;
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(string $accessToken): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        $response = $this->facebookService->get('me/permissions', $params);
        
        if (isset($response['error'])) {
            throw new FacebookApiException(
                $response['error']['message'] ?? 'Failed to get user permissions',
                $response['error']['code'] ?? 0
            );
        }

        return $response['data'] ?? [];
    }

    /**
     * Revoke user permissions
     */
    public function revokePermissions(string $accessToken, array $permissions = []): array
    {
        $data = [
            'access_token' => $accessToken,
        ];

        if (!empty($permissions)) {
            $data['permission'] = implode(',', $permissions);
        }

        $response = $this->facebookService->delete('me/permissions', $data);
        
        if (isset($response['error'])) {
            throw new FacebookApiException(
                $response['error']['message'] ?? 'Failed to revoke permissions',
                $response['error']['code'] ?? 0
            );
        }

        return $response;
    }

    /**
     * Get debug information about an access token
     */
    public function debugToken(string $accessToken): array
    {
        $params = [
            'input_token' => $accessToken,
            'access_token' => $this->facebookService->getAppAccessToken(),
        ];

        $response = $this->facebookService->get('debug_token', $params);
        
        if (isset($response['error'])) {
            throw new FacebookApiException(
                $response['error']['message'] ?? 'Failed to debug token',
                $response['error']['code'] ?? 0
            );
        }

        return $response['data'] ?? [];
    }

    /**
     * Extend a short-lived access token to a long-lived one
     */
    public function extendToken(string $accessToken): array
    {
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->facebookService->getAppId(),
            'client_secret' => $this->facebookService->getAppSecret(),
            'fb_exchange_token' => $accessToken,
        ];

        $response = $this->facebookService->get('oauth/access_token', $params);
        
        if (isset($response['error'])) {
            throw new FacebookApiException(
                $response['error']['message'] ?? 'Failed to extend token',
                $response['error']['code'] ?? 0
            );
        }

        return $response;
    }

    /**
     * Get the app access token
     */
    public function getAppAccessToken(): string
    {
        return $this->facebookService->getAppAccessToken();
    }

    /**
     * Validate a signed request
     */
    public function parseSignedRequest(string $signedRequest): array
    {
        $data = explode('.', $signedRequest);
        
        if (count($data) !== 2) {
            throw new FacebookApiException('Invalid signed request format');
        }

        $signature = $data[0];
        $payload = $data[1];

        // Decode the payload
        $decodedPayload = $this->base64UrlDecode($payload);
        $payloadData = json_decode($decodedPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FacebookApiException('Invalid JSON in signed request payload');
        }

        // Verify the signature
        $expectedSignature = hash_hmac('sha256', $payload, $this->facebookService->getAppSecret(), true);
        $expectedSignature = $this->base64UrlEncode($expectedSignature);

        if ($signature !== $expectedSignature) {
            throw new FacebookApiException('Invalid signature in signed request');
        }

        return $payloadData;
    }

    /**
     * Base64 URL decode
     */
    protected function base64UrlDecode(string $data): string
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        
        return base64_decode($data);
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
