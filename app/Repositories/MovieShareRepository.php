<?php

namespace App\Repositories;

use App\Models\Movie;
use App\Models\MovieShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MovieShareRepository
{
    public function getSharedUsers(Movie $movie): EloquentCollection
    {
        return $movie->sharedUsers()->orderBy('name')->get();
    }

    public function searchUsers(string $search, int $ownerId, Movie $movie): EloquentCollection
    {
        return User::query()
            ->where('id', '!=', $ownerId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $movie->shares()->pluck('shared_with_user_id'))
            ->limit(8)
            ->get();
    }

    public function shareWithUser(Movie $movie, int $sharedWithUserId): MovieShare
    {
        return MovieShare::updateOrCreate(
            [
                'movie_id' => $movie->id,
                'shared_with_user_id' => $sharedWithUserId,
            ],
            [
                'shared_by_user_id' => $movie->user_id,
                'permission' => 'view',
            ]
        );
    }

    public function removeShare(Movie $movie, int $sharedWithUserId): void
    {
        MovieShare::query()
            ->where('movie_id', $movie->id)
            ->where('shared_with_user_id', $sharedWithUserId)
            ->delete();
    }
}
