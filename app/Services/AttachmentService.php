<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Workspace;
use App\Repositories\AttachmentRepository;
use Illuminate\Support\Facades\Auth;

class AttachmentService
{
    public function __construct(
        protected AttachmentRepository $attachmentRepository
    ) {}

    public function getAttachedWorkspaceIdsForCollection(int $collectionId): array
    {
        $this->ensureOwnCollection($collectionId);

        return $this->attachmentRepository
            ->getAttachedWorkspaceIdsForCollection($collectionId);
    }

    public function attachCollectionToWorkspaces(
        int $collectionId,
        array $workspaceIds
    ): void {
        $this->ensureOwnCollection($collectionId);
        $this->ensureOwnWorkspaces($workspaceIds);

        foreach ($workspaceIds as $workspaceId) {
            $this->attachmentRepository->attachCollectionToWorkspace(
                (int) $workspaceId,
                $collectionId
            );
        }
    }

    public function bulkAttachCollectionsToWorkspaces(
        array $collectionIds,
        array $workspaceIds
    ): void {
        $this->ensureOwnCollections($collectionIds);
        $this->ensureOwnWorkspaces($workspaceIds);

        $this->attachmentRepository->attachCollectionsToWorkspaces(
            $collectionIds,
            $workspaceIds
        );
    }

    public function detachCollectionFromWorkspace(
        int $collectionId,
        int $workspaceId
    ): void {
        $this->ensureOwnCollection($collectionId);
        $this->ensureOwnWorkspace($workspaceId);

        $this->attachmentRepository->detachCollectionFromWorkspace(
            $workspaceId,
            $collectionId
        );
    }

    private function ensureOwnCollection(int $collectionId): void
    {
        abort_if(
            ! Collection::query()
                ->where('id', $collectionId)
                ->where('user_id', Auth::id())
                ->exists(),
            403
        );
    }

    private function ensureOwnCollections(array $collectionIds): void
    {
        $count = Collection::query()
            ->whereIn('id', $collectionIds)
            ->where('user_id', Auth::id())
            ->count();

        abort_if($count !== count($collectionIds), 403);
    }

    private function ensureOwnWorkspace(int $workspaceId): void
    {
        abort_if(
            ! Workspace::query()
                ->where('id', $workspaceId)
                ->where('user_id', Auth::id())
                ->exists(),
            403
        );
    }

    private function ensureOwnWorkspaces(array $workspaceIds): void
    {
        if (empty($workspaceIds)) {
            return;
        }

        $count = Workspace::query()
            ->whereIn('id', $workspaceIds)
            ->where('user_id', Auth::id())
            ->count();

        abort_if($count !== count($workspaceIds), 403);
    }
}
