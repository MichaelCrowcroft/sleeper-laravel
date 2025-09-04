<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use MichaelCrowcroft\SleeperLaravel\Requests\State\GetState;
use MichaelCrowcroft\SleeperLaravel\Requests\Users\GetUserLeagues;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetTrendingPlayers;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetPlayerProjections;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetPlayerStats;
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

it('fetches player projections with required parameters', function () {
    $mock = MockClient::global([
        GetPlayerProjections::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->projections('6794', '2025');

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/projections/nfl/player/6794');
});

it('fetches player projections with custom parameters', function () {
    $mock = MockClient::global([
        GetPlayerProjections::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->projections('6794', '2025', 'nfl', 'regular', 'game');

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/projections/nfl/player/6794');
});

it('fetches player stats with required parameters', function () {
    $mock = MockClient::global([
        GetPlayerStats::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->stats('6794', '2024');

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/stats/nfl/player/6794');
});

it('fetches player stats with custom parameters', function () {
    $mock = MockClient::global([
        GetPlayerStats::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->stats('6794', '2024', 'nfl', 'regular', 'season');

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/stats/nfl/player/6794');
});

it('fetches player projections with season grouping for aggregated data', function () {
    $mock = MockClient::global([
        GetPlayerProjections::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->projections('6794', '2025', null, null, 'season');

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/projections/nfl/player/6794');
});

it('fetches player projections with null grouping for aggregated data', function () {
    $mock = MockClient::global([
        GetPlayerProjections::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->projections('6794', '2025', null, null, null);

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/projections/nfl/player/6794');
});

it('fetches player stats with season grouping for aggregated data', function () {
    $mock = MockClient::global([
        GetPlayerStats::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->stats('6794', '2024', null, null, 'season');

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/stats/nfl/player/6794');
});

it('fetches player stats with null grouping for aggregated data', function () {
    $mock = MockClient::global([
        GetPlayerStats::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $sleeper->players()->stats('6794', '2024', null, null, null);

    $pending = $mock->getLastPendingRequest();
    expect($pending)->not->toBeNull();
    expect($pending->getUrl())->toContain('/stats/nfl/player/6794');
});

it('flattens weekly projections data correctly', function () {
    $weeklyData = [
        "1" => [
            "status" => null,
            "date" => "2025-09-08",
            "stats" => ["pts_half_ppr" => 15.92, "rec" => 6.12],
            "category" => "proj",
            "week" => 1,
            "season" => "2025",
            "player_id" => "6794"
        ],
        "2" => [
            "status" => null,
            "date" => "2025-09-15",
            "stats" => ["pts_half_ppr" => 14.5, "rec" => 5.8],
            "category" => "proj",
            "week" => 2,
            "season" => "2025",
            "player_id" => "6794"
        ]
    ];

    $mock = MockClient::global([
        GetPlayerProjections::class => MockResponse::make($weeklyData, 200),
    ]);

    $sleeper = app(Sleeper::class);
    $response = $sleeper->players()->projections('6794', '2025', null, null, 'week');

    expect($response->successful())->toBe(true);

    $data = $response->json();
    expect($data)->toBeArray();
    expect($data)->toHaveCount(2);

    // Check first week
    expect($data[0])->toHaveKey('week', 1);
    expect($data[0])->toHaveKey('date', '2025-09-08');
    expect($data[0])->toHaveKey('category', 'proj');

    // Check second week
    expect($data[1])->toHaveKey('week', 2);
    expect($data[1])->toHaveKey('date', '2025-09-15');
});

it('flattens weekly stats data correctly', function () {
    $weeklyData = [
        "1" => [
            "status" => null,
            "date" => "2024-09-08",
            "anytime_tds" => 1,
            "pts_half_ppr" => 13.9,
            "category" => "stat",
            "week" => 1,
            "season" => "2024",
            "player_id" => "6794"
        ],
        "3" => [
            "status" => null,
            "date" => "2024-09-22",
            "anytime_tds" => 0,
            "pts_half_ppr" => 8.2,
            "category" => "stat",
            "week" => 3,
            "season" => "2024",
            "player_id" => "6794"
        ],
        "2" => [
            "status" => null,
            "date" => "2024-09-15",
            "anytime_tds" => 1,
            "pts_half_ppr" => 16.1,
            "category" => "stat",
            "week" => 2,
            "season" => "2024",
            "player_id" => "6794"
        ]
    ];

    $mock = MockClient::global([
        GetPlayerStats::class => MockResponse::make($weeklyData, 200),
    ]);

    $sleeper = app(Sleeper::class);
    $response = $sleeper->players()->stats('6794', '2024', null, null, 'week');

    expect($response->successful())->toBe(true);

    $data = $response->json();
    expect($data)->toBeArray();
    expect($data)->toHaveCount(3);

    // Check that data is sorted by week
    expect($data[0])->toHaveKey('week', 1);
    expect($data[1])->toHaveKey('week', 2);
    expect($data[2])->toHaveKey('week', 3);

    // Check data integrity
    expect($data[0])->toHaveKey('anytime_tds', 1);
    expect($data[0])->toHaveKey('pts_half_ppr', 13.9);
});

it('preserves non-weekly data structure', function () {
    $aggregatedData = [
        "status" => null,
        "season" => "2024",
        "player_id" => "6794",
        "total_points" => 150.5,
        "games_played" => 15
    ];

    $mock = MockClient::global([
        GetPlayerProjections::class => MockResponse::make($aggregatedData, 200),
    ]);

    $sleeper = app(Sleeper::class);
    $response = $sleeper->players()->projections('6794', '2024', null, null, null);

    expect($response->successful())->toBe(true);

    $data = $response->json();
    expect($data)->toBeArray();
    expect($data)->toHaveKey('season', '2024');
    expect($data)->toHaveKey('total_points', 150.5);
    expect($data)->not->toHaveKey('week');
});

it('handles empty responses correctly', function () {
    $mock = MockClient::global([
        GetPlayerStats::class => MockResponse::make([], 200),
    ]);

    $sleeper = app(Sleeper::class);
    $response = $sleeper->players()->stats('6794', '2024', null, null, 'week');

    expect($response->successful())->toBe(true);

    $data = $response->json();
    expect($data)->toBeArray();
    expect($data)->toBeEmpty();
});
