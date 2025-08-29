<?php

namespace MichaelCrowcroft\SleeperLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use MichaelCrowcroft\SleeperLaravel\Sleeper as SleeperConnector;

/**
 * @method static \MichaelCrowcroft\SleeperLaravel\Resources\UsersResource users()
 * @method static \MichaelCrowcroft\SleeperLaravel\Resources\LeaguesResource leagues()
 * @method static \MichaelCrowcroft\SleeperLaravel\Resources\DraftsResource drafts()
 * @method static \MichaelCrowcroft\SleeperLaravel\Resources\PlayersResource players()
 * @method static \MichaelCrowcroft\SleeperLaravel\Resources\StateResource state()
 * @method static \MichaelCrowcroft\SleeperLaravel\Resources\AvatarsResource avatars()
 * @method static \MichaelCrowcroft\SleeperLaravel\Fluent\UserContext user(string $userId)
 * @method static \MichaelCrowcroft\SleeperLaravel\Fluent\LeagueContext league(string $leagueId)
 * @method static \MichaelCrowcroft\SleeperLaravel\Fluent\DraftContext draft(string $draftId)
 */
class Sleeper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sleeper';
    }
}

