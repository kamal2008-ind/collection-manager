<?php

namespace App\Services;

use App\Models\Book;
use App\Repositories\BookShareRepository;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

class BookShareService
{
    public function __construct(
        protected BookShareRepository $bookShareRepository
    ) {}

    public function getSharedUsers(Book $book): SupportCollection
    {
        $this->ensureOwner($book);

        return $this->bookShareRepository->getSharedUsers($book);
    }

    public function searchUsers(Book $book, string $search): SupportCollection
    {
        $this->ensureOwner($book);

        if (strlen(trim($search)) < 2) {
            return collect();
        }

        return $this->bookShareRepository->searchUsers(trim($search), $book->user_id, $book);
    }

    public function shareWithUser(Book $book, int $sharedWithUserId): void
    {
        $this->ensureOwner($book);

        if ($book->user_id === $sharedWithUserId) {
            return;
        }

        $this->bookShareRepository->shareWithUser($book, $sharedWithUserId);
    }

    public function removeShare(Book $book, int $sharedWithUserId): void
    {
        $this->ensureOwner($book);

        $this->bookShareRepository->removeShare($book, $sharedWithUserId);
    }

    private function ensureOwner(Book $book): void
    {
        abort_if($book->user_id !== Auth::id(), 403);
    }
}
