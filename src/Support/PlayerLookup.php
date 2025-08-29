<?php

namespace Sleeper\Laravel\Support;

use Sleeper\Laravel\Models\Player;

class PlayerLookup
{
    /**
     * Return a map of player_id => player attributes for the given IDs.
     * Missing IDs will be absent from the returned map.
     *
     * @param array<int,string> $ids
     * @return array<string,array>
     */
    public static function mapByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, fn ($v) => (string) $v !== '')));
        if (empty($ids)) {
            return [];
        }

        $players = Player::query()
            ->whereIn('player_id', $ids)
            ->get()
            ->keyBy('player_id');

        $out = [];
        foreach ($players as $p) {
            $out[$p->player_id] = $p->toArray();
        }
        return $out;
    }

    /**
     * Find a single player by ID, returning attributes or null.
     */
    public static function find(string $id): ?array
    {
        $p = Player::query()->find($id);
        return $p ? $p->toArray() : null;
    }
}

