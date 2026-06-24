<?php

namespace App\Livewire\Workspace;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\WorkspaceService;
use Livewire\WithFileUploads;
use App\Models\Workspace;
use App\Services\WorkspaceShareService;
use SebastianBergmann\Timer\Duration;
use App\Models\Collection;
use App\Services\AttachmentService;
use Illuminate\Validation\Rule;
use App\Models\Movie;
use App\Models\Attachment;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $filter = 'recent';
    public int $perPage = 12;
    public string $view = 'card';
    public bool $showDrawer = false;
    public string $name = '';
    public string $description = '';
    public $image;
    public string $visibility = 'private';
    public ?int $workspaceId = null;
    public string $drawerMode = 'create';
    public bool $removeImage = false;
    public ?string $currentImage = null;
    public ?int $deleteWorkspaceId = null;
    public bool $showDeleteModal = false;
    public ?string $deleteWorkspaceName = null;
    public array $selected = [];
    public ?string $deleteWorkspaceMessage = null;
    public bool $shareDrawerOpen = false;
    public ?Workspace $sharingWorkspace = null;
    public string $shareSearch = '';
    public ?int $sharingWorkspaceId = null;
    public array $shareSearchResults = [];
    public array $sharedUsers = [];
    public string $paginationMode = 'pages';
    public bool $addItemsDrawerOpen = false;
    public ?int $addItemsWorkspaceId = null;
    public ?string $addItemsWorkspaceName = null;
    public string $addItemsType = 'collections';
    public array $collectionOptions = [];
    public array $addedCollectionIds = [];
    public array $selectedCollectionIds = [];
    public string $collectionSearch = '';
    public bool $removeItemsDrawerOpen = false;
    public ?int $removeItemsWorkspaceId = null;
    public ?string $removeItemsWorkspaceName = null;
    public string $removeItemsType = 'collections';
    public array $removeCollectionOptions = [];
    public array $selectedRemoveCollectionIds = [];
    public int $drawerPerPage = 12;
    public string $removeCollectionSearch = '';
    public string $accessMode = 'owned';
    public array $movieOptions = [];
    public array $addedMovieIds = [];
    public array $selectedMovieIds = [];
    public string $movieSearch = '';
    public array $removeMovieOptions = [];
    public array $selectedRemoveMovieIds = [];
    public string $removeMovieSearch = '';

    protected WorkspaceService $workspaceService;
    protected WorkspaceShareService $workspaceShareService;
    protected AttachmentService $attachmentService;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('workspaces', 'name')
                    ->where(fn($query) => $query->where('user_id', auth()->id()))
                    ->ignore($this->workspaceId),
                function ($attribute, $value, $fail) {
                    $exists = Workspace::query()
                        ->where('user_id', auth()->id())
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($value))])
                        ->when($this->workspaceId, function ($query) {
                            $query->where('id', '!=', $this->workspaceId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The name has already been taken.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'visibility' => ['required', Rule::in(['private', 'public'])],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
    public function boot(
        WorkspaceService $workspaceService,
        WorkspaceShareService $workspaceShareService,
        AttachmentService $attachmentService
    ): void {
        $this->workspaceService = $workspaceService;
        $this->workspaceShareService = $workspaceShareService;
        $this->attachmentService = $attachmentService;
    }
    public function mount(): void
    {
        $this->shareSearchResults = [];
        $this->sharedUsers = [];
    }
    public function updatedSearch(): void
    {
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
        $validated = $this->validate();

        $validated['user_id'] = auth()->id();
        $imagePath = null;

        if ($this->image) {
            $imagePath = $this->image->store(
                'workspaces',
                'public'
            );
        }
        $validated['image'] = $imagePath;
        if ($this->drawerMode === 'create') {

            $this->workspaceService
                ->create($validated);
        } else {

            $imagePath = $this->currentImage;

            if ($this->removeImage) {
                $imagePath = null;
            }

            if ($this->image) {
                $imagePath = $this->image->store(
                    'workspaces',
                    'public'
                );
            }

            $validated['image'] = $imagePath;
            $this->workspaceService
                ->update(
                    $this->workspaceId,
                    $validated
                );
        }
        $this->reset([
            'name',
            'description',
        ]);

        $this->visibility = 'private';

        $this->closeDrawer();

        // session()->flash(
        //     'success',
        //     $this->drawerMode === 'create'
        //         ? 'Workspace created successfully.'
        //         : 'Workspace updated successfully.'
        // );
        if ($this->drawerMode === 'create') {
            $this->dispatch('toast', message: 'Workspace created successfully.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Workspace updated successfully.', type: 'success');
        }
    }

    public function editWorkspace(
        int $workspaceId
    ): void {
        $this->abortIfNotOwner($workspaceId);

        $workspace = $this->workspaceService
            ->findById($workspaceId);

        $this->workspaceId = $workspace->id;
        $this->name = trim($workspace->name);
        $this->currentImage = $workspace->image;
        $this->description = $workspace->description ?? '';
        $this->visibility = $workspace->visibility;
        $this->drawerMode = 'edit';
        $this->showDrawer = true;
    }
    private function resetForm(): void
    {
        $this->workspaceId = null;
        $this->name = '';
        $this->description = '';
        $this->visibility = 'private';
        $this->image = null;
        $this->currentImage = null;
        $this->removeImage = false;
        $this->resetValidation();
    }
    public function removeCurrentImage(): void
    {
        $this->removeImage = true;
        $this->currentImage = null;
    }
    public function confirmDelete(int $workspaceId): void
    {
        $this->abortIfNotOwner($workspaceId);

        $workspace = $this->workspaceService
            ->findById($workspaceId);

        $this->deleteWorkspaceId = $workspace->id;
        $this->deleteWorkspaceName = $workspace->name;
        $this->showDeleteModal = true;
    }

    public function deleteWorkspace(): void
    {
        if (! empty($this->selected) && $this->deleteWorkspaceId === null) {
            $this->workspaceService->bulkDelete($this->selected);
            $this->selected = [];
            $deleteWorkspaceMessage = "Workspace(s) moved to trash successfully.";
        } else {
            $this->workspaceService->delete($this->deleteWorkspaceId);
            $deleteWorkspaceMessage = "Workspace moved to trash successfully.";
        }

        $this->showDeleteModal = false;
        $this->deleteWorkspaceId = null;
        $this->deleteWorkspaceName = null;

        // session()->flash('success',$deleteWorkspaceMessage);
        $this->dispatch('toast', message: $deleteWorkspaceMessage, type: 'success');
    }
    public function toggleFavorite(int $workspaceId): void
    {
        $this->abortIfNotOwner($workspaceId);

        $this->workspaceService
            ->toggleFavorite($workspaceId);
    }
    public function updatedFilter(): void
    {
        $this->selected = [];
        $this->resetPage();
    }
    public function bulkFavorite(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->workspaceService->bulkFavorite($this->selected);
        $this->selected = [];
        // session()->flash('success', 'Selected workspaces marked as favorite.');
        $this->dispatch('toast', message: 'Selected workspaces marked as favorite.', type: 'success');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->deleteWorkspaceId = null;
        $this->deleteWorkspaceName = count($this->selected) . ' selected workspaces';
        $this->showDeleteModal = true;
    }
    public function setView(string $view): void
    {
        if (! in_array($view, ['table', 'card', 'masonry'])) {
            return;
        }

        $this->view = $view;
        $this->selected = [];
        $this->resetPage();
    }
    public function copyShareLink(int $workspaceId): void
    {
        $workspace = $this->workspaceService->findById($workspaceId);

        if ($workspace->visibility !== 'public') {
            // session()->flash('success', 'Make workspace public before sharing.');
            $this->dispatch('toast', message: 'Make workspace public before sharing.', type: 'success');
            return;
        }

        $url = url('/u/' . $workspace->user->username . '/workspaces/' . $workspace->slug);

        $this->dispatch('copy-to-clipboard', text: $url);

        // session()->flash('success', 'Workspace share link copied.');
        $this->dispatch('toast', message: 'Workspace share link copied.', type: 'success');
    }

    public function openShareDrawer(int $workspaceId): void
    {
        $this->sharingWorkspace = $this->abortIfNotOwner($workspaceId);
        $this->sharingWorkspace = Workspace::findOrFail($workspaceId);
        abort_if($this->sharingWorkspace->user_id !== auth()->id(), 403);
        $this->shareDrawerOpen = true;
        $this->shareSearch = '';
        $this->loadSharedUsers();
        $this->shareSearchResults = [];
    }

    public function updatedShareSearch(): void
    {
        if (! $this->sharingWorkspace) {
            return;
        }

        $this->shareSearchResults = $this->workspaceShareService
            ->searchUsers($this->sharingWorkspace, $this->shareSearch)
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
        if (! $this->sharingWorkspace) {
            return;
        }

        $this->workspaceShareService->shareWithUser(
            $this->sharingWorkspace,
            $userId
        );

        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();

        // session()->flash('success', 'Workspace shared successfully.');
        $this->dispatch('toast', message: 'Workspace shared successfully.', type: 'success');
    }

    public function removeSharedUser(int $userId): void
    {
        if (! $this->sharingWorkspace) {
            return;
        }

        $this->workspaceShareService->removeShare(
            $this->sharingWorkspace,
            $userId
        );

        $this->loadSharedUsers();

        // session()->flash('success', 'Workspace share removed.');
        $this->dispatch('toast', message: 'Workspace share removed.', type: 'success');
    }

    public function closeShareDrawer(): void
    {
        $this->shareDrawerOpen = false;
        $this->sharingWorkspace = null;
        $this->shareSearch = '';
        $this->shareSearchResults = [];
        $this->sharedUsers = [];
    }

    private function loadSharedUsers(): void
    {
        if (! $this->sharingWorkspace) {
            $this->sharedUsers = [];
            return;
        }

        $this->sharedUsers = $this->workspaceShareService
            ->getSharedUsers($this->sharingWorkspace)
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->values()
            ->toArray();
    }
    private function abortIfNotOwner(int $workspaceId): Workspace
    {
        $workspace = Workspace::findOrFail($workspaceId);

        abort_if($workspace->user_id !== auth()->id(), 403);

        return $workspace;
    }
    public function duplicateWorkspace(int $workspaceId): void
    {
        // session()->flash(
        //     'success',
        //     'Duplicate workspace feature coming soon.'
        // );
        $this->dispatch('toast', message: 'Duplicate workspace feature coming soon.', type: 'info');
    }

    public function copyWorkspaceUrl(int $workspaceId): void
    {
        $workspace = $this->workspaceService->findById($workspaceId);

        $url = url(
            '/u/' .
                $workspace->user->username .
                '/workspaces/' .
                $workspace->slug
        );

        $this->dispatch('copy-to-clipboard', text: $url);

        // session()->flash('success', 'Workspace link copied.');
        $this->dispatch('toast', message: 'Workspace link copied.', type: 'success');
    }

    public function workspaceStatistics(int $workspaceId): void
    {
        // session()->flash('success', 'Workspace statistics coming soon.');
        $this->dispatch('toast', message: 'Workspace statistics coming soon.', type: 'info');
    }
    public function workspaceSettings(int $workspaceId): void
    {
        // session()->flash('success', 'Workspace settings coming soon.');
        $this->dispatch('toast', message: 'Workspace settings coming soon.', type: 'info');
    }
    public function setPaginationMode(string $mode): void
    {
        if (! in_array($mode, ['pages', 'lazy'])) {
            return;
        }

        $this->paginationMode = $mode;
        $this->selected = [];
        $this->resetPage();

        if ($mode === 'pages') {
            $this->perPage = 12;
        }

        if ($mode === 'lazy') {
            $this->perPage = 12;
        }
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }
    public function openAddItemsDrawer(int $workspaceId): void
    {
        $workspace = $this->abortIfNotOwner($workspaceId);

        $this->addItemsWorkspaceId = $workspace->id;
        $this->addItemsWorkspaceName = $workspace->name;
        $this->addItemsType = 'collections';
        $this->drawerPerPage = 12;

        $this->collectionOptions = Collection::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->addedCollectionIds = Attachment::query()
            ->where('container_type', 'workspace')
            ->where('container_id', $workspace->id)
            ->where('attachable_type', 'collection')
            ->pluck('attachable_id')
            ->toArray();

        $this->movieOptions = Movie::query()
            ->where('user_id', auth()->id())
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn($movie) => [
                'id' => $movie->id,
                'name' => $movie->title,
            ])
            ->toArray();

        $this->addedMovieIds = Attachment::query()
            ->where('container_type', 'workspace')
            ->where('container_id', $workspace->id)
            ->where('attachable_type', 'movie')
            ->pluck('attachable_id')
            ->toArray();

        $this->selectedCollectionIds = [];
        $this->selectedMovieIds = [];
        $this->addItemsDrawerOpen = true;
    }
    public function closeAddItemsDrawer(): void
    {
        $this->addItemsDrawerOpen = false;
        $this->addItemsWorkspaceId = null;
        $this->addItemsWorkspaceName = null;
        $this->addItemsType = 'collections';
        $this->collectionOptions = [];
        $this->addedCollectionIds = [];
        $this->selectedCollectionIds = [];
        $this->collectionSearch = '';
        $this->drawerPerPage = 12;
        $this->movieOptions = [];
        $this->addedMovieIds = [];
        $this->selectedMovieIds = [];
        $this->movieSearch = '';
    }

    public function addSelectedItems(): void
    {
        if (! $this->addItemsWorkspaceId) {
            return;
        }

        if ($this->addItemsType === 'collections') {
            if (empty($this->selectedCollectionIds)) {
                $this->dispatch('toast', message: 'Please select at least 1 collection.', type: 'error');
                return;
            } else {
                foreach ($this->selectedCollectionIds as $collectionId) {
                    $this->attachmentService->attachCollectionToWorkspaces(
                        (int) $collectionId,
                        [$this->addItemsWorkspaceId]
                    );
                }

                $this->addedCollectionIds = Attachment::query()
                    ->where('container_type', 'workspace')
                    ->where('container_id', $this->addItemsWorkspaceId)
                    ->where('attachable_type', 'collection')
                    ->pluck('attachable_id')
                    ->toArray();

                $this->selectedCollectionIds = [];
                $this->collectionSearch = '';
                $this->dispatch('toast', message: 'Selected collection(s) added successfully.', type: 'success');
            }
        } elseif ($this->addItemsType === 'movies') {
            if (empty($this->selectedMovieIds)) {
                $this->dispatch('toast', message: 'Please select at least 1 movie.', type: 'error');
                return;
            } else {
                foreach ($this->selectedMovieIds as $movieId) {
                    $this->attachmentService->attachMovieToWorkspaces(
                        (int) $movieId,
                        [$this->addItemsWorkspaceId]
                    );
                }

                $this->addedMovieIds = Attachment::query()
                    ->where('container_type', 'workspace')
                    ->where('container_id', $this->addItemsWorkspaceId)
                    ->where('attachable_type', 'movie')
                    ->pluck('attachable_id')
                    ->toArray();

                $this->selectedMovieIds = [];
                $this->movieSearch = '';
                $this->dispatch('toast', message: 'Selected movie(s) added successfully.', type: 'success');
            }
        }
    }

    public function detachCollectionItem(int $collectionId): void
    {
        if (! $this->addItemsWorkspaceId) {
            return;
        }

        $this->attachmentService->detachCollectionFromWorkspace(
            $collectionId,
            $this->addItemsWorkspaceId
        );

        $this->addedCollectionIds = \App\Models\Attachment::query()
            ->where('container_type', 'workspace')
            ->where('container_id', $this->addItemsWorkspaceId)
            ->where('attachable_type', 'collection')
            ->pluck('attachable_id')
            ->toArray();

        $this->dispatch('toast', message: 'Collection detached from workspace.', type: 'success');
    }
    public function openRemoveItemsDrawer(int $workspaceId): void
    {
        $workspace = $this->abortIfNotOwner($workspaceId);

        $this->removeItemsWorkspaceId = $workspace->id;
        $this->removeItemsWorkspaceName = $workspace->name;
        $this->removeItemsType = 'collections';
        $this->drawerPerPage = 12;

        $this->removeCollectionOptions = \App\Models\Collection::query()
            ->where('user_id', auth()->id())
            ->whereHas('attachedWorkspaces', function ($query) use ($workspace) {
                $query->where('container_id', $workspace->id);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->removeMovieOptions = Movie::query()
            ->where('user_id', auth()->id())
            ->whereHas('attachedWorkspaces', function ($query) use ($workspace) {
                $query->where('container_id', $workspace->id);
            })
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn($movie) => [
                'id' => $movie->id,
                'name' => $movie->title,
            ])
            ->toArray();

        $this->selectedRemoveCollectionIds = [];
        $this->removeCollectionSearch = '';
        $this->selectedRemoveMovieIds = [];
        $this->removeMovieSearch = '';
        $this->removeItemsDrawerOpen = true;
    }

    public function closeRemoveItemsDrawer(): void
    {
        $this->removeItemsDrawerOpen = false;
        $this->removeItemsWorkspaceId = null;
        $this->removeItemsWorkspaceName = null;
        $this->removeItemsType = 'collections';
        $this->removeCollectionOptions = [];
        $this->selectedRemoveCollectionIds = [];
        $this->removeCollectionSearch = '';
        $this->drawerPerPage = 12;
        $this->removeMovieOptions = [];
        $this->selectedRemoveMovieIds = [];
        $this->removeMovieSearch = '';
    }

    public function detachCollectionFromRemoveDrawer(int $collectionId): void
    {
        if (! $this->removeItemsWorkspaceId) {
            return;
        }

        $this->attachmentService->detachCollectionFromWorkspace(
            $collectionId,
            $this->removeItemsWorkspaceId
        );

        $this->removeCollectionOptions = collect($this->removeCollectionOptions)
            ->reject(fn($collection) => (int) $collection['id'] === $collectionId)
            ->values()
            ->toArray();

        $this->selectedRemoveCollectionIds = array_values(
            array_diff($this->selectedRemoveCollectionIds, [$collectionId])
        );

        $this->dispatch('toast', message: 'Collection detached from workspace.', type: 'success');
    }

    public function removeSelectedItems(): void
    {
        if (! $this->removeItemsWorkspaceId) {
            return;
        }
        if ($this->removeItemsType === 'collections') {
            if (empty($this->selectedRemoveCollectionIds)) {
                $this->dispatch('toast', message: 'Please select at least 1 collection.', type: 'error');
                return;
            } else {

                foreach ($this->selectedRemoveCollectionIds as $collectionId) {
                    $this->attachmentService->detachCollectionFromWorkspace(
                        (int) $collectionId,
                        $this->removeItemsWorkspaceId
                    );
                }

                $this->removeCollectionOptions = collect($this->removeCollectionOptions)
                    ->whereNotIn('id', $this->selectedRemoveCollectionIds)
                    ->values()
                    ->toArray();

                $this->selectedRemoveCollectionIds = [];

                $this->dispatch('toast', message: 'Selected collection(s) detached from workspace.', type: 'success');
            }
        } elseif ($this->removeItemsType === 'movies') {
            if (empty($this->selectedRemoveMovieIds)) {
                $this->dispatch('toast', message: 'Please select at least 1 movie.', type: 'error');
                return;
            } else {
                foreach ($this->selectedRemoveMovieIds as $movieId) {
                    $this->attachmentService->detachMovieFromWorkspace(
                        (int) $movieId,
                        $this->removeItemsWorkspaceId
                    );
                }

                $this->removeMovieOptions = collect($this->removeMovieOptions)
                    ->whereNotIn('id', $this->selectedRemoveMovieIds)
                    ->values()
                    ->toArray();

                $this->selectedRemoveMovieIds = [];

                $this->dispatch('toast', message: 'Selected movie(s) detached from workspace.', type: 'success');
            }
        }
    }
    public function loadMoreDrawer(): void
    {
        $this->drawerPerPage += 12;
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
    public function detachMovieFromRemoveDrawer(int $movieId): void
    {
        if (! $this->removeItemsWorkspaceId) {
            return;
        }

        $this->attachmentService->detachMovieFromWorkspace(
            $movieId,
            $this->removeItemsWorkspaceId
        );

        $this->removeMovieOptions = collect($this->removeMovieOptions)
            ->reject(fn($movie) => (int) $movie['id'] === $movieId)
            ->values()
            ->toArray();

        $this->selectedRemoveMovieIds = array_values(
            array_diff($this->selectedRemoveMovieIds, [$movieId])
        );

        $this->dispatch('toast', message: 'Movie detached from workspace.', type: 'success');
    }
    public function render()
    {
        return view('livewire.workspace.index', [
            'workspaces' => $this->workspaceService
                ->getPaginatedWorkspaces(
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
