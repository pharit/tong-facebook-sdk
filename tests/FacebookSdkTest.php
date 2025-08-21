<?php

namespace Tong\FacebookSdk\Tests;

use Orchestra\Testbench\TestCase;
use Tong\FacebookSdk\FacebookSdkServiceProvider;
use Tong\FacebookSdk\Services\FacebookService;
use Tong\FacebookSdk\Services\FacebookAuthService;
use Tong\FacebookSdk\Services\FacebookGraphService;
use Tong\FacebookSdk\Facades\Facebook;
use Tong\FacebookSdk\Exceptions\FacebookApiException;

class FacebookSdkTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FacebookSdkServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Facebook' => Facebook::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('facebook-sdk.app_id', 'test_app_id');
        $app['config']->set('facebook-sdk.app_secret', 'test_app_secret');
        $app['config']->set('facebook-sdk.default_graph_version', 'v18.0');
        $app['config']->set('facebook-sdk.redirect_uri', 'http://localhost/callback');
    }

    public function testServiceProviderRegistersServices(): void
    {
        $this->assertInstanceOf(FacebookService::class, app('facebook'));
        $this->assertInstanceOf(FacebookAuthService::class, app('facebook.auth'));
        $this->assertInstanceOf(FacebookGraphService::class, app('facebook.graph'));
    }

    public function testFacadeWorks(): void
    {
        $this->assertInstanceOf(FacebookService::class, Facebook::getFacadeRoot());
    }

    public function testFacebookServiceConfiguration(): void
    {
        $service = app(FacebookService::class);
        
        $this->assertEquals('test_app_id', $service->getAppId());
        $this->assertEquals('test_app_secret', $service->getAppSecret());
        $this->assertEquals('v18.0', $service->getGraphVersion());
    }

    public function testAuthServiceGetLoginUrl(): void
    {
        $authService = app(FacebookAuthService::class);
        $loginUrl = $authService->getLoginUrl(['public_profile', 'email']);
        
        $this->assertStringContainsString('facebook.com/dialog/oauth', $loginUrl);
        $this->assertStringContainsString('client_id=test_app_id', $loginUrl);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2Flocalhost%2Fcallback', $loginUrl);
        $this->assertStringContainsString('scope=public_profile%2Cemail', $loginUrl);
    }

    public function testGraphServiceMethodsExist(): void
    {
        $graphService = app(FacebookGraphService::class);
        
        $this->assertTrue(method_exists($graphService, 'getUserProfile'));
        $this->assertTrue(method_exists($graphService, 'getUserPosts'));
        $this->assertTrue(method_exists($graphService, 'createPost'));
        $this->assertTrue(method_exists($graphService, 'getUserPhotos'));
        $this->assertTrue(method_exists($graphService, 'uploadPhoto'));
        $this->assertTrue(method_exists($graphService, 'getUserAlbums'));
        $this->assertTrue(method_exists($graphService, 'createAlbum'));
        $this->assertTrue(method_exists($graphService, 'getPage'));
        $this->assertTrue(method_exists($graphService, 'getUserPages'));
        $this->assertTrue(method_exists($graphService, 'createPagePost'));
        $this->assertTrue(method_exists($graphService, 'getGroup'));
        $this->assertTrue(method_exists($graphService, 'getUserGroups'));
        $this->assertTrue(method_exists($graphService, 'createGroupPost'));
        $this->assertTrue(method_exists($graphService, 'getComments'));
        $this->assertTrue(method_exists($graphService, 'addComment'));
        $this->assertTrue(method_exists($graphService, 'getLikes'));
        $this->assertTrue(method_exists($graphService, 'likePost'));
        $this->assertTrue(method_exists($graphService, 'unlikePost'));
        $this->assertTrue(method_exists($graphService, 'search'));
        $this->assertTrue(method_exists($graphService, 'getInsights'));
        $this->assertTrue(method_exists($graphService, 'getUserEvents'));
        $this->assertTrue(method_exists($graphService, 'createEvent'));
    }

    public function testAuthServiceMethodsExist(): void
    {
        $authService = app(FacebookAuthService::class);
        
        $this->assertTrue(method_exists($authService, 'getLoginUrl'));
        $this->assertTrue(method_exists($authService, 'getAccessTokenFromCode'));
        $this->assertTrue(method_exists($authService, 'getUser'));
        $this->assertTrue(method_exists($authService, 'getUserPermissions'));
        $this->assertTrue(method_exists($authService, 'revokePermissions'));
        $this->assertTrue(method_exists($authService, 'debugToken'));
        $this->assertTrue(method_exists($authService, 'extendToken'));
        $this->assertTrue(method_exists($authService, 'getAppAccessToken'));
        $this->assertTrue(method_exists($authService, 'parseSignedRequest'));
    }

    public function testFacebookApiException(): void
    {
        $exception = new FacebookApiException('Test error', 190);
        
        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(190, $exception->getCode());
        $this->assertTrue($exception->isInvalidToken());
        $this->assertFalse($exception->isRateLimited());
    }

    public function testConfigurationPublishing(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => FacebookSdkServiceProvider::class,
            '--tag' => 'facebook-sdk-config',
        ]);

        $this->assertFileExists(config_path('facebook-sdk.php'));
    }
}
