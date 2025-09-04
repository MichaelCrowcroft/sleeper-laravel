# Sleeper Laravel SDK

A fluent Laravel SDK for the Sleeper API built on Saloon. It groups endpoints into discoverable resources and provides helpful convenience methods for common use-cases, including automatic data flattening for player projections and stats.

## Installation

You can install the package via composer:

```bash
composer require michaelcrowcroft/sleeper-laravel
```

(Optional) Publish the config file if you want to customise timeouts or base URLs:

```bash
php artisan vendor:publish --tag="sleeper-config"
```

## Usage

```php
use Sleeper\Laravel\Facades\Sleeper;

// Get a user by username or user_id
$user = Sleeper::users()->get('sleeperuser')->json();

// Get user's leagues for the current season (sport defaults to nfl)
$leagues = Sleeper::users()->leaguesForCurrentSeason($user['user_id'])->json();

// League details
$league = Sleeper::leagues()->get($leagues[0]['league_id'])->json();

// League rosters & users
$rosters = Sleeper::leagues()->rosters($league['league_id'])->json();
$users   = Sleeper::leagues()->users($league['league_id'])->json();

// Helpful helpers
$standings = Sleeper::leagues()->standings($league['league_id']);
$wk1MatchupsWithUsers = Sleeper::leagues()->matchupsWithUsers($league['league_id'], 1);

// Drafts
$drafts = Sleeper::drafts()->forLeague($league['league_id'])->json();
$picks  = Sleeper::drafts()->picks($drafts[0]['draft_id'])->json();

// Players
$playersMap = Sleeper::players()->all()->json();

// Player Projections
// Get weekly projections (returns flattened array with 'week' property)
$weeklyProjections = Sleeper::players()->projections('6794', '2025', grouping: 'week')->json();

// Get season-aggregated projections (grouping omitted or set to 'season')
$seasonProjections = Sleeper::players()->projections('6794', '2025')->json();

// Player Stats
// Get weekly stats (returns flattened array with 'week' property)
$weeklyStats = Sleeper::players()->stats('6794', '2024', grouping: 'week')->json();

// Get season-aggregated stats (grouping omitted or set to 'season')
$seasonStats = Sleeper::players()->stats('6794', '2024')->json();

// Enriched trending (includes `player` info from local Sushi model)
$trendingAdds = Sleeper::players()->trendingWithPlayers(lookbackHours: 24, limit: 25);

// Avatars
$avatarUrl = Sleeper::avatars()->fullUrl($user['avatar'] ?? null);
```

### Player Projections & Stats

The SDK provides methods to fetch player projections and stats with automatic data flattening for easier consumption.

#### Method Signatures

```php
// projections(string $playerId, string $season, ?string $sport = null, ?string $seasonType = null, ?string $grouping = null)
// stats(string $playerId, string $season, ?string $sport = null, ?string $seasonType = null, ?string $grouping = null)
```

#### Parameters

- **`$playerId`** (required): The Sleeper player ID (e.g., '6794' for Justin Jefferson)
- **`$season`** (required): Season year (e.g., '2024', '2025')
- **`$sport`** (optional): Sport type, defaults to 'nfl'
- **`$seasonType`** (optional): Season type, defaults to 'regular'
- **`$grouping`** (optional): Data grouping - 'week', 'season', or null

#### Grouping Parameter

The `grouping` parameter controls how data is aggregated:

- **`'week'`**: Returns week-by-week data as a flattened array
- **`'season'` or `null`**: Returns season-aggregated data
- **Omitted**: Returns season-aggregated data (same as `'season'`)

#### Data Flattening

The API returns weekly data in this format:
```json
{
  "1": { "date": "2024-09-08", "pts_half_ppr": 13.9, "week": 1, ... },
  "2": { "date": "2024-09-15", "pts_half_ppr": 16.1, "week": 2, ... },
  "3": { "date": "2024-09-22", "pts_half_ppr": 8.2, "week": 3, ... }
}
```

The SDK automatically flattens this to:
```json
[
  { "week": 1, "date": "2024-09-08", "pts_half_ppr": 13.9, ... },
  { "week": 2, "date": "2024-09-15", "pts_half_ppr": 16.1, ... },
  { "week": 3, "date": "2024-09-22", "pts_half_ppr": 8.2, ... }
]
```

#### Examples

```php
// Weekly projections (flattened array)
$weekly = Sleeper::players()->projections('6794', '2025', grouping: 'week')->json();
// Result: [{ "week": 1, "date": "2025-09-08", "stats": {...}, ... }, ...]

// Season-aggregated projections
$season = Sleeper::players()->projections('6794', '2025')->json();
// Result: { "season": "2025", "total_points": 150.5, ... }

// Weekly stats (flattened array)
$weeklyStats = Sleeper::players()->stats('6794', '2024', grouping: 'week')->json();
// Result: [{ "week": 1, "date": "2024-09-08", "pts_half_ppr": 13.9, ... }, ...]

// Season-aggregated stats
$seasonStats = Sleeper::players()->stats('6794', '2024')->json();
// Result: { "season": "2024", "total_points": 200.3, ... }
```

#### Response Format

**Weekly Data (when `grouping = 'week'`):**
```php
[
  {
    "week": 1,
    "date": "2024-09-08",
    "status": null,
    "anytime_tds": 1,
    "pts_half_ppr": 13.9,
    "pts_ppr": 15.9,
    "pts_std": 11.9,
    "rec": 4,
    "rec_td": 1,
    "category": "stat",
    "last_modified": 1725894573070,
    "season": "2024",
    "season_type": "regular",
    "sport": "nfl",
    "team": "MIN",
    "player_id": "6794",
    "updated_at": 1725894573070,
    "game_id": "202410123",
    "opponent": "NYG"
  },
  // ... more weeks
]
```

**Season Data (when `grouping` is omitted or `'season'`):**
```php
{
  "season": "2024",
  "player_id": "6794",
  "total_points": 200.3,
  "games_played": 15,
  // ... other aggregated stats
}
```

### Fluent Context API

You can also set a user and league (or draft) context and call league/draft-specific methods fluently. Week/round defaults are pulled from Sleeper state (display_week/week/leg). When an ID isn’t required, you can omit it.

```php
// League context (with or without user)
Sleeper::league('289646328504385536')
    ->rosters();         // GET /league/{league_id}/rosters

Sleeper::league('289646328504385536')
    ->users();           // GET /league/{league_id}/users

// Defaults to current week from state when no week provided
Sleeper::league('289646328504385536')
    ->matchups();        // GET /league/{league_id}/matchups/{current_week}

// Defaults to current week as round when no round provided
Sleeper::league('289646328504385536')
    ->transactions();    // GET /league/{league_id}/transactions/{current_week}

// Extra helpers available on league context
$standings = Sleeper::league('289646328504385536')->standings();
$pairs = Sleeper::league('289646328504385536')->matchupsWithUsers();

// Draft context (with or without user)
Sleeper::draft('257270643320426496')->picks();
Sleeper::draft('257270643320426496')->tradedPicks();
Sleeper::draft('257270643320426496')->board();

// You can also infer the first league and latest draft from a user
Sleeper::user('12345678')->league()->rosters();     // uses first league of current season
Sleeper::user('12345678')->draft()->picks();        // uses latest draft of first league
```

### Convenience Helpers

These helpers cache within the context instance to avoid repeat network calls.

- User context:
  - displayName(), avatarUrl()
  - leaguesArray(sport?, season?), currentLeagues(sport?)
  - firstLeague(sport?): LeagueContext|null
  - findLeagueByName(name, sport?, season?): LeagueContext|null
  - leagueIds(sport?, season?): array

- League context:
  - name(), avatarUrl()
  - usersArray(), rostersArray()
  - teamMap(): roster_id => { owner_id, user, team_name, avatar_url }
  - ownerForRoster(rosterId)
  - scoreboard(week? = current): pairs matchups with users
  - matchupsArray(week?), transactionsArray(round?), tradedPicksArray()
  - winnersBracketArray(), losersBracketArray()
  - drafts(), draftsArray(), latestDraft(): DraftContext|null

- Draft context:
  - getArray(), picksArray(), tradedPicksArray(), board()

- Players context:
  - all(): Get all players map
  - trending(type?, lookbackHours?, limit?): Get trending players
  - projections(playerId, season, sport?, seasonType?, grouping?): Get player projections with automatic flattening
  - stats(playerId, season, sport?, seasonType?, grouping?): Get player stats with automatic flattening

**Tip**: Use named parameters for better readability: `Sleeper::players()->projections('6794', '2025', grouping: 'week')`

## Players CSV Cache + Sushi Model

Sleeper returns player info only from the heavy “all players” endpoint (≈5MB) keyed by `player_id`. To make player lookup fast and ergonomic across responses:

- Command: `php artisan sleeper:players:refresh` downloads the all-players dataset and saves a CSV locally.
- Default path: `storage/app/sleeper/players.csv`
  - Override via env `SLEEPER_PLAYERS_CSV` or `config('sleeper.players.csv_path')`.
- Model: `Sleeper\Laravel\Models\Player` uses Sushi to read from the CSV and caches based on the CSV file mtime.

Examples:

```php
use Sleeper\Laravel\Models\Player;

// Generate CSV once (or as needed):
// php artisan sleeper:players:refresh --sport=nfl

// Look up by id
$p = Player::find('4034');

// Query with Eloquent
$wrs = Player::where('position', 'WR')->limit(10)->get();
```

Enriched helpers that blend player data wherever `player_id` appears:

- `Sleeper::players()->trendingArrayWithPlayers()` / `trendingWithPlayers()` → merges Sushi player attributes into each item.
- `Sleeper::leagues()->matchupsWithUsers(leagueId, week)` → each team now includes `players_info` and `starters_info` arrays.
- `Sleeper::drafts()->board(draftId)` → each pick includes a `player` key when resolvable.
- `Sleeper::leagues()->transactionsWithPlayers(leagueId, round)` → adds `adds_players` and `drops_players` arrays.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- Michael Crowcroft
- Built with Saloon PHP

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
