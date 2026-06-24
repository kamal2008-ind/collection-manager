<?php

namespace App\Repositories;

use App\Models\Attachment;

class AttachmentRepository
{
    /**
     * Generic: return container IDs for one attachable item.
     * Example: movie attached to workspaces, collection attached to workspaces, movie attached to collections.
     */
    public function getAttachedContainerIds(
        string $containerType,
        string $attachableType,
        int $attachableId
    ): array {
        return Attachment::query()
            ->where('container_type', $containerType)
            ->where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->pluck('container_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    /**
     * Generic: attach one item to one container.
     * firstOrCreate keeps duplicate attachments safe.
     */
    public function attach(
        string $containerType,
        int $containerId,
        string $attachableType,
        int $attachableId
    ): Attachment {
        return Attachment::firstOrCreate([
            'container_type' => $containerType,
            'container_id' => $containerId,
            'attachable_type' => $attachableType,
            'attachable_id' => $attachableId,
        ]);
    }

    /**
     * Generic: detach one item from one container.
     */
    public function detach(
        string $containerType,
        int $containerId,
        string $attachableType,
        int $attachableId
    ): void {
        Attachment::query()
            ->where('container_type', $containerType)
            ->where('container_id', $containerId)
            ->where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->delete();
    }

    /**
     * Generic: attach many attachables to many containers.
     */
    public function attachManyToMany(
        string $containerType,
        array $containerIds,
        string $attachableType,
        array $attachableIds
    ): void {
        foreach ($containerIds as $containerId) {
            foreach ($attachableIds as $attachableId) {
                $this->attach(
                    $containerType,
                    (int) $containerId,
                    $attachableType,
                    (int) $attachableId
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Backward-compatible Collection methods
    |--------------------------------------------------------------------------
    | Keep these so the completed Collection module does not break.
    */

    public function getAttachedWorkspaceIdsForCollection(int $collectionId): array
    {
        return $this->getAttachedContainerIds(
            'workspace',
            'collection',
            $collectionId
        );
    }

    public function attachCollectionToWorkspace(
        int $workspaceId,
        int $collectionId
    ): Attachment {
        return $this->attach(
            'workspace',
            $workspaceId,
            'collection',
            $collectionId
        );
    }

    public function detachCollectionFromWorkspace(
        int $workspaceId,
        int $collectionId
    ): void {
        $this->detach(
            'workspace',
            $workspaceId,
            'collection',
            $collectionId
        );
    }

    public function attachCollectionsToWorkspaces(
        array $collectionIds,
        array $workspaceIds
    ): void {
        $this->attachManyToMany(
            'workspace',
            $workspaceIds,
            'collection',
            $collectionIds
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Movie helper methods
    |--------------------------------------------------------------------------
    */

    public function getAttachedWorkspaceIdsForMovie(int $movieId): array
    {
        return $this->getAttachedContainerIds(
            'workspace',
            'movie',
            $movieId
        );
    }

    public function getAttachedCollectionIdsForMovie(int $movieId): array
    {
        return $this->getAttachedContainerIds(
            'collection',
            'movie',
            $movieId
        );
    }

    public function attachMovieToWorkspace(int $workspaceId, int $movieId): Attachment
    {
        return $this->attach(
            'workspace',
            $workspaceId,
            'movie',
            $movieId
        );
    }

    public function attachMovieToCollection(int $collectionId, int $movieId): Attachment
    {
        return $this->attach(
            'collection',
            $collectionId,
            'movie',
            $movieId
        );
    }

    public function detachMovieFromWorkspace(int $workspaceId, int $movieId): void
    {
        $this->detach(
            'workspace',
            $workspaceId,
            'movie',
            $movieId
        );
    }

    public function detachMovieFromCollection(int $collectionId, int $movieId): void
    {
        $this->detach(
            'collection',
            $collectionId,
            'movie',
            $movieId
        );
    }
}
