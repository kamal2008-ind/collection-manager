<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookRepository
{
    public function create(array $data): Book
    {
        $title = trim($data['title']);

        return Book::create([
            'user_id' => $data['user_id'] ?? Auth::id(),
            'title' => $title,
            'slug' => $this->makeUniqueSlug(
                $title,
                $data['user_id'] ?? Auth::id()
            ),
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'author' => $data['author'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'visibility' => $data['visibility'] ?? 'private',
            'is_favorite' => $data['is_favorite'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function findById(int $id): Book
    {
        return Book::with('user')->findOrFail($id);
    }

    public function update(int $id, array $data): Book
    {
        $book = Book::findOrFail($id);

        $title = trim($data['title']);

        $book->update([
            'title' => $title,
            'slug' => $book->title !== $title
                ? $this->makeUniqueSlug($title, $book->user_id, $book->id)
                : $book->slug,
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'cover_image' => array_key_exists('cover_image', $data)
                ? $data['cover_image']
                : $book->cover_image,
            'author' => $data['author'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'visibility' => $data['visibility'] ?? $book->visibility,
        ]);

        return $book;
    }

    public function delete(int $id): bool
    {
        return (bool) Book::findOrFail($id)->delete();
    }

    public function toggleFavorite(int $id): bool
    {
        $book = Book::findOrFail($id);

        $book->update([
            'is_favorite' => ! $book->is_favorite,
        ]);

        return (bool) $book->fresh()->is_favorite;
    }

    public function bulkFavorite(array $ids): void
    {
        Book::query()
            ->whereIn('id', $ids)
            ->update(['is_favorite' => true]);
    }

    public function bulkDelete(array $ids): void
    {
        Book::query()
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
        $query = Book::query()
            ->with(['user'])
            ->withCount([
                'shares',
                'attachedWorkspaces as workspaces_count',
                'attachedCollections as collections_count',
                'likes'
            ])
            ->when($search, function ($query) use ($search) {
                $search = trim($search);

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('year', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('publisher', 'LIKE', "%{$search}%")
                        ->orWhere('author', 'LIKE', "%{$search}%");
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
            Book::query()
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
