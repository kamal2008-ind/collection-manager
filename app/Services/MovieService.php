<?php

namespace App\Services;

use App\Models\Movie;
use App\Repositories\MovieRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MovieService
{
    public function __construct(
        protected MovieRepository $movieRepository
    ) {}

    public function getPaginatedMovies(
        int $userId,
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        return $this->movieRepository->paginateByUser(
            $userId,
            $accessMode,
            $search,
            $perPage,
            $filter
        );
    }

    public function create(array $data): Movie
    {
        $title = trim($data['title']);

        return Movie::create([
            'user_id' => Auth::id(),
            'title' => $title,
            'slug' => $this->makeUniqueSlug($title),
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'poster_path' => $data['poster_path'] ?? null,
            'tmdb_id' => $data['tmdb_id'] ?? null,
            'imdb_id' => $data['imdb_id'] ?? null,
            'visibility' => $data['visibility'] ?? 'private',
            'is_favorite' => false,
            'sort_order' => 0,
        ]);
    }

    public function update(Movie $movie, array $data): Movie
    {
        $title = trim($data['title']);

        $movie->update([
            'title' => $title,
            'slug' => $movie->title !== $title
                ? $this->makeUniqueSlug($title, $movie->id)
                : $movie->slug,
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'poster_path' => $data['poster_path'] ?? $movie->poster_path,
            'tmdb_id' => $data['tmdb_id'] ?? null,
            'imdb_id' => $data['imdb_id'] ?? null,
            'visibility' => $data['visibility'] ?? $movie->visibility,
        ]);

        return $movie;
    }

    public function delete(Movie $movie): void
    {
        $movie->delete();
    }

    public function toggleFavorite(Movie $movie): void
    {
        $movie->update([
            'is_favorite' => ! $movie->is_favorite,
        ]);
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Movie::query()
            ->where('user_id', Auth::id())
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
    public function findById(int $id)
    {
        return $this->movieRepository
            ->findById($id);
    }
}
