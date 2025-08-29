<?php

namespace MichaelCrowcroft\SleeperLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use MichaelCrowcroft\SleeperLaravel\SleeperServiceProvider;
use Saloon\Http\Faking\MockClient;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [SleeperServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        // Deterministic config for tests
        $app['config']->set('sleeper.base_url', 'https://example.test/v1');
        $app['config']->set('sleeper.cdn_url', 'https://cdn.test');
        $app['config']->set('sleeper.default_sport', 'nfl');
        $app['config']->set('sleeper.timeout', 5);
        $app['config']->set('sleeper.connect_timeout', 2);

        // Minimal app config
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    }

    protected function tearDown(): void
    {
        // Ensure global Saloon mock is reset between tests
        if (method_exists(MockClient::class, 'destroyGlobal')) {
            MockClient::destroyGlobal();
        }

        parent::tearDown();
    }
}
