<?php

namespace Sleeper\Laravel\Fluent;

use Saloon\Http\Response;
use Sleeper\Laravel\Requests\Drafts\GetDraft;
use Sleeper\Laravel\Requests\Drafts\GetDraftPicks;
use Sleeper\Laravel\Requests\Drafts\GetDraftTradedPicks;
use Sleeper\Laravel\Sleeper;

class DraftContext
{
    public function __construct(
        protected Sleeper $connector,
        protected ?string $userId,
        protected string $draftId,
    ) {}

    protected ?array $draftCache = null;
    protected ?array $picksCache = null;
    protected ?array $tradedPicksCache = null;

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function draftId(): string
    {
        return $this->draftId;
    }

    public function get(): Response
    {
        return $this->connector->send(new GetDraft($this->draftId));
    }

    public function getArray(): array
    {
        if ($this->draftCache !== null) {
            return $this->draftCache;
        }
        return $this->draftCache = $this->get()->json();
    }

    public function picks(): Response
    {
        return $this->connector->send(new GetDraftPicks($this->draftId));
    }

    public function picksArray(): array
    {
        if ($this->picksCache !== null) {
            return $this->picksCache;
        }
        return $this->picksCache = $this->picks()->json();
    }

    public function picksArrayWithPlayers(): array
    {
        $picks = $this->picksArray();
        $ids = [];
        foreach ($picks as $p) {
            $pid = $p['player_id'] ?? null;
            if ($pid) $ids[] = (string) $pid;
        }
        $players = \Sleeper\Laravel\Support\PlayerLookup::mapByIds($ids);
        foreach ($picks as &$p) {
            if (($p['player_id'] ?? null) && isset($players[$p['player_id']])) {
                $p['player'] = $players[$p['player_id']];
            }
        }
        unset($p);
        return $picks;
    }

    public function tradedPicks(): Response
    {
        return $this->connector->send(new GetDraftTradedPicks($this->draftId));
    }

    public function tradedPicksArray(): array
    {
        if ($this->tradedPicksCache !== null) {
            return $this->tradedPicksCache;
        }
        return $this->tradedPicksCache = $this->tradedPicks()->json();
    }

    public function board(): array
    {
        return $this->connector->drafts()->board($this->draftId);
    }
}
