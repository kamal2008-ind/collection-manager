<?php

namespace App\Livewire\Concerns;

trait HasUserViewPreferences
{
    public function loadUserViewPreferences(): void
    {
        if (! auth()->check()) {
            return;
        }

        $this->view = auth()->user()->view_mode ?? 'card';
        $this->paginationMode = auth()->user()->pagination_mode ?? 'pages';
    }

    public function setView(string $view): void
    {
        if (! in_array($view, ['table', 'card', 'masonry'], true)) {
            return;
        }

        $this->view = $view;

        auth()->user()?->update([
            'view_mode' => $view,
        ]);

        $this->selected = [];
        $this->resetPage();
    }

    public function setPaginationMode(string $mode): void
    {
        if (! in_array($mode, ['pages', 'lazy'], true)) {
            return;
        }

        $this->paginationMode = $mode;

        auth()->user()?->update([
            'pagination_mode' => $mode,
        ]);

        $this->selected = [];
        $this->perPage = 12;
        $this->resetPage();
    }
}
