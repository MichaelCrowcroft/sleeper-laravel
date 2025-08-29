<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueRosters;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueUsers;
use MichaelCrowcroft\SleeperLaravel\Sleeper;

it('computes league standings and sorts correctly', function () {
    $rosters = [
        [
            'roster_id' => 1,
            'owner_id' => 'alice',
            'settings' => [
                'wins' => 5,
                'losses' => 1,
                'ties' => 0,
                'fpts' => 100,
                'fpts_decimal' => 12,
                'fpts_against' => 80,
                'fpts_against_decimal' => 5,
            ],
        ],
        [
            'roster_id' => 2,
            'owner_id' => 'bob',
            'settings' => [
                'wins' => 5,
                'losses' => 1,
                'ties' => 0,
                'fpts' => 110,
                'fpts_decimal' => 5,
                'fpts_against' => 90,
                'fpts_against_decimal' => 3,
            ],
        ],
    ];

    $users = [
        [
            'user_id' => 'alice',
            'display_name' => 'Alice',
            'metadata' => [
                'team_name' => 'A-Team',
            ],
        ],
        [
            'user_id' => 'bob',
            'display_name' => 'Bob',
            'metadata' => [],
        ],
    ];

    MockClient::global([
        GetLeagueRosters::class => MockResponse::make($rosters, 200),
        GetLeagueUsers::class => MockResponse::make($users, 200),
    ]);

    $standings = app(Sleeper::class)->leagues()->standings('league-1');

    expect($standings)->toHaveCount(2);
    // Bob has higher fpts, should come first when wins are equal
    expect($standings[0]['team_name'])->toBe('Bob');
    expect($standings[1]['team_name'])->toBe('A-Team');
});
