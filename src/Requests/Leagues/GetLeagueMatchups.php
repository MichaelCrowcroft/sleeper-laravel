<?php

namespace Sleeper\Laravel\Requests\Leagues;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetLeagueMatchups extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $leagueId,
        protected int $week,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/league/{$this->leagueId}/matchups/{$this->week}";
    }
}

