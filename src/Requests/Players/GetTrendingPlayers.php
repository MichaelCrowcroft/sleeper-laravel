<?php

namespace MichaelCrowcroft\SleeperLaravel\Requests\Players;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTrendingPlayers extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $sport,
        protected string $type = 'add',
        protected ?int $lookbackHours = null,
        protected ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/players/{$this->sport}/trending/{$this->type}";
    }

    protected function defaultQuery(): array
    {
        $query = [];
        if ($this->lookbackHours !== null) {
            $query['lookback_hours'] = $this->lookbackHours;
        }
        if ($this->limit !== null) {
            $query['limit'] = $this->limit;
        }
        return $query;
    }
}
