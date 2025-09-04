<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetAllPlayers;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetTrendingPlayers;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetPlayerProjections;
use MichaelCrowcroft\SleeperLaravel\Requests\Players\GetPlayerStats;
use Illuminate\Support\Collection;
use GuzzleHttp\Psr7\Utils;

class PlayersResource extends BaseResource
{
    /**
     * Flatten weekly stats/projections data from the API response.
     *
     * The API returns data in the format:
     * {
     *   "1": { ... week 1 data ... },
     *   "2": { ... week 2 data ... },
     *   ...
     * }
     *
     * This method converts it to:
     * [
     *   { "week": 1, ... week 1 data ... },
     *   { "week": 2, ... week 2 data ... },
     *   ...
     * ]
     */
    protected function flattenWeeklyData(array $data): array
    {
        // Check if this is weekly data (numeric string keys)
        $weeklyKeys = array_filter(array_keys($data), fn($key) => is_numeric($key));

        if (empty($weeklyKeys)) {
            // Not weekly data, return as-is
            return $data;
        }

        $flattened = [];
        foreach ($data as $weekKey => $weekData) {
            if (is_numeric($weekKey) && is_array($weekData)) {
                // Add week number to the data
                $weekData['week'] = (int) $weekKey;
                $flattened[] = $weekData;
            }
        }

        // Sort by week number
        usort($flattened, fn($a, $b) => $a['week'] <=> $b['week']);

        return $flattened;
    }

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
        $response = $this->connector->send(new GetPlayerProjections(
            $playerId,
            $season,
            $sport,
            $seasonType,
            $grouping
        ));

        // Flatten weekly data if present
        if ($response->successful()) {
            $data = $response->json();
            if (is_array($data)) {
                $flattenedData = $this->flattenWeeklyData($data);

                // Create a new response with flattened data
                $newResponse = new \Saloon\Http\Response(
                    $response->getPsrResponse()->withBody(
                        Utils::streamFor(json_encode($flattenedData))
                    ),
                    $response->getPendingRequest(),
                    $response->getPsrRequest()
                );
                return $newResponse;
            }
        }

        return $response;
    }

    public function stats(
        string $playerId,
        string $season,
        ?string $sport = null,
        ?string $seasonType = null,
        ?string $grouping = null
    ): Response
    {
        $response = $this->connector->send(new GetPlayerStats(
            $playerId,
            $season,
            $sport,
            $seasonType,
            $grouping
        ));

        // Flatten weekly data if present
        if ($response->successful()) {
            $data = $response->json();
            if (is_array($data)) {
                $flattenedData = $this->flattenWeeklyData($data);

                // Create a new response with flattened data
                $newResponse = new \Saloon\Http\Response(
                    $response->getPsrResponse()->withBody(
                        Utils::streamFor(json_encode($flattenedData))
                    ),
                    $response->getPendingRequest(),
                    $response->getPsrRequest()
                );
                return $newResponse;
            }
        }

        return $response;
    }
}
