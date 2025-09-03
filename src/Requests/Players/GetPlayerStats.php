<?php

namespace MichaelCrowcroft\SleeperLaravel\Requests\Players;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPlayerStats extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $playerId,
        protected string $season,
        protected ?string $sport = null,
        protected ?string $seasonType = null,
        protected ?string $grouping = null,
    ) {
        $this->sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
    }

    public function resolveEndpoint(): string
    {
        return "/stats/{$this->sport}/player/{$this->playerId}";
    }

    protected function defaultQuery(): array
    {
        $query = [
            'season' => $this->season,
            'season_type' => $this->seasonType ?? 'regular',
        ];

        if ($this->grouping !== null) {
            $query['grouping'] = $this->grouping;
        }

        return $query;
    }
}
