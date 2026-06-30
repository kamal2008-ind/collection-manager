<?php

namespace App\Livewire\Book;

use App\Models\Book;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Book $book;
    public bool $isPrivateBlocked = false;

    public function mount(string $username, string $slug): void
    {
        $user = User::where('username', $username)->firstOrFail();

        $book = Book::query()
            ->withCount(['attachedWorkspaces as workspaces_count', 'attachedCollections as collections_count'])
            ->with('user')
            ->where('user_id', $user->id)
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $book->canBeViewedBy(auth()->user())) {
            $this->book = $book;
            $this->isPrivateBlocked = true;

            return;
        }

        $this->book = $book;
    }

    public function render()
    {
        return view('livewire.book.show')
            ->layout('layouts.app');
    }
}
