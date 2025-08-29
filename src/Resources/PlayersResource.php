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

    /**
     * Convenience helper: return trending list with player details merged into each item.
     *
     * Example output item:
     * [
     *   'player_id' => '4034',
     *   'count' => 123,
     *   ... attributes from Sushi Player model
     * ]
     */
    public function trendingArrayWithPlayers(?string $sport = null, ?int $lookbackHours = null, ?int $limit = null): array
    {
        $items = $this->trending($sport, $lookbackHours, $limit)->json();
        $ids = [];
        foreach ($items as $i) {
            $pid = $i['player_id'] ?? null;
            if ($pid) $ids[] = (string) $pid;
        }
        $players = \MichaelCrowcroft\SleeperLaravel\Support\PlayerLookup::mapByIds($ids);
        foreach ($items as &$i) {
            $pid = $i['player_id'] ?? null;
            if ($pid && isset($players[$pid])) {
                // Merge player attributes into the item. Preserve existing item keys on collision.
                $i = $i + $players[$pid];
            }
        }
        unset($i);
        return $items;
    }

    /**
     * Alias of trendingArrayWithPlayers for a cleaner name.
     */
    public function trendingWithPlayers(?string $sport = null, ?int $lookbackHours = null, ?int $limit = null): array
    {
        return $this->trendingArrayWithPlayers($sport, $lookbackHours, $limit);
    }
}
