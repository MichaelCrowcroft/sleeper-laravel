<?php

namespace Sleeper\Laravel\Requests\Leagues;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetLeagueTransactions extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $leagueId,
        protected int $round,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/league/{$this->leagueId}/transactions/{$this->round}";
    }
}

