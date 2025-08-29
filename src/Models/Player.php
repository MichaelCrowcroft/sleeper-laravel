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

    // Keep chunk size modest for SQLite inserts
    public int $sushiInsertChunkSize = 100;

    // Minimal schema so Sushi can create an empty table if CSV is missing
    protected $schema = [
        'player_id' => 'string',
        'full_name' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'position' => 'string',
        'team' => 'string',
        'fantasy_positions' => 'string',
    ];

    protected function sushiShouldCache(): bool
    {
        return true;
    }

    protected function sushiCacheReferencePath(): ?string
    {
        return (string) config('sleeper.players.csv_path');

    }

    public function getRows(): array
    {
        $path = (string) config('sleeper.players.csv_path');

        $rows = [];
        $handle = fopen($path, 'r');

        $header = null;
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if ($header === null) {
                $header = $data;
                continue;
            }
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $data[$i] ?? null;
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    // Accessor: expose fantasy_positions as array while storing as pipe-delimited string
    public function getFantasyPositionsAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            return array_values(array_filter(explode('|', $value)));
        }
        return [];
    }
}
