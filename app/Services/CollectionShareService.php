<?php

namespace App\Services;

use App\Models\Collection;
use App\Repositories\CollectionShareRepository;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

class CollectionShareService
{
    public function __construct(
        protected CollectionShareRepository $collectionShareRepository
    ) {}

    public function getSharedUsers(Collection $collection): SupportCollection
    {
        $this->ensureOwner($collection);

        return $this->collectionShareRepository->getSharedUsers($collection);
    }

    public function searchUsers(Collection $collection, string $search): SupportCollection
    {
        $this->ensureOwner($collection);

        if (strlen(trim($search)) < 2) {
            return collect();
        }

        return $this->collectionShareRepository->searchUsers(
            trim($search),
            $collection->user_id
        );
    }

    public function shareWithUser(Collection $collection, int $sharedWithUserId): void
    {
        $this->ensureOwner($collection);

        if ($collection->user_id === $sharedWithUserId) {
            return;
        }

        $this->collectionShareRepository->shareWithUser(
            $collection,
            $sharedWithUserId
        );
    }

    public function removeShare(Collection $collection, int $sharedWithUserId): void
    {
        $this->ensureOwner($collection);

        $this->collectionShareRepository->removeShare(
            $collection,
            $sharedWithUserId
        );
    }

    private function ensureOwner(Collection $collection): void
    {
        abort_if($collection->user_id !== Auth::id(), 403);
    }
}
