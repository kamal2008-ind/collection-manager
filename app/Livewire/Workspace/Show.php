<?php

namespace App\Livewire\Workspace;

use App\Models\User;
use Livewire\Component;
use App\Models\Workspace;

class Show extends Component
{
    public Workspace $workspace;
    public bool $isPrivateBlocked = false;
    public function mount(string $username, string $slug): void
    {
        $user = User::where('username', $username)->firstOrFail();

        $workspace = Workspace::query()
            ->where('user_id', $user->id)
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $workspace->canBeViewedBy(auth()->user()))
        {
            $this->workspace = $workspace;
            $this->isPrivateBlocked = true;

            return;
        }

        $this->workspace = $workspace;
    }

    public function render()
    {
        return view('livewire.workspace.show')
            ->layout('layouts.app');
    }
}
