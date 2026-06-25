<?php

namespace App\Services;

use App\Models\Movie;
use App\Repositories\MovieRepository;

class MovieService
{
    public function __construct(
        protected MovieRepository $movieRepository
    ) {}

    public function create(array $data): Movie
    {
        return $this->movieRepository->create($data);
    }
    public function findById(int $id): Movie
    {
        return $this->movieRepository->findById($id);
    }
    public function update(Movie $movie, array $data): Movie
    {
        return $this->movieRepository->update(
            $movie->id,
            $data
        );
    }

    public function delete(Movie $movie): void
    {
        $this->movieRepository->delete($movie->id);
    }

    public function toggleFavorite(Movie $movie): void
    {
        $this->movieRepository->toggleFavorite($movie->id);
    }

    public function bulkFavorite(array $ids): void
    {
        $this->movieRepository->bulkFavorite($ids);
    }

    public function bulkDelete(array $ids): void
    {
        $this->movieRepository->bulkDelete($ids);
    }

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
}
