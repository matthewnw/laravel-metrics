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
            __DIR__.'/Config/metrics.php' => config_path('metrics.php'),
        ], 'metrics_config');

        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/Views', 'laravel-metrics');

        $this->publishes([
            __DIR__ . '/Views' => resource_path('views/vendor/laravel-metrics'),
        ]);

        $this->publishes([
            __DIR__ . '/Assets' => public_path('vendor/laravel-metrics'),
        ], 'metrics_assets');

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
        $this->mergeConfigFrom(
            __DIR__ . '/Config/metrics.php', 'Metrics'
        );
    }
}
