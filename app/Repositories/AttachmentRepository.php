<?php

namespace App\Repositories;

use App\Models\Attachment;
use Illuminate\Support\Collection;

class AttachmentRepository
{
    public function getAttachedWorkspaceIdsForCollection(int $collectionId): array
    {
        return Attachment::query()
            ->where('container_type', 'workspace')
            ->where('attachable_type', 'collection')
            ->where('attachable_id', $collectionId)
            ->pluck('container_id')
            ->toArray();
    }

    public function attachCollectionToWorkspace(
        int $workspaceId,
        int $collectionId
    ): Attachment {
        return Attachment::firstOrCreate([
            'container_type' => 'workspace',
            'container_id' => $workspaceId,
            'attachable_type' => 'collection',
            'attachable_id' => $collectionId,
        ]);
    }

    public function detachCollectionFromWorkspace(
        int $workspaceId,
        int $collectionId
    ): void {
        Attachment::query()
            ->where('container_type', 'workspace')
            ->where('container_id', $workspaceId)
            ->where('attachable_type', 'collection')
            ->where('attachable_id', $collectionId)
            ->delete();
    }

    public function attachCollectionsToWorkspaces(
        array $collectionIds,
        array $workspaceIds
    ): void {
        foreach ($workspaceIds as $workspaceId) {
            foreach ($collectionIds as $collectionId) {
                $this->attachCollectionToWorkspace(
                    (int) $workspaceId,
                    (int) $collectionId
                );
            }
        }
    }
}
