<?php

namespace Fleetbase\Storefront\Providers;

use Fleetbase\Providers\CoreServiceProvider;
use Fleetbase\FleetOps\Providers\FleetOpsServiceProvider;

if (!class_exists(CoreServiceProvider::class)) {
    throw new \Exception('Storefront cannot be loaded without `fleetbase/core-api` installed!');
}

if (!class_exists(FleetOpsServiceProvider::class)) {
    throw new \Exception('Storefront cannot be loaded without `fleetbase/fleetops-api` installed!');
}

/**
 * Storefront service provider.
 *
 * @package \Fleetbase\Storefront\Providers
 */
class StorefrontServiceProvider extends CoreServiceProvider
{
    /**
     * The observers registered with the service provider.
     *
     * @var array
     */
    public $observers = [
        \Fleetbase\Storefront\Models\Product::class => \Fleetbase\Storefront\Observers\ProductObserver::class,
        \Fleetbase\Storefront\Models\Network::class => \Fleetbase\Storefront\Observers\NetworkObserver::class,
    ];

    /**
     * The middleware groups registered with the service provider.
     *
     * @var array
     */
    public $middleware = [
        'storefront.api' => [
            'throttle:60,1',
            \Illuminate\Session\Middleware\StartSession::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Fleetbase\Storefront\Http\Middleware\SetStorefrontSession::class,
            \Fleetbase\Http\Middleware\ConvertStringBooleans::class,
            \Fleetbase\Http\Middleware\SetGlobalHeaders::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Fleetbase\Http\Middleware\LogApiRequests::class,
        ],
    ];

    /**
     * Register any application services.
     *
     * Within the register method, you should only bind things into the 
     * service container. You should never attempt to register any event 
     * listeners, routes, or any other piece of functionality within the 
     * register method.
     *
     * More information on this can be found in the Laravel documentation:
     * https://laravel.com/docs/8.x/providers
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(CoreServiceProvider::class);
        $this->app->register(FleetOpsServiceProvider::class);
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     *
     * @throws \Exception If the `fleetbase/core-api` package is not installed.
     * @throws \Exception If the `fleetbase/fleetops-api` package is not installed.
     */
    public function boot()
    {
        $this->registerObservers();
        $this->registerMiddleware();
        $this->registerExpansionsFrom(__DIR__ . '/../Expansions');
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        $this->mergeConfigFrom(__DIR__ . '/../../config/database.connections.php', 'database.connections');
        $this->mergeConfigFrom(__DIR__ . '/../../config/storefront.php', 'storefront');
        $this->mergeConfigFrom(__DIR__ . '/../../config/api.php', 'storefront.api');
        $this->mergeConfigFrom(__DIR__ . '/../../config/twilio-notification-channel.php', 'twilio-notification-channel');
    }
}
