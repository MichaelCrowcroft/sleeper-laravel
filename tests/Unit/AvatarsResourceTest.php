<?php

use MichaelCrowcroft\SleeperLaravel\Sleeper;

it('builds avatar URLs', function () {
    $avatars = app(Sleeper::class)->avatars();

    expect($avatars->fullUrl(null))->toBeNull();
    expect($avatars->thumbUrl(null))->toBeNull();

    expect($avatars->fullUrl('abc123'))
        ->toBe('https://cdn.test/avatars/abc123');

    expect($avatars->thumbUrl('abc123'))
        ->toBe('https://cdn.test/avatars/thumbs/abc123');
});
