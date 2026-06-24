<?php

namespace App\Services;

use App\Repositories\CollectionRepository;
use Illuminate\Support\Str;

class CollectionService
{
    public function __construct(
        protected CollectionRepository $collectionRepository
    ) {}

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->collectionRepository->create($data);
    }

    public function findById(int $id)
    {
        return $this->collectionRepository->findById($id);
    }

    public function update(int $id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->collectionRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->collectionRepository->delete($id);
    }

    public function toggleFavorite(int $id): bool
    {
        return $this->collectionRepository->toggleFavorite($id);
    }

    public function bulkFavorite(array $ids): void
    {
        $this->collectionRepository->bulkFavorite($ids);
    }

    public function bulkDelete(array $ids): void
    {
        $this->collectionRepository->bulkDelete($ids);
    }

    public function getPaginatedCollections(
        int $userId,
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        return $this->collectionRepository->paginateByUser(
            $userId,
            $accessMode,
            $search,
            $perPage,
            $filter
        );
    }
}
