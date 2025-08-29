<?php

namespace MichaelCrowcroft\SleeperLaravel\Requests\Players;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAllPlayers extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $sport) {}

    public function resolveEndpoint(): string
    {
        return "/players/{$this->sport}";
    }
}
