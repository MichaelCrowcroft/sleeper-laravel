<?php

namespace MichaelCrowcroft\SleeperLaravel\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserLeagues extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $userId,
        protected string $sport,
        protected string $season,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/user/{$this->userId}/leagues/{$this->sport}/{$this->season}";
    }
}
