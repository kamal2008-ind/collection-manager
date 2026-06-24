<?php

namespace App\Repositories;

use App\Models\Collection;

class CollectionRepository
{
    public function create(array $data): Collection
    {
        return Collection::create($data);
    }

    public function findById(int $id)
    {
        return Collection::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $collection = Collection::findOrFail($id);

        $collection->update($data);

        return $collection;
    }

    public function delete(int $id): bool
    {
        return Collection::findOrFail($id)->delete();
    }

    public function toggleFavorite(int $id): bool
    {
        $collection = Collection::findOrFail($id);

        $collection->update([
            'is_favorite' => ! $collection->is_favorite,
        ]);

        return $collection->fresh()->is_favorite;
    }

    public function bulkFavorite(array $ids): void
    {
        Collection::whereIn('id', $ids)
            ->update(['is_favorite' => true]);
    }

    public function bulkDelete(array $ids): void
    {
        Collection::whereIn('id', $ids)
            ->delete();
    }

    public function paginateByUser(
        int $userId,
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        $query = Collection::query()
            ->with('user')
            ->withCount([
                'shares',
                'attachedWorkspaces as workspaces_count',
                'attachedMovies as movies_count',
            ])
            ->when(
                $search,
                fn($query) =>
                $query->where('name', 'LIKE', "%{$search}%")
            );

        switch ($accessMode) {
            case 'owned':
                $query->where('user_id', $userId);
                break;

            case 'public':
                $query->where('visibility', 'public')
                    ->where('user_id', '!=', $userId);
                break;

            case 'shared':
                $query->whereHas('shares', function ($shareQuery) use ($userId) {
                    $shareQuery->where('shared_with_user_id', $userId);
                });
                break;
        }
        switch ($filter) {
            case 'favorites':
                $query->where('user_id', $userId)
                    ->where('is_favorite', true);
                break;

            case 'attached':
                $query->where('user_id', $userId)
                    ->where(function ($query) {
                        $query->whereHas('attachedWorkspaces')
                            ->orWhereHas('attachedMovies');
                    });
                break;

            case 'unattached':
                $query->where('user_id', $userId)
                    ->whereDoesntHave('attachedWorkspaces')
                    ->whereDoesntHave('attachedMovies');
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
