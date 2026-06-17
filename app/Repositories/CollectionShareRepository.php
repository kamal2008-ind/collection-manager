<?php

namespace App\Repositories;

use App\Models\Collection;
use App\Models\CollectionShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionShareRepository
{
    public function getSharedUsers(Collection $collection): EloquentCollection
    {
        return $collection->sharedUsers()
            ->orderBy('name')
            ->get();
    }

    public function searchUsers(string $search, int $ownerId): EloquentCollection
    {
        return User::query()
            ->where('id', '!=', $ownerId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->limit(8)
            ->get();
    }

    public function shareWithUser(Collection $collection, int $sharedWithUserId): CollectionShare
    {
        return CollectionShare::updateOrCreate(
            [
                'collection_id' => $collection->id,
                'shared_with_user_id' => $sharedWithUserId,
            ],
            [
                'shared_by_user_id' => $collection->user_id,
                'permission' => 'view',
            ]
        );
    }

    public function removeShare(Collection $collection, int $sharedWithUserId): void
    {
        CollectionShare::query()
            ->where('collection_id', $collection->id)
            ->where('shared_with_user_id', $sharedWithUserId)
            ->delete();
    }
}
