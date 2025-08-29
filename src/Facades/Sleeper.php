<?php

namespace Sleeper\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Sleeper\Laravel\Sleeper as SleeperConnector;

/**
 * @method static \Sleeper\Laravel\Resources\UsersResource users()
 * @method static \Sleeper\Laravel\Resources\LeaguesResource leagues()
 * @method static \Sleeper\Laravel\Resources\DraftsResource drafts()
 * @method static \Sleeper\Laravel\Resources\PlayersResource players()
 * @method static \Sleeper\Laravel\Resources\StateResource state()
 * @method static \Sleeper\Laravel\Resources\AvatarsResource avatars()
 * @method static \Sleeper\Laravel\Fluent\UserContext user(string $userId)
 * @method static \Sleeper\Laravel\Fluent\LeagueContext league(string $leagueId)
 * @method static \Sleeper\Laravel\Fluent\DraftContext draft(string $draftId)
 */
class Sleeper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sleeper';
    }
}
