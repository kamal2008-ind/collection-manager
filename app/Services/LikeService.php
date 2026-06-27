<?php

namespace App\Services;

use App\Repositories\LikeRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LikeService
{
    public function __construct(
        protected LikeRepository $likeRepository
    ) {}

    public function toggle(Model $likeable): bool
    {
        $user = Auth::user();

        abort_if(! $user, 403);

        abort_if((int) $likeable->user_id === (int) $user->id, 403);

        abort_if(! $this->canLike($likeable, $user), 403);

        return $this->likeRepository->toggle(
            $user->id,
            $likeable
        );
    }

    public function hasLiked(Model $likeable): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $this->likeRepository->hasLiked(
            $user->id,
            $likeable
        );
    }

    public function count(Model $likeable): int
    {
        return $this->likeRepository->count($likeable);
    }

    private function canLike(Model $likeable, $user): bool
    {
        if (($likeable->visibility ?? null) === 'public') {
            return true;
        }

        if (method_exists($likeable, 'isSharedWith')) {
            return $likeable->isSharedWith($user);
        }

        return false;
    }
}
