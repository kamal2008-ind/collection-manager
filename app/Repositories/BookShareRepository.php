<?php

namespace App\Repositories;

use App\Models\Book;
use App\Models\BookShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class BookShareRepository
{
    public function getSharedUsers(Book $book): EloquentCollection
    {
        return $book->sharedUsers()->orderBy('name')->get();
    }

    public function searchUsers(string $search, int $ownerId, Book $book): EloquentCollection
    {
        return User::query()
            ->where('id', '!=', $ownerId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $book->shares()->pluck('shared_with_user_id'))
            ->limit(8)
            ->get();
    }

    public function shareWithUser(Book $book, int $sharedWithUserId): BookShare
    {
        return BookShare::updateOrCreate(
            [
                'book_id' => $book->id,
                'shared_with_user_id' => $sharedWithUserId,
            ],
            [
                'shared_by_user_id' => $book->user_id,
                'permission' => 'view',
            ]
        );
    }

    public function removeShare(Book $book, int $sharedWithUserId): void
    {
        BookShare::query()
            ->where('book_id', $book->id)
            ->where('shared_with_user_id', $sharedWithUserId)
            ->delete();
    }
}
