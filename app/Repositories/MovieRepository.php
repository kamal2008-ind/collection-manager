<?php

namespace App\Repositories;

use App\Models\Movie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MovieRepository
{
    public function create(array $data): Movie
    {
        $title = trim($data['title']);

        return Movie::create([
            'user_id' => $data['user_id'] ?? Auth::id(),
            'title' => $title,
            'slug' => $this->makeUniqueSlug(
                $title,
                $data['user_id'] ?? Auth::id()
            ),
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'poster_path' => $data['poster_path'] ?? null,
            'tmdb_id' => $data['tmdb_id'] ?? null,
            'imdb_id' => $data['imdb_id'] ?? null,
            'visibility' => $data['visibility'] ?? 'private',
            'is_favorite' => $data['is_favorite'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function findById(int $id): Movie
    {
        return Movie::with('user')->findOrFail($id);
    }

    public function update(int $id, array $data): Movie
    {
        $movie = Movie::findOrFail($id);

        $title = trim($data['title']);

        $movie->update([
            'title' => $title,
            'slug' => $movie->title !== $title
                ? $this->makeUniqueSlug($title, $movie->user_id, $movie->id)
                : $movie->slug,
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'poster_path' => array_key_exists('poster_path', $data)
                ? $data['poster_path']
                : $movie->poster_path,
            'tmdb_id' => $data['tmdb_id'] ?? null,
            'imdb_id' => $data['imdb_id'] ?? null,
            'visibility' => $data['visibility'] ?? $movie->visibility,
        ]);

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
                'shares',
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

    private function makeUniqueSlug(
        string $title,
        int $userId,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Movie::query()
            ->where('user_id', $userId)
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
