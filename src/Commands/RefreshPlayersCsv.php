<?php

namespace MichaelCrowcroft\SleeperLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use MichaelCrowcroft\SleeperLaravel\Sleeper;

class RefreshPlayersCsv extends Command
{
    protected $signature = 'sleeper:players:refresh 
        {--sport= : Sport key (default from config)}
        {--path= : Output CSV path (default from config)}';

    protected $description = 'Fetch Sleeper all-players dataset and cache it locally as CSV.';

    public function handle(Sleeper $sleeper): int
    {
        $sport = (string) ($this->option('sport') ?: config('sleeper.default_sport', 'nfl'));
        $path = (string) ($this->option('path') ?: config('sleeper.players.csv_path'));

        if (empty($path)) {
            $this->error('No output path configured. Set sleeper.players.csv_path or provide --path.');
            return self::FAILURE;
        }

        $this->info("Fetching players for sport '{$sport}'...");
        $response = $sleeper->players()->all($sport);
        if (!$response->successful()) {
            $this->error('Failed to fetch players: HTTP '.$response->status());
            return self::FAILURE;
        }

        $map = $response->json();
        if (!is_array($map)) {
            $this->error('Unexpected response format from Sleeper API.');
            return self::FAILURE;
        }

        // Build a union of keys for CSV header
        $union = [];
        $count = 0;
        foreach ($map as $id => $row) {
            if (!is_array($row)) continue;
            $row['player_id'] = (string) $row['player_id'] ?? (string) $id;
            foreach ($row as $k => $_) {
                $union[$k] = true;
            }
            $count++;
        }

        // Preferred ordering of the most useful columns
        $preferred = [
            'player_id', 'full_name', 'first_name', 'last_name', 'position', 'fantasy_positions', 'team', 'status', 'age', 'number', 'years_exp',
            'height', 'weight', 'college', 'birth_date', 'injury_status', 'injury_body_part', 'injury_start_date', 'news_updated', 'depth_chart_order',
            'espn_id', 'yahoo_id', 'sportradar_id', 'gsis_id', 'rotowire_id', 'rotoworld_id', 'search_full_name', 'search_last_name', 'search_rank',
        ];
        $allKeys = array_keys($union);
        $remaining = array_values(array_diff($allKeys, $preferred));
        sort($remaining);
        $header = array_values(array_unique(array_merge($preferred, $remaining)));

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($path));

        // Write CSV
        $fh = fopen($path, 'w');
        if ($fh === false) {
            $this->error('Unable to open output file for writing: '.$path);
            return self::FAILURE;
        }

        fputcsv($fh, $header);

        $written = 0;
        foreach ($map as $id => $row) {
            if (!is_array($row)) continue;
            $row['player_id'] = (string) ($row['player_id'] ?? $id);
            $flat = [];
            foreach ($header as $key) {
                $value = $row[$key] ?? null;
                if (is_array($value)) {
                    // Join common arrays as pipe-delimited, otherwise JSON-encode
                    if ($key === 'fantasy_positions') {
                        $value = implode('|', $value);
                    } else {
                        $value = json_encode($value);
                    }
                } elseif (is_bool($value)) {
                    $value = $value ? 1 : 0;
                }
                // Cast scalars to string to keep CSV simple
                $flat[] = is_scalar($value) || $value === null ? $value : (string) $value;
            }
            fputcsv($fh, $flat);
            $written++;
        }

        fclose($fh);

        $this->info("Wrote {$written} players to CSV: {$path}");

        return self::SUCCESS;
    }
}
