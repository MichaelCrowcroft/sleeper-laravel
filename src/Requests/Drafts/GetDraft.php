<?php

namespace Sleeper\Laravel\Requests\Drafts;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDraft extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $draftId) {}

    public function resolveEndpoint(): string
    {
        return "/draft/{$this->draftId}";
    }
}

