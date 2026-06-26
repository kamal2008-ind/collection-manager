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
    public function duplicate(int $workspaceId): Workspace
    {
        $workspace = Workspace::findOrFail($workspaceId);

        $newName = $this->makeUniqueCopyName(
            $workspace->name,
            $workspace->user_id
        );

        return Workspace::create([
            'user_id' => $workspace->user_id,
            'name' => $newName,
            'slug' => \Illuminate\Support\Str::slug($newName),
            'description' => $workspace->description,
            'image' => $workspace->image,
            'visibility' => 'private',
            'is_favorite' => false,
        ]);
    }

    private function makeUniqueCopyName(string $name, int $userId): string
    {
        $baseName = trim($name) . ' - Copy';
        $newName = $baseName;
        $counter = 2;

        while (
            Workspace::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($newName))])
            ->exists()
        ) {
            $newName = $baseName . ' ' . $counter;
            $counter++;
        }

        return $newName;
    }
    public function paginateByUser(
        int $userId,
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        $query = Workspace::query()
            ->with('user')
            ->withCount([
                'shares',
                'collections as collections_count',
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
                        $query->whereHas('collections')
                            ->orWhereHas('attachedMovies');
                    });
                break;

            case 'unattached':
                $query->where('user_id', $userId)
                    ->whereDoesntHave('collections')
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
