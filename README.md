# Tong Facebook SDK for Laravel

A comprehensive Facebook SDK package for Laravel applications that provides easy integration with Facebook's Graph API, OAuth authentication, and various Facebook features.

## Features

- 🔐 **OAuth Authentication** - Complete OAuth flow implementation
- 📊 **Graph API Integration** - Full access to Facebook's Graph API
- 👥 **User Management** - Get user profiles, friends, posts, and more
- 📱 **Page Management** - Create posts, manage pages, and get insights
- 👥 **Group Operations** - Post to groups and manage group interactions
- 📸 **Media Handling** - Upload photos and manage albums
- 🎯 **Search Functionality** - Search for users, pages, groups, and events
- 📈 **Analytics** - Get insights and analytics data
- 🛡️ **Error Handling** - Comprehensive error handling with custom exceptions
- 📝 **Logging** - Built-in request/response logging
- 💾 **Caching** - Automatic caching for improved performance
- 🧪 **Testing** - Full test suite with PHPUnit

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- GuzzleHTTP 7.0 or higher

## Installation

1. **Install the package via Composer:**

```bash
composer require tong/facebook-sdk
```

2. **Publish the configuration file:**

```bash
php artisan vendor:publish --provider="Tong\FacebookSdk\FacebookSdkServiceProvider" --tag="facebook-sdk-config"
```

3. **Add your Facebook App credentials to your `.env` file:**

```env
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_GRAPH_VERSION=v18.0
FACEBOOK_REDIRECT_URI=https://your-domain.com/facebook/callback
```

## Configuration

The configuration file `config/facebook-sdk.php` contains all the settings for the Facebook SDK:

```php
return [
    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),
    'default_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v18.0'),
    'graph_url' => 'https://graph.facebook.com',
    'oauth_url' => 'https://www.facebook.com/dialog/oauth',
    'default_permissions' => ['public_profile', 'email'],
    'redirect_uri' => env('FACEBOOK_REDIRECT_URI', ''),
    'http' => [
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
    ],
    'logging' => [
        'enabled' => env('FACEBOOK_LOGGING_ENABLED', false),
        'channel' => env('FACEBOOK_LOGGING_CHANNEL', 'stack'),
    ],
    'cache' => [
        'enabled' => env('FACEBOOK_CACHE_ENABLED', true),
        'ttl' => env('FACEBOOK_CACHE_TTL', 3600),
    ],
];
```

## Usage

### Basic Usage

#### Using the Facade

```php
use Tong\FacebookSdk\Facades\Facebook;

// Get user profile
$userProfile = Facebook::get('me', ['access_token' => $accessToken]);

// Create a post
$post = Facebook::post('me/feed', [
    'access_token' => $accessToken,
    'message' => 'Hello from Laravel!'
]);
```

#### Using Dependency Injection

```php
use Tong\FacebookSdk\Services\FacebookService;
use Tong\FacebookSdk\Services\FacebookAuthService;
use Tong\FacebookSdk\Services\FacebookGraphService;

class FacebookController extends Controller
{
    public function __construct(
        private FacebookService $facebookService,
        private FacebookAuthService $authService,
        private FacebookGraphService $graphService
    ) {}

    public function login()
    {
        $loginUrl = $this->authService->getLoginUrl(['public_profile', 'email']);
        return redirect($loginUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');
        $tokenData = $this->authService->getAccessTokenFromCode($code);
        $user = $this->authService->getUser($tokenData['access_token']);
        
        return response()->json($user);
    }
}
```

### Authentication

#### OAuth Login Flow

```php
// 1. Generate login URL
$loginUrl = app(FacebookAuthService::class)->getLoginUrl([
    'public_profile',
    'email',
    'user_posts'
]);

// 2. Redirect user to Facebook
return redirect($loginUrl);

// 3. Handle callback
public function handleCallback(Request $request)
{
    $code = $request->get('code');
    $tokenData = app(FacebookAuthService::class)->getAccessTokenFromCode($code);
    
    // Store the access token
    $accessToken = $tokenData['access_token'];
    
    // Get user information
    $user = app(FacebookAuthService::class)->getUser($accessToken);
    
    return response()->json($user);
}
```

#### Token Management

```php
$authService = app(FacebookAuthService::class);

// Debug token information
$tokenInfo = $authService->debugToken($accessToken);

// Extend short-lived token to long-lived
$longLivedToken = $authService->extendToken($accessToken);

// Get user permissions
$permissions = $authService->getUserPermissions($accessToken);

// Revoke permissions
$authService->revokePermissions($accessToken, ['email']);
```

### Graph API Operations

#### User Operations

```php
$graphService = app(FacebookGraphService::class);

// Get user profile
$profile = $graphService->getUserProfile($accessToken, [
    'id', 'name', 'email', 'picture'
]);

// Get user posts
$posts = $graphService->getUserPosts($accessToken, [
    'id', 'message', 'created_time'
], 10);

// Create a post
$post = $graphService->createPost($accessToken, 'Hello from Laravel!');

// Get user friends
$friends = $graphService->getUserFriends($accessToken, ['id', 'name']);
```

#### Media Operations

```php
// Get user photos
$photos = $graphService->getUserPhotos($accessToken, [
    'id', 'source', 'created_time'
]);

// Upload a photo
$photo = $graphService->uploadPhoto(
    $accessToken,
    'https://example.com/image.jpg',
    'My awesome photo!'
);

// Get user albums
$albums = $graphService->getUserAlbums($accessToken, [
    'id', 'name', 'description'
]);

// Create an album
$album = $graphService->createAlbum(
    $accessToken,
    'My Vacation Photos',
    'Photos from my recent vacation'
);
```

#### Page Operations

```php
// Get user's pages
$pages = $graphService->getUserPages($accessToken, [
    'id', 'name', 'access_token'
]);

// Get page information
$page = $graphService->getPage($pageId, $pageAccessToken, [
    'id', 'name', 'fan_count', 'category'
]);

// Create a post on a page
$pagePost = $graphService->createPagePost(
    $pageId,
    $pageAccessToken,
    'Hello from our page!'
);
```

#### Group Operations

```php
// Get user's groups
$groups = $graphService->getUserGroups($accessToken, [
    'id', 'name', 'privacy'
]);

// Get group information
$group = $graphService->getGroup($groupId, $accessToken, [
    'id', 'name', 'description', 'member_count'
]);

// Create a post in a group
$groupPost = $graphService->createGroupPost(
    $groupId,
    $accessToken,
    'Hello group members!'
);
```

#### Social Interactions

```php
// Get comments on a post
$comments = $graphService->getComments($postId, $accessToken, [
    'id', 'message', 'from', 'created_time'
]);

// Add a comment
$comment = $graphService->addComment($postId, $accessToken, 'Great post!');

// Get likes on a post
$likes = $graphService->getLikes($postId, $accessToken, [
    'id', 'name'
]);

// Like a post
$graphService->likePost($postId, $accessToken);

// Unlike a post
$graphService->unlikePost($postId, $accessToken);
```

#### Search and Analytics

```php
// Search for users
$users = $graphService->search('John Doe', 'user', $accessToken, [
    'id', 'name', 'picture'
]);

// Search for pages
$pages = $graphService->search('Laravel', 'page', $accessToken, [
    'id', 'name', 'category'
]);

// Get insights for a page
$insights = $graphService->getInsights($pageId, $pageAccessToken, [
    'page_impressions',
    'page_engaged_users'
], 'day');
```

### Error Handling

The package provides comprehensive error handling with custom exceptions:

```php
use Tong\FacebookSdk\Exceptions\FacebookApiException;

try {
    $user = $graphService->getUserProfile($accessToken);
} catch (FacebookApiException $e) {
    if ($e->isInvalidToken()) {
        // Handle invalid token
        return redirect()->route('facebook.login');
    }
    
    if ($e->isInsufficientPermissions()) {
        // Handle insufficient permissions
        return response()->json(['error' => 'Insufficient permissions'], 403);
    }
    
    if ($e->isRateLimited()) {
        // Handle rate limiting
        return response()->json(['error' => 'Rate limited'], 429);
    }
    
    // Handle other errors
    return response()->json(['error' => $e->getMessage()], 500);
}
```

### Caching

The package includes automatic caching for GET requests:

```php
// Enable caching in config
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
],

// Cache is automatically used for GET requests
$userProfile = $graphService->getUserProfile($accessToken); // Cached
$posts = $graphService->getUserPosts($accessToken); // Cached
```

### Logging

Enable logging to track API requests and responses:

```php
// Enable logging in config
'logging' => [
    'enabled' => true,
    'channel' => 'stack',
],

// Logs will be written to the specified channel
// You can view them in your Laravel logs
```

## Testing

Run the test suite:

```bash
composer test
```

Or run with coverage:

```bash
composer test -- --coverage
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Run the test suite
6. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

If you encounter any issues or have questions, please open an issue on GitHub.

## Changelog

### 1.0.0
- Initial release
- OAuth authentication
- Graph API integration
- User, page, and group operations
- Media handling
- Search functionality
- Analytics and insights
- Error handling and logging
- Caching support
- Comprehensive test suite
