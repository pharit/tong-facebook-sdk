<?php

namespace Tong\FacebookSdk;

use Illuminate\Support\ServiceProvider;
use Tong\FacebookSdk\Services\FacebookService;
use Tong\FacebookSdk\Services\FacebookGraphService;
use Tong\FacebookSdk\Services\FacebookAuthService;

class FacebookSdkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/facebook-sdk.php', 'facebook-sdk'
        );

        $this->app->singleton(FacebookService::class, function ($app) {
            return new FacebookService(
                config('facebook-sdk.app_id'),
                config('facebook-sdk.app_secret'),
                config('facebook-sdk.default_graph_version', 'v18.0')
            );
        });

        $this->app->singleton(FacebookGraphService::class, function ($app) {
            return new FacebookGraphService(
                $app->make(FacebookService::class)
            );
        });

        $this->app->singleton(FacebookAuthService::class, function ($app) {
            return new FacebookAuthService(
                $app->make(FacebookService::class)
            );
        });

        $this->app->alias(FacebookService::class, 'facebook');
        $this->app->alias(FacebookGraphService::class, 'facebook.graph');
        $this->app->alias(FacebookAuthService::class, 'facebook.auth');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/facebook-sdk.php' => config_path('facebook-sdk.php'),
            ], 'facebook-sdk-config');
        }
    }
}
