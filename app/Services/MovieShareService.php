<?php

namespace App\Services;

use App\Models\Movie;
use App\Repositories\MovieShareRepository;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

class MovieShareService
{
    public function __construct(
        protected MovieShareRepository $movieShareRepository
    ) {}

    public function getSharedUsers(Movie $movie): SupportCollection
    {
        $this->ensureOwner($movie);

        return $this->movieShareRepository->getSharedUsers($movie);
    }

    public function searchUsers(Movie $movie, string $search): SupportCollection
    {
        $this->ensureOwner($movie);

        if (strlen(trim($search)) < 2) {
            return collect();
        }

        return $this->movieShareRepository->searchUsers(trim($search), $movie->user_id, $movie);
    }

    public function shareWithUser(Movie $movie, int $sharedWithUserId): void
    {
        $this->ensureOwner($movie);

        if ($movie->user_id === $sharedWithUserId) {
            return;
        }

        $this->movieShareRepository->shareWithUser($movie, $sharedWithUserId);
    }

    public function removeShare(Movie $movie, int $sharedWithUserId): void
    {
        $this->ensureOwner($movie);

        $this->movieShareRepository->removeShare($movie, $sharedWithUserId);
    }

    private function ensureOwner(Movie $movie): void
    {
        abort_if($movie->user_id !== Auth::id(), 403);
    }
}
