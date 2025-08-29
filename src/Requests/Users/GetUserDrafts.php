<?php

namespace Sleeper\Laravel\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserDrafts extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $userId,
        protected string $sport,
        protected string $season,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/user/{$this->userId}/drafts/{$this->sport}/{$this->season}";
    }
}

