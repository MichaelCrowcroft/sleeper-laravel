<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Drafts\GetDraft;
use MichaelCrowcroft\SleeperLaravel\Requests\Drafts\GetDraftPicks;
use MichaelCrowcroft\SleeperLaravel\Requests\Drafts\GetDraftTradedPicks;
use MichaelCrowcroft\SleeperLaravel\Requests\Drafts\GetLeagueDrafts;

class DraftsResource extends BaseResource
{
    public function forLeague(string $leagueId): Response
    {
        return $this->connector->send(new GetLeagueDrafts($leagueId));
    }

    public function get(string $draftId): Response
    {
        return $this->connector->send(new GetDraft($draftId));
    }

    public function picks(string $draftId): Response
    {
        return $this->connector->send(new GetDraftPicks($draftId));
    }

    public function tradedPicks(string $draftId): Response
    {
        return $this->connector->send(new GetDraftTradedPicks($draftId));
    }

    // Helper: Produce a draft board grouped by round
    public function board(string $draftId): array
    {
        $picks = $this->picks($draftId)->json();
        $board = [];

        // Enrich: build a player map for any player_id present in picks
        $playerIds = [];
        foreach ($picks as $pick) {
            $pid = $pick['player_id'] ?? null;
            if ($pid) $playerIds[] = (string) $pid;
        }
        $players = \MichaelCrowcroft\SleeperLaravel\Support\PlayerLookup::mapByIds($playerIds);

        foreach ($picks as $pick) {
            $round = (int) ($pick['round'] ?? 0);
            if (($pick['player_id'] ?? null) && isset($players[$pick['player_id']])) {
                $pick['player'] = $players[$pick['player_id']];
            }
            $board[$round][] = $pick;
        }
        ksort($board);
        return $board;
    }
}
