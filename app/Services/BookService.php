<?php

namespace App\Services;

use App\Models\Book;
use App\Repositories\BookRepository;

class BookService
{
    public function __construct(
        protected BookRepository $bookRepository
    ) {}

    public function create(array $data): Book
    {
        return $this->bookRepository->create($data);
    }
    public function findById(int $id): Book
    {
        return $this->bookRepository->findById($id);
    }
    public function update(Book $book, array $data): Book
    {
        return $this->bookRepository->update(
            $book->id,
            $data
        );
    }

    public function delete(Book $book): void
    {
        $this->bookRepository->delete($book->id);
    }

    public function toggleFavorite(Book $book): void
    {
        $this->bookRepository->toggleFavorite($book->id);
    }

    public function bulkFavorite(array $ids): void
    {
        $this->bookRepository->bulkFavorite($ids);
    }

    public function bulkDelete(array $ids): void
    {
        $this->bookRepository->bulkDelete($ids);
    }

    public function getPaginatedBooks(
        int $userId,
        string $accessMode = 'owned',
        string $search = '',
        int $perPage = 12,
        string $filter = 'recent'
    ) {
        return $this->bookRepository->paginateByUser(
            $userId,
            $accessMode,
            $search,
            $perPage,
            $filter
        );
    }
}
