<?php

namespace App\Repositories;

use App\Models\Movie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MovieRepository
{
    public function create(array $data): Movie
    {
        return Movie::create($data);
    }

    public function findById(int $id): Movie
    {
        return Movie::with('user')->findOrFail($id);
    }

    public function update(int $id, array $data): Movie
    {
        $movie = Movie::findOrFail($id);

        $movie->update($data);

        return $movie;
    }

    public function delete(int $id): bool
    {
        return (bool) Movie::findOrFail($id)->delete();
    }

    public function toggleFavorite(int $id): bool
    {
        $movie = Movie::findOrFail($id);

        $movie->update([
            'is_favorite' => ! $movie->is_favorite,
        ]);

        return (bool) $movie->fresh()->is_favorite;
    }

    public function bulkFavorite(array $ids): void
    {
        Movie::query()
            ->whereIn('id', $ids)
            ->update(['is_favorite' => true]);
    }

    public function bulkDelete(array $ids): void
    {
        Movie::query()
            ->whereIn('id', $ids)
            ->delete();
    }

    public function paginateByUser(
        int $userId,
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ): LengthAwarePaginator {
        $query = Movie::query()
            ->with(['user'])
            ->withCount([
                'attachedWorkspaces as workspaces_count',
                'attachedCollections as collections_count',
            ])
            ->when($search, function ($query) use ($search) {
                $search = trim($search);

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('year', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('imdb_id', 'LIKE', "%{$search}%")
                        ->orWhere('tmdb_id', 'LIKE', "%{$search}%");
                });
            });

        switch ($accessMode) {
            case 'owned':
                $query->where('user_id', $userId);
                break;

            case 'public':
                $query->where('visibility', 'public')
                    ->where('user_id', '!=', $userId);
                break;

            case 'shared':
                $query->whereRaw('1 = 0');
                break;
        }
        switch ($filter) {
            case 'favorites':
                $query->where('user_id', $userId)->where('is_favorite', true);
                break;

            case 'attached':
                $query->where('user_id', $userId)
                    ->where(function ($query) {
                        $query->whereHas('attachedWorkspaces')
                            ->orWhereHas('attachedCollections');
                    });
                break;

            case 'unattached':
                $query->where('user_id', $userId)
                    ->whereDoesntHave('attachedWorkspaces')
                    ->whereDoesntHave('attachedCollections');
                break;

            case 'recent':
            default:
                break;
        }

        return $query
            ->latest()
            ->paginate($perPage);
    }
}
