<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceShare;
use Illuminate\Database\Eloquent\Collection;

class WorkspaceShareRepository
{
    public function getSharedUsers(Workspace $workspace): Collection
    {
        return $workspace->sharedUsers()
            ->orderBy('name')
            ->get();
    }

    public function searchUsers(string $search, int $ownerId, Workspace $workspace): Collection
    {
        return User::query()
            ->where('id', '!=', $ownerId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $workspace->shares()->pluck('shared_with_user_id'))
            ->limit(8)
            ->get();
    }

    public function shareWithUser(Workspace $workspace, int $sharedWithUserId): WorkspaceShare
    {
        return WorkspaceShare::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'shared_with_user_id' => $sharedWithUserId,
            ],
            [
                'shared_by_user_id' => $workspace->user_id,
                'permission' => 'view',
            ]
        );
    }

    public function removeShare(Workspace $workspace, int $sharedWithUserId): void
    {
        WorkspaceShare::query()
            ->where('workspace_id', $workspace->id)
            ->where('shared_with_user_id', $sharedWithUserId)
            ->delete();
    }
}
