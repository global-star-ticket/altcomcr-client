<?php

namespace Altcomcr\Client;

use Illuminate\Support\ServiceProvider;

class AltcomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/altcom.php', 'altcom');

        $this->app->bind(AltcomFactory::class, function ($app) {
            $config = $app['config']['altcom'];

            return new AltcomFactory(sandbox: $config['sandbox'] ?? true, timeout: $config['timeout'] ?? 30, retries: $config['retries'] ?? 3, retryDelay: $config['retry_delay'] ?? 100,);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                                 __DIR__.'/../config/altcom.php' => config_path('altcom.php'),
                             ], 'altcom-config');
        }
    }
}
