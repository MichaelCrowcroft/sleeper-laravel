<?php

namespace MichaelCrowcroft\SleeperLaravel;

use Saloon\Http\Connector;
use MichaelCrowcroft\SleeperLaravel\Fluent\DraftContext;
use MichaelCrowcroft\SleeperLaravel\Fluent\LeagueContext;
use MichaelCrowcroft\SleeperLaravel\Fluent\UserContext;
use MichaelCrowcroft\SleeperLaravel\Resources\AvatarsResource;
use MichaelCrowcroft\SleeperLaravel\Resources\DraftsResource;
use MichaelCrowcroft\SleeperLaravel\Resources\LeaguesResource;
use MichaelCrowcroft\SleeperLaravel\Resources\PlayersResource;
use MichaelCrowcroft\SleeperLaravel\Resources\StateResource;
use MichaelCrowcroft\SleeperLaravel\Resources\UsersResource;

class Sleeper extends Connector
{
    public function resolveBaseUrl(): string
    {
        return (string) config('sleeper.base_url', 'https://api.sleeper.app/v1');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => (int) config('sleeper.timeout', 30),
            'connect_timeout' => (int) config('sleeper.connect_timeout', 10),
        ];
    }

    public function users(): UsersResource
    {
        return new UsersResource($this);
    }

    public function leagues(): LeaguesResource
    {
        return new LeaguesResource($this);
    }

    public function drafts(): DraftsResource
    {
        return new DraftsResource($this);
    }

    public function players(): PlayersResource
    {
        return new PlayersResource($this);
    }

    public function state(): StateResource
    {
        return new StateResource($this);
    }

    public function avatars(): AvatarsResource
    {
        return new AvatarsResource($this);
    }

    // -------- Fluent Contexts --------

    public function user(string $userId): UserContext
    {
        return new UserContext($this, $userId);
    }

    public function league(string $leagueId): LeagueContext
    {
        return new LeagueContext($this, null, $leagueId);
    }

    public function draft(string $draftId): DraftContext
    {
        return new DraftContext($this, null, $draftId);
    }
}
