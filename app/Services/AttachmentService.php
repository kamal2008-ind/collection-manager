<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Movie;
use App\Models\Workspace;
use App\Repositories\AttachmentRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AttachmentService
{
    public function __construct(
        protected AttachmentRepository $attachmentRepository
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Collection → Workspace methods
    |--------------------------------------------------------------------------
    | Existing Collection module compatibility.
    */

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

    /*
    |--------------------------------------------------------------------------
    | Movie → Workspace/Collection methods
    |--------------------------------------------------------------------------
    */

    public function getAttachedWorkspaceIdsForMovie(int $movieId): array
    {
        $this->ensureOwnMovie($movieId);

        return $this->attachmentRepository
            ->getAttachedWorkspaceIdsForMovie($movieId);
    }

    public function getAttachedCollectionIdsForMovie(int $movieId): array
    {
        $this->ensureOwnMovie($movieId);

        return $this->attachmentRepository
            ->getAttachedCollectionIdsForMovie($movieId);
    }

    public function attachMovieToWorkspaces(
        int $movieId,
        array $workspaceIds
    ): void {
        $this->ensureOwnMovie($movieId);
        $this->ensureOwnWorkspaces($workspaceIds);

        foreach ($workspaceIds as $workspaceId) {
            $this->attachmentRepository->attachMovieToWorkspace(
                (int) $workspaceId,
                $movieId
            );
        }
    }

    public function attachMovieToCollections(
        int $movieId,
        array $collectionIds
    ): void {
        $this->ensureOwnMovie($movieId);
        $this->ensureOwnCollections($collectionIds);

        foreach ($collectionIds as $collectionId) {
            $this->attachmentRepository->attachMovieToCollection(
                (int) $collectionId,
                $movieId
            );
        }
    }

    public function bulkAttachMoviesToWorkspaces(
        array $movieIds,
        array $workspaceIds
    ): void {
        $this->ensureOwnMovies($movieIds);
        $this->ensureOwnWorkspaces($workspaceIds);

        $this->attachmentRepository->attachManyToMany(
            'workspace',
            $workspaceIds,
            'movie',
            $movieIds
        );
    }

    public function bulkAttachMoviesToCollections(
        array $movieIds,
        array $collectionIds
    ): void {
        $this->ensureOwnMovies($movieIds);
        $this->ensureOwnCollections($collectionIds);

        $this->attachmentRepository->attachManyToMany(
            'collection',
            $collectionIds,
            'movie',
            $movieIds
        );
    }

    public function detachMovieFromWorkspace(
        int $movieId,
        int $workspaceId
    ): void {
        $this->ensureOwnMovie($movieId);
        $this->ensureOwnWorkspace($workspaceId);

        $this->attachmentRepository->detachMovieFromWorkspace(
            $workspaceId,
            $movieId
        );
    }

    public function detachMovieFromCollection(
        int $movieId,
        int $collectionId
    ): void {
        $this->ensureOwnMovie($movieId);
        $this->ensureOwnCollection($collectionId);

        $this->attachmentRepository->detachMovieFromCollection(
            $collectionId,
            $movieId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ownership guards
    |--------------------------------------------------------------------------
    */

    private function ensureOwnWorkspace(int $workspaceId): void
    {
        $this->ensureOwnModel(Workspace::class, $workspaceId);
    }

    private function ensureOwnWorkspaces(array $workspaceIds): void
    {
        $this->ensureOwnModels(Workspace::class, $workspaceIds);
    }

    private function ensureOwnCollection(int $collectionId): void
    {
        $this->ensureOwnModel(Collection::class, $collectionId);
    }

    private function ensureOwnCollections(array $collectionIds): void
    {
        $this->ensureOwnModels(Collection::class, $collectionIds);
    }

    private function ensureOwnMovie(int $movieId): void
    {
        $this->ensureOwnModel(Movie::class, $movieId);
    }

    private function ensureOwnMovies(array $movieIds): void
    {
        $this->ensureOwnModels(Movie::class, $movieIds);
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function ensureOwnModel(string $modelClass, int $id): void
    {
        abort_if(
            ! $modelClass::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->exists(),
            403
        );
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function ensureOwnModels(string $modelClass, array $ids): void
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return;
        }

        $count = $modelClass::query()
            ->whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->count();

        abort_if($count !== count($ids), 403);
    }
    public function copyWorkspaceAttachments(
        int $fromWorkspaceId,
        int $toWorkspaceId
    ): void {
        $this->ensureOwnWorkspace($fromWorkspaceId);
        $this->ensureOwnWorkspace($toWorkspaceId);

        $this->attachmentRepository->copyWorkspaceAttachments(
            $fromWorkspaceId,
            $toWorkspaceId
        );
    }

    public function copyCollectionMovieAttachments(
        int $fromCollectionId,
        int $toCollectionId
    ): void {
        $this->ensureOwnCollection($fromCollectionId);
        $this->ensureOwnCollection($toCollectionId);

        $this->attachmentRepository->copyCollectionMovieAttachments(
            $fromCollectionId,
            $toCollectionId
        );
    }
}
