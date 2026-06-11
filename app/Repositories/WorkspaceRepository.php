<?php

namespace App\Repositories;

use App\Models\Workspace;

class WorkspaceRepository
{
    public function create(array $data)
    {
        return Workspace::create($data);
    }

    public function findById(int $id)
    {
        return Workspace::findOrFail($id);
    }

    public function update(
        int $id,
        array $data
    ) {
        $workspace = Workspace::findOrFail($id);

        $workspace->update($data);

        return $workspace;
    }
    public function delete(int $id): bool
    {
        return Workspace::findOrFail($id)
            ->delete();
    }
    public function toggleFavorite(int $id): bool
    {
        $workspace = Workspace::findOrFail($id);

        $workspace->update([
            'is_favorite' => ! $workspace->is_favorite,
        ]);

        return $workspace->fresh()->is_favorite;
    }
    public function bulkFavorite(array $ids): void
    {
        Workspace::whereIn('id', $ids)
            ->update(['is_favorite' => true]);
    }

    public function bulkDelete(array $ids): void
    {
        Workspace::whereIn('id', $ids)
            ->delete();
    }
    public function paginateByUser(
        int $userId,
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        $query = Workspace::query()
            ->withCount(['collections', 'shares'])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('shares', function ($shareQuery) use ($userId) {
                        $shareQuery->where('shared_with_user_id', $userId);
                    });
            })
            ->when(
                $search,
                fn($query) =>
                $query->where(
                    'name',
                    'LIKE',
                    "%{$search}%"
                )
            );

        if ($filter === 'favorites') {
            $query->where('is_favorite', true);
        }

        return $query
            ->latest()
            ->paginate($perPage);
    }
}
