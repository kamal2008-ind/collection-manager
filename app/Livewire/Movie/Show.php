<?php

namespace App\Livewire\Movie;

use App\Models\Movie;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Movie $movie;
    public bool $isPrivateBlocked = false;

    public function mount(string $username, string $slug): void
    {
        $user = User::where('username', $username)->firstOrFail();

        $movie = Movie::query()
            ->withCount(['attachedWorkspaces as workspaces_count', 'attachedCollections as collections_count'])
            ->with('user')
            ->where('user_id', $user->id)
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $movie->canBeViewedBy(auth()->user())) {
            $this->movie = $movie;
            $this->isPrivateBlocked = true;

            return;
        }

        $this->movie = $movie;
    }

    public function render()
    {
        return view('livewire.movie.show')
            ->layout('layouts.app');
    }
}
