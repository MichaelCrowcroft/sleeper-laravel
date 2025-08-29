<?php

namespace MichaelCrowcroft\SleeperLaravel\Fluent;

use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Users\GetUser;
use MichaelCrowcroft\SleeperLaravel\Requests\Users\GetUserDrafts;
use MichaelCrowcroft\SleeperLaravel\Requests\Users\GetUserLeagues;
use MichaelCrowcroft\SleeperLaravel\Sleeper;

class UserContext
{
    public function __construct(
        protected Sleeper $connector,
        protected string $userId,
    ) {}

    protected ?array $userCache = null;
    protected array $leaguesCache = []; // key: sport|season
    protected array $stateCache = [];   // key: sport

    public function id(): string
    {
        return $this->userId;
    }

    public function get(): Response
    {
        return $this->connector->send(new GetUser($this->userId));
    }

    public function getArray(): array
    {
        if ($this->userCache !== null) {
            return $this->userCache;
        }
        return $this->userCache = $this->get()->json();
    }

    public function displayName(): ?string
    {
        return $this->getArray()['display_name'] ?? null;
    }

    public function avatarUrl(): ?string
    {
        $avatar = $this->getArray()['avatar'] ?? null;
        return $this->connector->avatars()->fullUrl($avatar);
    }

    public function leagues(?string $sport = null, ?string $season = null): Response
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        if ($season === null) {
            $state = $this->stateArray($sport);
            $season = (string) (($state['league_season'] ?? null) ?? ($state['season'] ?? ''));
        }

        return $this->connector->send(new GetUserLeagues($this->userId, $sport, $season));
    }

    public function leaguesArray(?string $sport = null, ?string $season = null): array
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        if ($season === null) {
            $state = $this->stateArray($sport);
            $season = (string) (($state['league_season'] ?? null) ?? ($state['season'] ?? ''));
        }
        $key = $sport.'|'.$season;
        if (array_key_exists($key, $this->leaguesCache)) {
            return $this->leaguesCache[$key];
        }
        return $this->leaguesCache[$key] = $this->leagues($sport, $season)->json();
    }

    public function drafts(?string $sport = null, ?string $season = null): Response
    {
        $sport = $sport ?? (string) config('sleeper.default_sport', 'nfl');
        if ($season === null) {
            $state = $this->stateArray($sport);
            $season = (string) (($state['league_season'] ?? null) ?? ($state['season'] ?? ''));
        }

        return $this->connector->send(new GetUserDrafts($this->userId, $sport, $season));
    }

    public function draftsArray(?string $sport = null, ?string $season = null): array
    {
        return $this->drafts($sport, $season)->json();
    }

    public function league(?string $leagueId = null): LeagueContext
    {
        if ($leagueId === null) {
            $lc = $this->firstLeague();
            if ($lc === null) {
                throw new \InvalidArgumentException('No leagues found for user to infer league context. Provide a league_id.');
            }
            return $lc;
        }
        return new LeagueContext($this->connector, $this->userId, $leagueId);
    }

    public function draft(?string $draftId = null): DraftContext
    {
        if ($draftId === null) {
            $league = $this->firstLeague();
            if ($league === null) {
                throw new \InvalidArgumentException('No leagues found for user to infer draft context. Provide a draft_id.');
            }
            $latest = $league->latestDraft();
            if ($latest === null) {
                throw new \InvalidArgumentException('No drafts found for user\'s league to infer draft context. Provide a draft_id.');
            }
            return $latest;
        }
        return new DraftContext($this->connector, $this->userId, $draftId);
    }

    public function currentLeagues(?string $sport = null): array
    {
        return $this->leaguesArray($sport, null);
    }

    public function firstLeague(?string $sport = null): ?LeagueContext
    {
        $leagues = $this->currentLeagues($sport);
        if (empty($leagues)) {
            return null;
        }
        return $this->league($leagues[0]['league_id']);
    }

    public function findLeagueByName(string $name, ?string $sport = null, ?string $season = null): ?LeagueContext
    {
        $leagues = $this->leaguesArray($sport, $season);
        foreach ($leagues as $league) {
            if (strcasecmp((string) ($league['name'] ?? ''), $name) === 0) {
                return $this->league($league['league_id']);
            }
        }
        return null;
    }

    public function leagueIds(?string $sport = null, ?string $season = null): array
    {
        return array_map(fn ($l) => $l['league_id'], $this->leaguesArray($sport, $season));
    }

    protected function stateArray(string $sport): array
    {
        if (isset($this->stateCache[$sport])) {
            return $this->stateCache[$sport];
        }
        return $this->stateCache[$sport] = $this->connector->state()->current($sport)->json();
    }
}
