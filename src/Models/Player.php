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

    public int $sushiInsertChunkSize = 100;

    public function getRows(): array
    {
        $data = [];

        if (($handle = fopen(__DIR__.'/roles.csv', "r")) !== FALSE) {
            $headers = fgetcsv($handle, 0, ",");
            while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        return $data;
    }

        protected function sushiShouldCache()
    {
        return true;
    }

    protected function sushiCacheReferencePath()
    {
        return __DIR__.'/roles.csv';
    }

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
