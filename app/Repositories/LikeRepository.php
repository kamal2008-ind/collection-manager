<?php

namespace App\Repositories;

use App\Models\Like;
use Illuminate\Database\Eloquent\Model;

class LikeRepository
{
    public function hasLiked(
        int $userId,
        Model $likeable
    ): bool {
        return Like::query()
            ->where('user_id', $userId)
            ->where('likeable_type', $likeable->getMorphClass())
            ->where('likeable_id', $likeable->getKey())
            ->exists();
    }

    public function count(Model $likeable): int
    {
        return Like::query()
            ->where('likeable_type', $likeable->getMorphClass())
            ->where('likeable_id', $likeable->getKey())
            ->count();
    }

    public function add(
        int $userId,
        Model $likeable
    ): Like {
        return Like::firstOrCreate([
            'user_id' => $userId,
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);
    }

    public function remove(
        int $userId,
        Model $likeable
    ): void {
        Like::query()
            ->where('user_id', $userId)
            ->where('likeable_type', $likeable->getMorphClass())
            ->where('likeable_id', $likeable->getKey())
            ->delete();
    }

    public function toggle(
        int $userId,
        Model $likeable
    ): bool {
        if ($this->hasLiked($userId, $likeable)) {
            $this->remove($userId, $likeable);

            return false;
        }

        $this->add($userId, $likeable);

        return true;
    }
}
