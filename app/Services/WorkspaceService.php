<?php

namespace App\Services;

use App\Repositories\WorkspaceRepository;
use Illuminate\Support\Str;
use App\Models\Workspace;

class WorkspaceService
{
    public function __construct(
        protected WorkspaceRepository $workspaceRepository
    ) {}

    public function create(array $data): Workspace
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->workspaceRepository->create($data);
    }

    public function findById(int $id): Workspace
    {
        return $this->workspaceRepository
            ->findById($id);
    }

    public function update(int $id, array $data): Workspace
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
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        return $this->workspaceRepository->paginateByUser(
            $userId,
            $accessMode,
            $search,
            $perPage,
            $filter
        );
    }
}
