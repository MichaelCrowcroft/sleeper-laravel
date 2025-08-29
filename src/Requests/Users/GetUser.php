<?php

namespace Sleeper\Laravel\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUser extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $usernameOrId) {}

    public function resolveEndpoint(): string
    {
        return "/user/{$this->usernameOrId}";
    }
}

