<?php

use MichaelCrowcroft\SleeperLaravel\Sleeper;

it('resolves Sleeper from the container', function () {
    $instance = app(Sleeper::class);
    expect($instance)->toBeInstanceOf(Sleeper::class);
});

it('resolves via the sleeper alias', function () {
    $instance = app('sleeper');
    expect($instance)->toBeInstanceOf(Sleeper::class);
});
