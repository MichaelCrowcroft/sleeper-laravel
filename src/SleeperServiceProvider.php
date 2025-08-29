<?php

namespace Sleeper\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SleeperServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sleeper.php', 'sleeper');

        $this->app->singleton(Sleeper::class, function () {
            return new Sleeper();
        });

        // Bind by string for the facade accessor as well
        $this->app->alias(Sleeper::class, 'sleeper');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/sleeper.php' => config_path('sleeper.php'),
        ], 'sleeper-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Sleeper\Laravel\Commands\RefreshPlayersCsv::class,
            ]);
        }
    }

    public function provides(): array
    {
        return [Sleeper::class, 'sleeper'];
    }
}
