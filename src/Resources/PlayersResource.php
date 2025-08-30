<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetAllPlayers;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetTrendingPlayers;

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
}
