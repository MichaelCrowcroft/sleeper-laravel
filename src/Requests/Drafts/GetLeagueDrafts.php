<?php

namespace Sleeper\Laravel\Requests\Drafts;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetLeagueDrafts extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $leagueId) {}

    public function resolveEndpoint(): string
    {
        return "/league/{$this->leagueId}/drafts";
    }
}

