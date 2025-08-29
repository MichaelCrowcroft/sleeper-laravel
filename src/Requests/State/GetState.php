<?php

namespace MichaelCrowcroft\SleeperLaravel\Requests\State;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetState extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $sport)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/state/{$this->sport}";
    }
}
