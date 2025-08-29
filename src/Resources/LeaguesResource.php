<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeague;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueMatchups;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueRosters;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueTransactions;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueTradedPicks;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLeagueUsers;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetLosersBracket;
use MichaelCrowcroft\SleeperLaravel\Requests\Leagues\GetWinnersBracket;

class LeaguesResource extends BaseResource
{
    public function get(string $leagueId): Response
    {
        return $this->connector->send(new GetLeague($leagueId));
    }

    public function rosters(string $leagueId): Response
    {
        return $this->connector->send(new GetLeagueRosters($leagueId));
    }

    /**
     * Helper: rosters enriched with player details for each `players` list.
     */
    public function rostersWithPlayers(string $leagueId): array
    {
        $rosters = $this->rosters($leagueId)->json();
        $ids = [];
        foreach ($rosters as $r) {
            foreach ((array) ($r['players'] ?? []) as $pid) { $ids[] = (string) $pid; }
        }
        $players = \MichaelCrowcroft\SleeperLaravel\Support\PlayerLookup::mapByIds($ids);
        foreach ($rosters as &$r) {
            $r['players_info'] = array_values(array_filter(array_map(fn ($pid) => $players[$pid] ?? null, (array) ($r['players'] ?? []))));
        }
        unset($r);
        return $rosters;
    }

    public function users(string $leagueId): Response
    {
        return $this->connector->send(new GetLeagueUsers($leagueId));
    }

    public function matchups(string $leagueId, int $week): Response
    {
        return $this->connector->send(new GetLeagueMatchups($leagueId, $week));
    }

    public function winnersBracket(string $leagueId): Response
    {
        return $this->connector->send(new GetWinnersBracket($leagueId));
    }

    public function losersBracket(string $leagueId): Response
    {
        return $this->connector->send(new GetLosersBracket($leagueId));
    }

    public function transactions(string $leagueId, int $round): Response
    {
        return $this->connector->send(new GetLeagueTransactions($leagueId, $round));
    }

    /**
     * Helper: transactions enriched with player details for adds/drops.
     */
    public function transactionsWithPlayers(string $leagueId, int $round): array
    {
        $tx = $this->transactions($leagueId, $round)->json();
        $ids = [];
        foreach ($tx as $t) {
            foreach (array_keys((array) ($t['adds'] ?? [])) as $pid) { $ids[] = (string) $pid; }
            foreach (array_keys((array) ($t['drops'] ?? [])) as $pid) { $ids[] = (string) $pid; }
        }
        $players = \MichaelCrowcroft\SleeperLaravel\Support\PlayerLookup::mapByIds($ids);
        foreach ($tx as &$t) {
            $t['adds_players'] = array_values(array_filter(array_map(fn ($pid) => $players[$pid] ?? null, array_keys((array) ($t['adds'] ?? [])))));
            $t['drops_players'] = array_values(array_filter(array_map(fn ($pid) => $players[$pid] ?? null, array_keys((array) ($t['drops'] ?? [])))));
        }
        unset($t);
        return $tx;
    }

    public function tradedPicks(string $leagueId): Response
    {
        return $this->connector->send(new GetLeagueTradedPicks($leagueId));
    }

    // -------- Helpers (combined use-cases) --------

    public function standings(string $leagueId): array
    {
        $rosters = $this->rosters($leagueId)->json();
        $users = $this->users($leagueId)->json();
        $userById = [];
        foreach ($users as $user) {
            // We'll identify roster->owner mapping via user_id later
            $userById[$user['user_id']] = $user;
        }

        $standings = [];
        foreach ($rosters as $roster) {
            $ownerId = $roster['owner_id'] ?? null;
            $user = $ownerId && isset($userById[$ownerId]) ? $userById[$ownerId] : null;
            $settings = $roster['settings'] ?? [];

            $standings[] = [
                'roster_id' => $roster['roster_id'] ?? null,
                'owner_id' => $ownerId,
                'team_name' => $user['metadata']['team_name'] ?? $user['display_name'] ?? null,
                'wins' => $settings['wins'] ?? 0,
                'losses' => $settings['losses'] ?? 0,
                'ties' => $settings['ties'] ?? 0,
                'fpts' => $settings['fpts'] ?? 0,
                'fpts_decimal' => $settings['fpts_decimal'] ?? 0,
                'fpts_against' => $settings['fpts_against'] ?? 0,
                'fpts_against_decimal' => $settings['fpts_against_decimal'] ?? 0,
            ];
        }

        // Sort by wins desc, then fpts desc, then fpts_decimal desc
        usort($standings, function ($a, $b) {
            return [$b['wins'], $b['fpts'], $b['fpts_decimal']] <=> [$a['wins'], $a['fpts'], $a['fpts_decimal']];
        });

        return $standings;
    }

    public function matchupsWithUsers(string $leagueId, int $week): array
    {
        $matchups = $this->matchups($leagueId, $week)->json();
        $users = $this->users($leagueId)->json();
        $userByRosterId = [];

        // Build rosterId -> user map via rosters
        $rosters = $this->rosters($leagueId)->json();
        $ownerByRosterId = [];
        foreach ($rosters as $r) {
            $ownerByRosterId[$r['roster_id']] = $r['owner_id'] ?? null;
        }
        $userById = [];
        foreach ($users as $u) {
            $userById[$u['user_id']] = $u;
        }
        foreach ($ownerByRosterId as $rosterId => $ownerId) {
            $userByRosterId[$rosterId] = $ownerId && isset($userById[$ownerId]) ? $userById[$ownerId] : null;
        }

        $pairs = [];
        $allPlayerIds = [];
        foreach ($matchups as $m) {
            $mid = $m['matchup_id'] ?? null;
            if ($mid === null) {
                // Some weeks may not have explicit matchup_id; group by roster_id standalone
                $mid = 'roster_'.$m['roster_id'];
            }
            $pairs[$mid]['matchup_id'] = $mid;
            $pairs[$mid]['teams'][] = [
                'roster_id' => $m['roster_id'] ?? null,
                'points' => $m['points'] ?? 0,
                'starters' => $m['starters'] ?? [],
                'players' => $m['players'] ?? [],
                'user' => $userByRosterId[$m['roster_id']] ?? null,
            ];

            // Collect player IDs for enrichment
            foreach (($m['players'] ?? []) as $pid) {
                if ($pid) { $allPlayerIds[] = (string) $pid; }
            }
            foreach (($m['starters'] ?? []) as $pid) {
                if ($pid) { $allPlayerIds[] = (string) $pid; }
            }
        }

        // Enrich with player details from local Sushi model
        $playerMap = \MichaelCrowcroft\SleeperLaravel\Support\PlayerLookup::mapByIds($allPlayerIds);

        // Attach enriched data per team
        foreach ($pairs as &$pair) {
            foreach ($pair['teams'] as &$team) {
                $teamPlayers = (array) ($team['players'] ?? []);
                $team['players_info'] = array_values(array_filter(array_map(fn ($id) => $playerMap[$id] ?? null, $teamPlayers)));
                $starters = (array) ($team['starters'] ?? []);
                $team['starters_info'] = array_values(array_filter(array_map(fn ($id) => $playerMap[$id] ?? null, $starters)));
            }
        }
        unset($pair, $team);

        return array_values($pairs);
    }
}
