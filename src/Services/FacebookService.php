<?php

namespace Tong\FacebookSdk\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Tong\FacebookSdk\Exceptions\FacebookApiException;

class FacebookService
{
    protected Client $httpClient;
    protected string $appId;
    protected string $appSecret;
    protected string $graphVersion;
    protected string $graphUrl;

    public function __construct(
        string $appId,
        string $appSecret,
        string $graphVersion = 'v18.0'
    ) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->graphVersion = $graphVersion;
        $this->graphUrl = config('facebook-sdk.graph_url', 'https://graph.facebook.com');

        $this->httpClient = new Client([
            'timeout' => config('facebook-sdk.http.timeout', 30),
            'connect_timeout' => config('facebook-sdk.http.connect_timeout', 10),
            'verify' => config('facebook-sdk.http.verify', true),
        ]);
    }

    /**
     * Make a GET request to the Facebook Graph API
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Make a POST request to the Facebook Graph API
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, [], $data);
    }

    /**
     * Make a DELETE request to the Facebook Graph API
     */
    public function delete(string $endpoint, array $params = []): array
    {
        return $this->request('DELETE', $endpoint, $params);
    }

    /**
     * Make a request to the Facebook Graph API
     */
    protected function request(string $method, string $endpoint, array $params = [], array $data = []): array
    {
        $url = $this->buildUrl($endpoint);
        
        // Add app access token if no access token is provided
        if (!isset($params['access_token'])) {
            $params['access_token'] = $this->getAppAccessToken();
        }

        $options = [
            'query' => $params,
        ];

        if (!empty($data)) {
            $options['form_params'] = $data;
        }

        $cacheKey = $this->generateCacheKey($method, $endpoint, $params, $data);
        
        if (config('facebook-sdk.cache.enabled') && $method === 'GET') {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $this->logRequest($method, $url, $params, $data);
            
            $response = $this->httpClient->request($method, $url, $options);
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            $this->logResponse($response->getStatusCode(), $responseData);

            if (config('facebook-sdk.cache.enabled') && $method === 'GET') {
                Cache::put($cacheKey, $responseData, config('facebook-sdk.cache.ttl', 3600));
            }

            return $responseData;
        } catch (GuzzleException $e) {
            $this->logError($e);
            throw new FacebookApiException(
                "Facebook API request failed: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Build the full URL for the Graph API request
     */
    protected function buildUrl(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');
        return "{$this->graphUrl}/{$this->graphVersion}/{$endpoint}";
    }

    /**
     * Get the app access token
     */
    protected function getAppAccessToken(): string
    {
        $cacheKey = "facebook_app_token_{$this->appId}";
        
        return Cache::remember($cacheKey, 3600, function () {
            $response = $this->httpClient->get("{$this->graphUrl}/oauth/access_token", [
                'query' => [
                    'client_id' => $this->appId,
                    'client_secret' => $this->appSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['access_token'] ?? '';
        });
    }

    /**
     * Generate a cache key for the request
     */
    protected function generateCacheKey(string $method, string $endpoint, array $params, array $data): string
    {
        return md5($method . $endpoint . serialize($params) . serialize($data));
    }

    /**
     * Log the API request
     */
    protected function logRequest(string $method, string $url, array $params, array $data): void
    {
        if (!config('facebook-sdk.logging.enabled')) {
            return;
        }

        Log::channel(config('facebook-sdk.logging.channel'))->info('Facebook API Request', [
            'method' => $method,
            'url' => $url,
            'params' => $params,
            'data' => $data,
        ]);
    }

    /**
     * Log the API response
     */
    protected function logResponse(int $statusCode, array $responseData): void
    {
        if (!config('facebook-sdk.logging.enabled')) {
            return;
        }

        Log::channel(config('facebook-sdk.logging.channel'))->info('Facebook API Response', [
            'status_code' => $statusCode,
            'response' => $responseData,
        ]);
    }

    /**
     * Log API errors
     */
    protected function logError(\Exception $e): void
    {
        if (!config('facebook-sdk.logging.enabled')) {
            return;
        }

        Log::channel(config('facebook-sdk.logging.channel'))->error('Facebook API Error', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    /**
     * Get the app ID
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get the app secret
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    /**
     * Get the graph version
     */
    public function getGraphVersion(): string
    {
        return $this->graphVersion;
    }
}
