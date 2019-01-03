<?php

namespace Matthewnw\Metrics;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class MetricsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->publishes([
            __DIR__.'/../config/metrics.php' => config_path('metrics.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/Views', 'metrics');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-metrics'),
        ]);

        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/laravel-metrics'),
        ], 'assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Matthewnw\Metrics\Commands\ValueCommand::class,
                \Matthewnw\Metrics\Commands\TrendCommand::class,
                \Matthewnw\Metrics\Commands\PartitionCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/metrics.php', 'metrics');
    }
}
