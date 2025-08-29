<?php

namespace MichaelCrowcroft\SleeperLaravel\Requests\Leagues;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetLosersBracket extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $leagueId) {}

    public function resolveEndpoint(): string
    {
        // API doc typo sometimes shows 'loses_bracket' – the correct endpoint is 'losers_bracket'
        return "/league/{$this->leagueId}/losers_bracket";
    }
}
