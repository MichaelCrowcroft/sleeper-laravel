<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\State\GetState;

class StateResource extends BaseResource
{
    public function current(?string $sport = null): Response
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        return $this->connector->send(new GetState($sport));
    }
}
