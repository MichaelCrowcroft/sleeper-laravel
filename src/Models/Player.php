<?php

namespace MichaelCrowcroft\SleeperLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Player extends Model
{
    use Sushi;

    protected $primaryKey = 'player_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Avoid huge INSERT chunks on some SQLite installs if needed.
    public int $sushiInsertChunkSize = 100;

    // Provide a base schema so empty CSVs don't error
    protected $schema = [
        'player_id' => 'string',
        'full_name' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'position' => 'string',
        'team' => 'string',
    ];

    protected function sushiShouldCache(): bool
    {
        return true;
    }

    protected function sushiCacheReferencePath(): ?string
    {
        $path = (string) config('sleeper.players.csv_path');
        return $path ?: null;
    }

    public function getRows(): array
    {
        $path = (string) config('sleeper.players.csv_path');
        if (empty($path) || ! is_file($path)) {
            return [];
        }

        $rows = [];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = null;
        while (($data = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = $data;
                continue;
            }
            // Create associative row by header
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $data[$i] ?? null;
            }
            // Normalize eager-used fields
            if (!empty($row['fantasy_positions'])) {
                // Store fantasy_positions as an array for convenience
                $row['fantasy_positions'] = array_values(array_filter(explode('|', (string) $row['fantasy_positions'])));
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }
}
