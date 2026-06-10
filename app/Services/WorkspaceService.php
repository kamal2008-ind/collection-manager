<?php

namespace App\Services;

use App\Repositories\WorkspaceRepository;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function __construct(
        protected WorkspaceRepository $workspaceRepository
    ) {}

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->workspaceRepository->create($data);
    }

    public function findById(int $id)
    {
        return $this->workspaceRepository
            ->findById($id);
    }

    public function update(int $id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->workspaceRepository
            ->update($id, $data);
    }
    public function delete(int $id): bool
    {
        return $this->workspaceRepository
            ->delete($id);
    }
    public function toggleFavorite(int $id): bool
    {
        return $this->workspaceRepository
            ->toggleFavorite($id);
    }
    public function bulkFavorite(array $ids): void
    {
        $this->workspaceRepository->bulkFavorite($ids);
    }

    public function bulkDelete(array $ids): void
    {
        $this->workspaceRepository->bulkDelete($ids);
    }
    public function getPaginatedWorkspaces(
        int $userId,
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        return $this->workspaceRepository->paginateByUser(
            $userId,
            $search,
            $perPage,
            $filter
        );
    }
}
