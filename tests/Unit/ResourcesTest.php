<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use MichaelCrowcroft\SleeperLaravel\Requests\State\GetState;
use MichaelCrowcroft\SleeperLaravel\Requests\Users\GetUserLeagues;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetTrendingPlayers;
use MichaelCrowcroft\SleeperLaravel\Sleeper;

it('uses default sport when omitted (players->trending)', function () {
    $mock = MockClient::global([
        GetTrendingPlayers::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);

    $sleeper->players()->trending();

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/players/nfl/trending/add');
});

it('derives current season from state for user leagues', function () {
    $mock = MockClient::global([
        GetState::class => MockResponse::make(['league_season' => '2024'], 200),
        GetUserLeagues::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->users()->leaguesForCurrentSeason('user-99');

    $pending = $mock->getLastPendingRequest();
    expect($pending->getUrl())->toBe('https://example.test/v1/user/user-99/leagues/nfl/2024');
});
