<?php

namespace App\Livewire\Collection;

use App\Models\Collection;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Collection $collection;
    public bool $isPrivateBlocked = false;

    public function mount(string $username, string $slug): void
    {
        $user = User::where('username', $username)->firstOrFail();

        $collection = Collection::query()
            ->withCount(['attachedWorkspaces as workspaces_count'])
            ->where('user_id', $user->id)
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $collection->canBeViewedBy(auth()->user())) {
            $this->collection = $collection;
            $this->isPrivateBlocked = true;

            return;
        }

        $this->collection = $collection;
    }

    public function render()
    {
        return view('livewire.collection.show')
            ->layout('layouts.app');
    }
}
