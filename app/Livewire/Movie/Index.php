<?php

namespace App\Livewire\Movie;

use App\Models\Collection;
use App\Models\Movie;
use App\Models\Workspace;
use App\Services\AttachmentService;
use App\Services\MovieService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Services\MovieShareService;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $filter = 'recent';
    public string $accessMode = 'owned';

    public int $perPage = 12;
    public string $view = 'card';
    public string $paginationMode = 'pages';
    public bool $showDrawer = false;
    public string $drawerMode = 'create';
    public ?int $movieId = null;
    public string $title = '';
    public ?int $year = null;
    public string $description = '';
    public ?int $tmdb_id = null;
    public ?string $imdb_id = null;
    public $image;
    public ?string $currentPoster = null;
    public bool $removePoster = false;
    public string $visibility = 'private';
    public array $selected = [];
    public bool $showDeleteModal = false;
    public ?int $deleteMovieId = null;
    public ?string $deleteMovieTitle = null;
    public int $drawerPerPage = 12;
    public bool $attachToDrawerOpen = false;
    public bool $isBulkAttachMode = false;
    public ?int $attachToMovieId = null;
    public ?string $attachToMovieName = null;
    public string $attachTargetType = 'workspace';
    public string $attachSearch = '';
    public array $workspaceOptions = [];
    public array $collectionOptions = [];
    public array $attachedWorkspaceIds = [];
    public array $attachedCollectionIds = [];
    public array $selectedWorkspaceIds = [];
    public array $selectedCollectionIds = [];
    public bool $detachFromDrawerOpen = false;
    public ?int $detachFromMovieId = null;
    public ?string $detachFromMovieName = null;
    public string $detachTargetType = 'workspace';
    public string $detachSearch = '';
    public array $detachWorkspaceOptions = [];
    public array $detachCollectionOptions = [];
    public array $selectedDetachWorkspaceIds = [];
    public array $selectedDetachCollectionIds = [];
    public bool $shareDrawerOpen = false;
    public ?Movie $sharingMovie = null;
    public string $shareSearch = '';
    public array $shareSearchResults = [];
    public array $sharedUsers = [];

    protected MovieService $movieService;
    protected AttachmentService $attachmentService;
    protected MovieShareService $movieShareService;

    public function boot(
        MovieService $movieService,
        AttachmentService $attachmentService,
        MovieShareService $movieShareService
    ): void {
        $this->movieService = $movieService;
        $this->attachmentService = $attachmentService;
        $this->movieShareService = $movieShareService;
    }

    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'min:2',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Movie::query()
                        ->where('user_id', auth()->id())
                        ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($value))])
                        ->where(function ($query) {
                            if ($this->year === null) {
                                $query->whereNull('year');
                            } else {
                                $query->where('year', $this->year);
                            }
                        })
                        ->when($this->movieId, function ($query) {
                            $query->where('id', '!=', $this->movieId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('This movie already exists for the selected year.');
                    }
                },
            ],
            'year' => [
                'nullable',
                'integer',
                'min:1800',
                'max:' . now()->addYears(5)->year,
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'tmdb_id' => ['nullable', 'integer', 'min:1'],
            'imdb_id' => ['nullable', 'string', 'max:30'],
            'visibility' => ['required', Rule::in(['private', 'public'])],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function updatedAttachTargetType(): void
    {
        $this->attachSearch = '';
        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];
        $this->drawerPerPage = 12;
    }

    public function updatedDetachTargetType(): void
    {
        $this->detachSearch = '';
        $this->selectedDetachWorkspaceIds = [];
        $this->selectedDetachCollectionIds = [];
        $this->drawerPerPage = 12;
    }

    public function setAccessMode(string $mode): void
    {
        if (! in_array($mode, ['owned', 'shared', 'public'], true)) {
            return;
        }

        $this->accessMode = $mode;

        if ($mode !== 'owned') {
            $this->filter = 'recent';
        }

        $this->selected = [];
        $this->resetPage();
    }

    public function openDrawer(): void
    {
        $this->resetForm();
        $this->drawerMode = 'create';
        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->resetForm();
        $this->showDrawer = false;
    }

    public function save(): void
    {
        $this->validate();

        $posterPath = $this->currentPoster;

        if ($this->drawerMode === 'create') {
            $posterPath = $this->image
                ? $this->image->store('movies', 'public')
                : null;

            $this->movieService->create($this->moviePayload($posterPath));

            $message = 'Movie created successfully.';
        } else {
            $movie = $this->abortIfNotOwner((int) $this->movieId);

            if ($this->removePoster) {
                $posterPath = null;
            }

            if ($this->image) {
                $posterPath = $this->image->store('movies', 'public');
            }

            $this->movieService->update(
                $movie,
                $this->moviePayload($posterPath)
            );

            $message = 'Movie updated successfully.';
        }

        $this->closeDrawer();

        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function moviePayload(?string $posterPath): array
    {
        return [
            'user_id' => auth()->id(),
            'title' => trim($this->title),
            'year' => $this->year,
            'description' => trim($this->description),
            'poster_path' => $posterPath,
            'tmdb_id' => $this->tmdb_id,
            'imdb_id' => $this->imdb_id ? trim($this->imdb_id) : null,
            'visibility' => $this->visibility,
        ];
    }

    public function editMovie(int $movieId): void
    {
        $movie = $this->abortIfNotOwner($movieId);

        $this->movieId = $movie->id;
        $this->title = trim($movie->title);
        $this->year = $movie->year;
        $this->description = $movie->description ?? '';
        $this->tmdb_id = $movie->tmdb_id;
        $this->imdb_id = $movie->imdb_id;
        $this->visibility = $movie->visibility;
        $this->currentPoster = $movie->poster_path;
        $this->drawerMode = 'edit';
        $this->showDrawer = true;
    }

    public function removeCurrentPoster(): void
    {
        $this->removePoster = true;
        $this->currentPoster = null;
    }

    public function confirmDelete(int $movieId): void
    {
        $movie = $this->abortIfNotOwner($movieId);

        $this->deleteMovieId = $movie->id;
        $this->deleteMovieTitle = $movie->title;
        $this->showDeleteModal = true;
    }

    public function deleteMovie(): void
    {
        if (! empty($this->selected) && $this->deleteMovieId === null) {
            if (method_exists($this->movieService, 'bulkDelete')) {
                $this->movieService->bulkDelete($this->selected);
            } else {
                Movie::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('id', $this->selected)
                    ->delete();
            }

            $this->selected = [];
            $message = 'Movie(s) moved to trash successfully.';
        } else {
            $movie = $this->abortIfNotOwner((int) $this->deleteMovieId);

            if (method_exists($this->movieService, 'delete')) {
                $this->movieService->delete($movie);
            } else {
                $movie->delete();
            }

            $message = 'Movie moved to trash successfully.';
        }

        $this->showDeleteModal = false;
        $this->deleteMovieId = null;
        $this->deleteMovieTitle = null;

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleFavorite(int $movieId): void
    {
        $movie = $this->getViewableMovie($movieId);

        if ($movie->user_id !== auth()->id() && $movie->visibility !== 'public') {
            abort(403);
        }

        if ($movie->user_id === auth()->id() && method_exists($this->movieService, 'toggleFavorite')) {
            $this->movieService->toggleFavorite($movie);
            return;
        }

        $movie->update([
            'is_favorite' => ! $movie->is_favorite,
        ]);
    }

    public function bulkFavorite(): void
    {
        if (empty($this->selected)) {
            return;
        }

        if (method_exists($this->movieService, 'bulkFavorite')) {
            $this->movieService->bulkFavorite($this->selected);
        } else {
            Movie::query()
                ->where('user_id', auth()->id())
                ->whereIn('id', $this->selected)
                ->update(['is_favorite' => true]);
        }

        $this->selected = [];

        $this->dispatch('toast', message: 'Selected movies marked as favorite.', type: 'success');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->deleteMovieId = null;
        $this->deleteMovieTitle = count($this->selected) . ' selected movies';
        $this->showDeleteModal = true;
    }

    public function setView(string $view): void
    {
        if (! in_array($view, ['table', 'card', 'masonry'], true)) {
            return;
        }

        $this->view = $view;
        $this->selected = [];
        $this->resetPage();
    }

    public function setPaginationMode(string $mode): void
    {
        if (! in_array($mode, ['pages', 'lazy'], true)) {
            return;
        }

        $this->paginationMode = $mode;
        $this->selected = [];
        $this->perPage = 12;
        $this->resetPage();
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    public function loadMoreDrawer(): void
    {
        $this->drawerPerPage += 12;
    }

    private function resetForm(): void
    {
        $this->movieId = null;
        $this->title = '';
        $this->year = null;
        $this->description = '';
        $this->tmdb_id = null;
        $this->imdb_id = null;
        $this->visibility = 'private';
        $this->image = null;
        $this->currentPoster = null;
        $this->removePoster = false;
        $this->resetValidation();
    }

    private function abortIfNotOwner(int $movieId): Movie
    {
        $movie = Movie::findOrFail($movieId);

        abort_if($movie->user_id !== auth()->id(), 403);

        return $movie;
    }

    private function getViewableMovie(int $movieId): Movie
    {
        $movie = Movie::with('user')->findOrFail($movieId);

        abort_if(
            $movie->user_id !== auth()->id() && $movie->visibility !== 'public',
            403
        );

        return $movie;
    }

    public function openAttachToDrawer(int $movieId): void
    {
        $this->isBulkAttachMode = false;
        $movie = $this->abortIfNotOwner($movieId);

        $this->attachToMovieId = $movie->id;
        $this->attachToMovieName = $movie->title;
        $this->attachTargetType = 'workspace';
        $this->drawerPerPage = 12;

        $this->loadAttachOptions($movie->id);

        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];
        $this->attachSearch = '';

        $this->attachToDrawerOpen = true;
    }

    public function openBulkAttachToDrawer(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->isBulkAttachMode = true;
        $this->attachToMovieId = null;
        $this->attachToMovieName = 'Attach ' . count($this->selected) . ' Movies To';
        $this->attachTargetType = 'workspace';
        $this->drawerPerPage = 12;

        $this->loadAttachOptions();

        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];
        $this->attachSearch = '';

        $this->attachToDrawerOpen = true;
    }

    private function loadAttachOptions(?int $movieId = null): void
    {
        $this->workspaceOptions = Workspace::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->collectionOptions = Collection::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->attachedWorkspaceIds = $movieId
            ? $this->attachmentService->getAttachedWorkspaceIdsForMovie($movieId)
            : [];

        $this->attachedCollectionIds = $movieId
            ? $this->attachmentService->getAttachedCollectionIdsForMovie($movieId)
            : [];
    }

    public function closeAttachToDrawer(): void
    {
        $this->attachToDrawerOpen = false;
        $this->attachToMovieId = null;
        $this->attachToMovieName = null;
        $this->attachTargetType = 'workspace';
        $this->attachSearch = '';

        $this->workspaceOptions = [];
        $this->collectionOptions = [];

        $this->attachedWorkspaceIds = [];
        $this->attachedCollectionIds = [];

        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];

        $this->drawerPerPage = 12;
    }

    public function attachToSelectedTargets(): void
    {
        $selectedTargetIds = $this->attachTargetType === 'collection'
            ? $this->selectedCollectionIds
            : $this->selectedWorkspaceIds;

        if (empty($selectedTargetIds)) {
            $this->dispatch('toast', message: 'Please select at least 1 target.', type: 'error');
            return;
        }

        if ($this->attachToMovieId) {
            if ($this->attachTargetType === 'collection') {
                $this->attachmentService->attachMovieToCollections(
                    $this->attachToMovieId,
                    $selectedTargetIds
                );

                $this->attachedCollectionIds = $this->attachmentService
                    ->getAttachedCollectionIdsForMovie($this->attachToMovieId);

                $message = 'Movie attached to collection(s).';
            } else {
                $this->attachmentService->attachMovieToWorkspaces(
                    $this->attachToMovieId,
                    $selectedTargetIds
                );

                $this->attachedWorkspaceIds = $this->attachmentService
                    ->getAttachedWorkspaceIdsForMovie($this->attachToMovieId);

                $message = 'Movie attached to workspace(s).';
            }
        } else {
            if ($this->attachTargetType === 'collection') {
                $this->attachmentService->bulkAttachMoviesToCollections(
                    $this->selected,
                    $selectedTargetIds
                );

                $message = 'Selected movies attached to collection(s).';
            } else {
                $this->attachmentService->bulkAttachMoviesToWorkspaces(
                    $this->selected,
                    $selectedTargetIds
                );

                $message = 'Selected movies attached to workspace(s).';
            }

            $this->selected = [];
            $this->closeAttachToDrawer();
        }

        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];
        $this->attachSearch = '';

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function openDetachFromDrawer(int $movieId): void
    {
        $movie = $this->abortIfNotOwner($movieId);

        $this->detachFromMovieId = $movie->id;
        $this->detachFromMovieName = $movie->title;
        $this->detachTargetType = 'workspace';
        $this->drawerPerPage = 12;

        $this->loadDetachOptions($movie->id);

        $this->selectedDetachWorkspaceIds = [];
        $this->selectedDetachCollectionIds = [];
        $this->detachSearch = '';

        $this->detachFromDrawerOpen = true;
    }

    private function loadDetachOptions(int $movieId): void
    {
        $attachedWorkspaceIds = $this->attachmentService
            ->getAttachedWorkspaceIdsForMovie($movieId);

        $attachedCollectionIds = $this->attachmentService
            ->getAttachedCollectionIdsForMovie($movieId);

        $this->detachWorkspaceOptions = Workspace::query()
            ->where('user_id', auth()->id())
            ->whereIn('id', $attachedWorkspaceIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->detachCollectionOptions = Collection::query()
            ->where('user_id', auth()->id())
            ->whereIn('id', $attachedCollectionIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function closeDetachFromDrawer(): void
    {
        $this->detachFromDrawerOpen = false;
        $this->detachFromMovieId = null;
        $this->detachFromMovieName = null;
        $this->detachTargetType = 'workspace';
        $this->detachSearch = '';

        $this->detachWorkspaceOptions = [];
        $this->detachCollectionOptions = [];

        $this->selectedDetachWorkspaceIds = [];
        $this->selectedDetachCollectionIds = [];

        $this->drawerPerPage = 12;
    }

    public function detachTargetFromDrawer(string $targetType, int $targetId): void
    {
        if (! $this->detachFromMovieId) {
            return;
        }

        if ($targetType === 'collection') {
            $this->attachmentService->detachMovieFromCollection(
                $this->detachFromMovieId,
                $targetId
            );

            $this->detachCollectionOptions = collect($this->detachCollectionOptions)
                ->reject(fn($collection) => (int) $collection['id'] === $targetId)
                ->values()
                ->toArray();

            $this->selectedDetachCollectionIds = array_values(
                array_diff($this->selectedDetachCollectionIds, [$targetId])
            );

            $message = 'Movie detached from collection.';
        } else {
            $this->attachmentService->detachMovieFromWorkspace(
                $this->detachFromMovieId,
                $targetId
            );

            $this->detachWorkspaceOptions = collect($this->detachWorkspaceOptions)
                ->reject(fn($workspace) => (int) $workspace['id'] === $targetId)
                ->values()
                ->toArray();

            $this->selectedDetachWorkspaceIds = array_values(
                array_diff($this->selectedDetachWorkspaceIds, [$targetId])
            );

            $message = 'Movie detached from workspace.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function detachSelectedTargets(): void
    {
        if (! $this->detachFromMovieId) {
            return;
        }

        $selectedTargetIds = $this->detachTargetType === 'collection'
            ? $this->selectedDetachCollectionIds
            : $this->selectedDetachWorkspaceIds;

        if (empty($selectedTargetIds)) {
            $this->dispatch('toast', message: 'Please select at least 1 target.', type: 'error');
            return;
        }

        foreach ($selectedTargetIds as $targetId) {
            $this->detachTargetFromDrawer(
                $this->detachTargetType,
                (int) $targetId
            );
        }

        $this->selectedDetachWorkspaceIds = [];
        $this->selectedDetachCollectionIds = [];

        $this->dispatch('toast', message: 'Selected attachment(s) detached from movie.', type: 'success');
    }

    public function copyMovieUrl(int $movieId): void
    {
        $movie = $this->getViewableMovie($movieId);

        $url = url(
            '/u/' .
                $movie->user->username .
                '/movies/' .
                $movie->slug
        );

        $this->dispatch('copy-to-clipboard', text: $url);
        $this->dispatch('toast', message: 'Movie link copied.', type: 'success');
    }

    public function duplicateMovie(int $movieId): void
    {
        $this->dispatch('toast', message: 'Movies cannot be duplicated.', type: 'info');
    }

    public function movieStatistics(int $movieId): void
    {
        $this->dispatch('toast', message: 'Movie statistics coming soon.', type: 'info');
    }

    public function movieSettings(int $movieId): void
    {
        $this->dispatch('toast', message: 'Movie settings coming soon.', type: 'info');
    }
    public function openShareDrawer(int $movieId): void
    {
        $this->sharingMovie = $this->abortIfNotOwner($movieId);

        $this->shareDrawerOpen = true;
        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();
    }

    public function updatedShareSearch(): void
    {
        if (! $this->sharingMovie) {
            return;
        }

        $this->shareSearchResults = $this->movieShareService
            ->searchUsers($this->sharingMovie, $this->shareSearch)
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->values()
            ->toArray();
    }

    public function shareWithUser(int $userId): void
    {
        if (! $this->sharingMovie) {
            return;
        }

        $this->movieShareService->shareWithUser(
            $this->sharingMovie,
            $userId
        );

        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();

        $this->dispatch('toast', message: 'Movie shared successfully.', type: 'success');
    }

    public function removeSharedUser(int $userId): void
    {
        if (! $this->sharingMovie) {
            return;
        }

        $this->movieShareService->removeShare(
            $this->sharingMovie,
            $userId
        );

        $this->loadSharedUsers();

        $this->dispatch('toast', message: 'Movie share removed.', type: 'success');
    }

    public function closeShareDrawer(): void
    {
        $this->shareDrawerOpen = false;
        $this->sharingMovie = null;
        $this->shareSearch = '';
        $this->shareSearchResults = [];
        $this->sharedUsers = [];
    }

    private function loadSharedUsers(): void
    {
        if (! $this->sharingMovie) {
            $this->sharedUsers = [];
            return;
        }

        $this->sharedUsers = $this->movieShareService
            ->getSharedUsers($this->sharingMovie)
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->values()
            ->toArray();
    }
    public function copyShareLink(int $movieId): void
    {
        $movie = $this->movieService->findById($movieId);

        if ($movie->visibility !== 'public') {
            // session()->flash('success', 'Make workspace public before sharing.');
            $this->dispatch('toast', message: 'Make movie public before sharing.', type: 'success');
            return;
        }

        $url = url('/u/' . $movie->user->username . '/movies/' . $movie->slug);

        $this->dispatch('copy-to-clipboard', text: $url);

        // session()->flash('success', 'Workspace share link copied.');
        $this->dispatch('toast', message: 'Movie share link copied.', type: 'success');
    }
    public function render()
    {
        return view('livewire.movie.index', [
            'movies' => $this->movieService->getPaginatedMovies(
                auth()->id(),
                $this->accessMode,
                $this->search,
                $this->perPage,
                $this->filter
            ),
            'view' => $this->view,
        ])->layout('layouts.app');
    }
}
