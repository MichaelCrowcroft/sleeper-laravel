<?php

namespace Sleeper\Laravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use Sleeper\Laravel\Requests\Players\GetAllPlayers;
use Sleeper\Laravel\Requests\Players\GetTrendingPlayers;

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

    /**
     * Convenience helper: return enriched trending list with `player` details attached.
     */
    public function trendingArrayWithPlayers(?string $sport = null, ?int $lookbackHours = null, ?int $limit = null): array
    {
        $items = $this->trending($sport, $lookbackHours, $limit)->json();
        $ids = [];
        foreach ($items as $i) {
            $pid = $i['player_id'] ?? null;
            if ($pid) $ids[] = (string) $pid;
        }
        $players = \Sleeper\Laravel\Support\PlayerLookup::mapByIds($ids);
        foreach ($items as &$i) {
            if (($i['player_id'] ?? null) && isset($players[$i['player_id']])) {
                $i['player'] = $players[$i['player_id']];
            }
        }
        unset($i);
        return $items;
    }
}
