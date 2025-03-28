<?php

namespace LaravelTicimax\Ticimax;

use Illuminate\Support\ServiceProvider;
use LaravelTicimax\Ticimax\Services\TicimaxService;

class TicimaxServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/ticimax.php', 'ticimax'
        );

        $this->app->singleton('ticimax', function ($app) {
            $config = $app['config']['ticimax'];
            
            // Allow environment overrides when used in a Laravel app
            $baseUrl = env('TICIMAX_BASE_URL', $config['base_url']);
            $apiKey = env('TICIMAX_API_KEY', $config['api_key']);
            $timeout = env('TICIMAX_TIMEOUT', $config['timeout']);
            $retryTimes = env('TICIMAX_RETRY_TIMES', $config['retry_times']);
            $retrySleep = env('TICIMAX_RETRY_SLEEP', $config['retry_sleep']);
            
            return new TicimaxService($baseUrl, $apiKey, $timeout, $retryTimes, $retrySleep);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/ticimax.php' => $this->app->configPath('ticimax.php'),
        ], 'ticimax-config');
    }
}