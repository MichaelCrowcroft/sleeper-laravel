<?php

namespace Sleeper\Laravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use Sleeper\Laravel\Requests\Users\GetUser;
use Sleeper\Laravel\Requests\Users\GetUserDrafts;
use Sleeper\Laravel\Requests\Users\GetUserLeagues;

class UsersResource extends BaseResource
{
    public function get(string $usernameOrId): Response
    {
        return $this->connector->send(new GetUser($usernameOrId));
    }

    public function leagues(string $userId, string $sport, string $season): Response
    {
        return $this->connector->send(new GetUserLeagues($userId, $sport, $season));
    }

    public function drafts(string $userId, string $sport, string $season): Response
    {
        return $this->connector->send(new GetUserDrafts($userId, $sport, $season));
    }

    public function leaguesForCurrentSeason(string $userId, ?string $sport = null): Response
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');

        $state = $this->connector->send(new \Sleeper\Laravel\Requests\State\GetState($sport));
        $season = (string) ($state->json('league_season') ?? $state->json('season'));

        return $this->leagues($userId, $sport, $season);
    }
}

