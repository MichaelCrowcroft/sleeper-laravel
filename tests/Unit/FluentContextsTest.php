<?php

use Sleeper\Laravel\Sleeper;

it('creates user, league, and draft contexts', function () {
    $sleeper = app(Sleeper::class);

    $user = $sleeper->user('user-1');
    expect($user->id())->toBe('user-1');

    $league = $sleeper->league('league-1');
    expect($league->leagueId())->toBe('league-1');

    $draft = $sleeper->draft('draft-1');
    expect($draft->draftId())->toBe('draft-1');
});

