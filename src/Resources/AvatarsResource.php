<?php

namespace MichaelCrowcroft\SleeperLaravel\Resources;

use Saloon\Http\BaseResource;

class AvatarsResource extends BaseResource
{
    public function fullUrl(?string $avatarId): ?string
    {
        if (empty($avatarId)) {
            return null;
        }
        return rtrim((string) config('sleeper.cdn_url'), '/').'/avatars/'.ltrim($avatarId, '/');
    }

    public function thumbUrl(?string $avatarId): ?string
    {
        if (empty($avatarId)) {
            return null;
        }
        return rtrim((string) config('sleeper.cdn_url'), '/').'/avatars/thumbs/'.ltrim($avatarId, '/');
    }
}
