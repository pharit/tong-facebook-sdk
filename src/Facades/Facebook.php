<?php

namespace Tong\FacebookSdk\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Tong\FacebookSdk\Services\FacebookService get(string $endpoint, array $params = [])
 * @method static \Tong\FacebookSdk\Services\FacebookService post(string $endpoint, array $data = [])
 * @method static \Tong\FacebookSdk\Services\FacebookService delete(string $endpoint, array $params = [])
 * @method static string getAppId()
 * @method static string getAppSecret()
 * @method static string getGraphVersion()
 * 
 * @see \Tong\FacebookSdk\Services\FacebookService
 */
class Facebook extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'facebook';
    }
}
