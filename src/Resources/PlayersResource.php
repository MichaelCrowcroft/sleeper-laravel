<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetAllPlayers;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetTrendingPlayers;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetPlayerProjections;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetPlayerStats;

class PlayersResource extends BaseResource
{
    public function all(?string $sport = null): Response
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        return $this->connector->send(new GetAllPlayers($sport));
    }

    public function trending(?string $sport = null, ?int $lookbackHours = null, ?int $limit = null): Response
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        return $this->connector->send(new GetTrendingPlayers($sport, 'add', $lookbackHours, $limit));
    }

    public function projections(
        string $playerId,
        string $season,
        ?string $sport = null,
        ?string $seasonType = null,
        ?string $grouping = null
    ): Response
    {
        return $this->connector->send(new GetPlayerProjections(
            $playerId,
            $season,
            $sport,
            $seasonType,
            $grouping
        ));
    }

    public function stats(
        string $playerId,
        string $season,
        ?string $sport = null,
        ?string $seasonType = null,
        ?string $grouping = null
    ): Response
    {
        return $this->connector->send(new GetPlayerStats(
            $playerId,
            $season,
            $sport,
            $seasonType,
            $grouping
        ));
    }
}
