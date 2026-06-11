<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Repositories\WorkspaceShareRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WorkspaceShareService
{
    public function __construct(
        protected WorkspaceShareRepository $workspaceShareRepository
    ) {}

    public function getSharedUsers(Workspace $workspace): Collection
    {
        $this->ensureOwner($workspace);

        return $this->workspaceShareRepository->getSharedUsers($workspace);
    }

    public function searchUsers(Workspace $workspace, string $search): Collection
    {
        $this->ensureOwner($workspace);

        if (strlen(trim($search)) < 2) {
            return collect();
        }

        return $this->workspaceShareRepository->searchUsers(
            trim($search),
            $workspace->user_id
        );
    }

    public function shareWithUser(Workspace $workspace, int $sharedWithUserId): void
    {
        $this->ensureOwner($workspace);

        if ($workspace->user_id === $sharedWithUserId) {
            return;
        }

        $this->workspaceShareRepository->shareWithUser(
            $workspace,
            $sharedWithUserId
        );
    }

    public function removeShare(Workspace $workspace, int $sharedWithUserId): void
    {
        $this->ensureOwner($workspace);

        $this->workspaceShareRepository->removeShare(
            $workspace,
            $sharedWithUserId
        );
    }

    private function ensureOwner(Workspace $workspace): void
    {
        abort_if($workspace->user_id !== Auth::id(), 403);
    }
}
