<?php

namespace MichaelCrowcroft\SleeperLaravel\Fluent;

use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeague;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueMatchups;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueRosters;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueTransactions;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueTradedPicks;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueUsers;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLosersBracket;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetWinnersBracket;
use MichaelCrowcroft\SleeperLaravel\Sleeper;

class LeagueContext
{
    public function __construct(
        protected Sleeper $connector,
        protected ?string $userId,
        protected string $leagueId,
    ) {}

    protected ?array $leagueCache = null;
    protected ?array $usersCache = null;
    protected ?array $rostersCache = null;
    protected ?array $winnersBracketCache = null;
    protected ?array $losersBracketCache = null;
    protected array $matchupsCache = [];
    protected array $transactionsCache = [];
    protected array $stateCache = [];

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function leagueId(): string
    {
        return $this->leagueId;
    }

    public function get(): Response
    {
        return $this->connector->send(new GetLeague($this->leagueId));
    }

    public function getArray(): array
    {
        if ($this->leagueCache !== null) {
            return $this->leagueCache;
        }
        return $this->leagueCache = $this->get()->json();
    }

    public function name(): ?string
    {
        return $this->getArray()['name'] ?? null;
    }

    public function avatarUrl(): ?string
    {
        $avatar = $this->getArray()['avatar'] ?? null;
        return $this->connector->avatars()->fullUrl($avatar);
    }

    public function rosters(): Response
    {
        return $this->connector->send(new GetLeagueRosters($this->leagueId));
    }

    public function rostersArray(): array
    {
        if ($this->rostersCache !== null) {
            return $this->rostersCache;
        }
        return $this->rostersCache = $this->rosters()->json();
    }

    public function users(): Response
    {
        return $this->connector->send(new GetLeagueUsers($this->leagueId));
    }

    public function usersArray(): array
    {
        if ($this->usersCache !== null) {
            return $this->usersCache;
        }
        return $this->usersCache = $this->users()->json();
    }

    public function matchups(?int $week = null, ?string $sport = null): Response
    {
        $week = $week ?? $this->currentWeek($sport);
        return $this->connector->send(new GetLeagueMatchups($this->leagueId, $week));
    }

    public function matchupsArray(?int $week = null, ?string $sport = null): array
    {
        $week = $week ?? $this->currentWeek($sport);
        if (isset($this->matchupsCache[$week])) {
            return $this->matchupsCache[$week];
        }
        return $this->matchupsCache[$week] = $this->matchups($week, $sport)->json();
    }

    public function winnersBracket(): Response
    {
        return $this->connector->send(new GetWinnersBracket($this->leagueId));
    }

    public function winnersBracketArray(): array
    {
        if ($this->winnersBracketCache !== null) {
            return $this->winnersBracketCache;
        }
        return $this->winnersBracketCache = $this->winnersBracket()->json();
    }

    public function losersBracket(): Response
    {
        return $this->connector->send(new GetLosersBracket($this->leagueId));
    }

    public function losersBracketArray(): array
    {
        if ($this->losersBracketCache !== null) {
            return $this->losersBracketCache;
        }
        return $this->losersBracketCache = $this->losersBracket()->json();
    }

    public function transactions(?int $round = null, ?string $sport = null): Response
    {
        $round = $round ?? $this->currentRound($sport);
        return $this->connector->send(new GetLeagueTransactions($this->leagueId, $round));
    }

    public function transactionsArray(?int $round = null, ?string $sport = null): array
    {
        $round = $round ?? $this->currentRound($sport);
        if (isset($this->transactionsCache[$round])) {
            return $this->transactionsCache[$round];
        }
        return $this->transactionsCache[$round] = $this->transactions($round, $sport)->json();
    }

    public function transactionsArrayWithPlayers(?int $round = null, ?string $sport = null): array
    {
        $round = $round ?? $this->currentRound($sport);
        return $this->connector->leagues()->transactionsWithPlayers($this->leagueId, $round);
    }

    public function tradedPicks(): Response
    {
        return $this->connector->send(new GetLeagueTradedPicks($this->leagueId));
    }

    public function tradedPicksArray(): array
    {
        return $this->tradedPicks()->json();
    }

    // Helpers
    public function standings(): array
    {
        return $this->connector->leagues()->standings($this->leagueId);
    }

    public function matchupsWithUsers(?int $week = null, ?string $sport = null): array
    {
        $week = $week ?? $this->currentWeek($sport);
        return $this->connector->leagues()->matchupsWithUsers($this->leagueId, $week);
    }

    public function matchupsWithUsersAndPlayers(?int $week = null, ?string $sport = null): array
    {
        return $this->matchupsWithUsers($week, $sport);
    }

    public function scoreboard(?int $week = null, ?string $sport = null): array
    {
        return $this->matchupsWithUsers($week, $sport);
    }

    public function teamMap(): array
    {
        $users = $this->usersArray();
        $rosters = $this->rostersArray();
        $userById = [];
        foreach ($users as $u) {
            $userById[$u['user_id']] = $u;
        }
        $map = [];
        foreach ($rosters as $r) {
            $rosterId = $r['roster_id'];
            $ownerId = $r['owner_id'] ?? null;
            $user = $ownerId && isset($userById[$ownerId]) ? $userById[$ownerId] : null;
            $teamName = $user['metadata']['team_name'] ?? ($user['display_name'] ?? null);
            $avatarUrl = $this->connector->avatars()->fullUrl($user['avatar'] ?? null);
            $map[$rosterId] = [
                'owner_id' => $ownerId,
                'user' => $user,
                'team_name' => $teamName,
                'avatar_url' => $avatarUrl,
            ];
        }
        return $map;
    }

    public function ownerForRoster(int $rosterId): ?array
    {
        $map = $this->teamMap();
        return $map[$rosterId]['user'] ?? null;
    }

    public function transactionsByType(string $type, ?int $round = null, ?string $sport = null): array
    {
        $tx = $this->transactionsArray($round, $sport);
        return array_values(array_filter($tx, fn ($t) => ($t['type'] ?? null) === $type));
    }

    public function trades(?int $round = null, ?string $sport = null): array
    {
        return $this->transactionsByType('trade', $round, $sport);
    }

    public function waivers(?int $round = null, ?string $sport = null): array
    {
        return $this->transactionsByType('waiver', $round, $sport);
    }

    public function freeAgents(?int $round = null, ?string $sport = null): array
    {
        return $this->transactionsByType('free_agent', $round, $sport);
    }

    public function drafts(): Response
    {
        return $this->connector->drafts()->forLeague($this->leagueId);
    }

    public function draftsArray(): array
    {
        return $this->drafts()->json();
    }

    public function latestDraft(): ?DraftContext
    {
        $drafts = $this->draftsArray();
        if (empty($drafts)) {
            return null;
        }
        $latest = $drafts[0];
        return new DraftContext($this->connector, $this->userId, $latest['draft_id']);
    }

    protected function currentWeek(?string $sport = null): int
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        $state = $this->stateArray($sport);
        return (int) (($state['display_week'] ?? null) ?? ($state['week'] ?? null) ?? ($state['leg'] ?? 1));
    }

    protected function currentRound(?string $sport = null): int
    {
        // For transactions, docs use "round" which in football maps to the current week
        return $this->currentWeek($sport);
    }

    protected function stateArray(string $sport): array
    {
        if (isset($this->stateCache[$sport])) {
            return $this->stateCache[$sport];
        }
        return $this->stateCache[$sport] = $this->connector->state()->current($sport)->json();
    }
}
