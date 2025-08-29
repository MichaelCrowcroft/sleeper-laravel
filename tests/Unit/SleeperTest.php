<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use MichaelCrowcroft\SleeperLaravel\Requests\State\GetState;
use MichaelCrowcroft\SleeperLaravel\Sleeper;

it('uses base URL from config', function () {
    $sleeper = app(Sleeper::class);
    expect($sleeper->resolveBaseUrl())->toBe('https://example.test/v1');
});

it('sets default headers and config on requests', function () {
    // Arrange a fake for any state request
    $mock = MockClient::global([
        GetState::class => MockResponse::make(['ok' => true], 200),
    ]);

    $sleeper = app(Sleeper::class);

    // Act
    $sleeper->state()->current('nfl');

    // Assert
    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->headers()->all())
        ->toHaveKey('Accept', 'application/json');

    expect($pending->config()->all())
        ->toHaveKey('timeout', 5)
        ->toHaveKey('connect_timeout', 2);
});
