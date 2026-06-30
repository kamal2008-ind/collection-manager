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
    public function duplicate(int $collectionId): Collection
    {
        $collection = Collection::findOrFail($collectionId);

        $newName = $this->makeUniqueCopyName(
            $collection->name,
            $collection->user_id
        );

        return Collection::create([
            'user_id' => $collection->user_id,
            'name' => $newName,
            'slug' => \Illuminate\Support\Str::slug($newName),
            'description' => $collection->description,
            'image' => $collection->image,
            'visibility' => 'private',
            'is_favorite' => false,
        ]);
    }

    private function makeUniqueCopyName(string $name, int $userId): string
    {
        $baseName = preg_replace('/ - Copy( \d+)?$/', '', $name);

        $copyName = $baseName . ' - Copy';
        $counter = 2;

        while (
            \App\Models\Collection::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($copyName))])
            ->exists()
        ) {
            $copyName = $baseName . ' - Copy ' . $counter;
            $counter++;
        }

        return $copyName;
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
                'likes',
                'attachedBooks as books_count',
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
                            ->orWhereHas('attachedMovies')
                            ->orWhereHas('attachedBooks');
                    });
                break;

            case 'unattached':
                $query->where('user_id', $userId)
                    ->whereDoesntHave('attachedWorkspaces')
                    ->whereDoesntHave('attachedMovies')
                    ->whereDoesntHave('attachedBooks');
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
