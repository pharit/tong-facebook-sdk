<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook App Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your Facebook App settings. You'll need to
    | create a Facebook App in the Facebook Developers Console and
    | get your App ID and App Secret.
    |
    */

    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),
    'default_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v18.0'),

    /*
    |--------------------------------------------------------------------------
    | Facebook API Endpoints
    |--------------------------------------------------------------------------
    |
    | The base URLs for Facebook's Graph API and OAuth endpoints.
    |
    */

    'graph_url' => 'https://graph.facebook.com',
    'oauth_url' => 'https://www.facebook.com/dialog/oauth',

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | Default permissions to request when authenticating users.
    | You can override these when calling the auth methods.
    |
    */

    'default_permissions' => [
        'public_profile',
        'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    |
    | The redirect URI for OAuth authentication. This should match
    | what you've configured in your Facebook App settings.
    |
    */

    'redirect_uri' => env('FACEBOOK_REDIRECT_URI', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HTTP client used to make API requests.
    |
    */

    'http' => [
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Enable logging of API requests and responses for debugging.
    |
    */

    'logging' => [
        'enabled' => env('FACEBOOK_LOGGING_ENABLED', false),
        'channel' => env('FACEBOOK_LOGGING_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for storing access tokens and API responses.
    |
    */

    'cache' => [
        'enabled' => env('FACEBOOK_CACHE_ENABLED', true),
        'ttl' => env('FACEBOOK_CACHE_TTL', 3600), // 1 hour
    ],
];
