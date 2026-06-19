<?php

namespace App\Livewire\Collection;

use App\Models\Collection;
use App\Services\CollectionService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Workspace;
use App\Services\AttachmentService;
use App\Services\CollectionShareService;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $filter = 'recent';
    public int $perPage = 12;
    public string $view = 'card';
    public string $paginationMode = 'pages';
    public bool $showDrawer = false;
    public string $drawerMode = 'create';
    public ?int $collectionId = null;
    public string $name = '';
    public string $description = '';
    public $image;
    public ?string $currentImage = null;
    public bool $removeImage = false;
    public string $visibility = 'private';
    public array $selected = [];
    public bool $showDeleteModal = false;
    public ?int $deleteCollectionId = null;
    public ?string $deleteCollectionName = null;
    public bool $attachToDrawerOpen = false;
    public ?int $attachToCollectionId = null;
    public ?string $attachToCollectionName = null;
    public array $workspaceOptions = [];
    public array $attachedWorkspaceIds = [];
    public array $selectedWorkspaceIds = [];
    public bool $isBulkAttachMode = false;
    public string $workspaceSearch = '';
    public bool $shareDrawerOpen = false;
    public ?Collection $sharingCollection = null;
    public string $shareSearch = '';
    public array $shareSearchResults = [];
    public array $sharedUsers = [];
    public int $drawerPerPage = 12;
    public bool $detachFromDrawerOpen = false;
    public ?int $detachFromCollectionId = null;
    public ?string $detachFromCollectionName = null;
    public array $detachWorkspaceOptions = [];
    public array $selectedDetachWorkspaceIds = [];
    public string $detachWorkspaceSearch = '';

    protected CollectionService $collectionService;
    protected AttachmentService $attachmentService;
    protected CollectionShareService $collectionShareService;
    public function boot(
        CollectionService $collectionService,
        AttachmentService $attachmentService,
        CollectionShareService $collectionShareService
    ): void {
        $this->collectionService = $collectionService;
        $this->attachmentService = $attachmentService;
        $this->collectionShareService = $collectionShareService;
    }
    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('collections', 'name')
                    ->where(fn($query) => $query->where('user_id', auth()->id()))
                    ->ignore($this->collectionId),
                function ($attribute, $value, $fail) {
                    $exists = Collection::query()
                        ->where('user_id', auth()->id())
                        ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($value))])
                        ->when($this->collectionId, function ($query) {
                            $query->where('id', '!=', $this->collectionId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('The name has already been taken.');
                    }
                },
            ],
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

        if ($this->drawerMode === 'create') {
            $validated['image'] = $this->image
                ? $this->image->store('collections', 'public')
                : null;

            $this->collectionService->create($validated);

            $message = 'Collection created successfully.';
        } else {
            $imagePath = $this->currentImage;

            if ($this->removeImage) {
                $imagePath = null;
            }

            if ($this->image) {
                $imagePath = $this->image->store('collections', 'public');
            }

            $validated['image'] = $imagePath;

            $this->collectionService->update(
                $this->collectionId,
                $validated
            );

            $message = 'Collection updated successfully.';
        }

        $this->closeDrawer();

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function editCollection(int $collectionId): void
    {
        $collection = $this->abortIfNotOwner($collectionId);

        $this->collectionId = $collection->id;
        $this->name = trim($collection->name);
        $this->description = $collection->description ?? '';
        $this->visibility = $collection->visibility;
        $this->currentImage = $collection->image;
        $this->drawerMode = 'edit';
        $this->showDrawer = true;
    }

    public function removeCurrentImage(): void
    {
        $this->removeImage = true;
        $this->currentImage = null;
    }

    public function confirmDelete(int $collectionId): void
    {
        $collection = $this->abortIfNotOwner($collectionId);

        $this->deleteCollectionId = $collection->id;
        $this->deleteCollectionName = $collection->name;
        $this->showDeleteModal = true;
    }

    public function deleteCollection(): void
    {
        if (! empty($this->selected) && $this->deleteCollectionId === null) {
            $this->collectionService->bulkDelete($this->selected);
            $this->selected = [];
            $message = 'Collection(s) moved to trash successfully.';
        } else {
            $this->collectionService->delete($this->deleteCollectionId);
            $message = 'Collection moved to trash successfully.';
        }

        $this->showDeleteModal = false;
        $this->deleteCollectionId = null;
        $this->deleteCollectionName = null;

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleFavorite(int $collectionId): void
    {
        $this->abortIfNotOwner($collectionId);

        $this->collectionService->toggleFavorite($collectionId);
    }

    public function bulkFavorite(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->collectionService->bulkFavorite($this->selected);
        $this->selected = [];

        $this->dispatch('toast', message: 'Selected collections marked as favorite.', type: 'success');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->deleteCollectionId = null;
        $this->deleteCollectionName = count($this->selected) . ' selected collections';
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

    public function setPaginationMode(string $mode): void
    {
        if (! in_array($mode, ['pages', 'lazy'])) {
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
        $this->collectionId = null;
        $this->name = '';
        $this->description = '';
        $this->visibility = 'private';
        $this->image = null;
        $this->currentImage = null;
        $this->removeImage = false;
        $this->resetValidation();
    }

    private function abortIfNotOwner(int $collectionId): Collection
    {
        $collection = Collection::findOrFail($collectionId);

        abort_if($collection->user_id !== auth()->id(), 403);

        return $collection;
    }
    public function openAttachToDrawer(int $collectionId): void
    {
        $this->isBulkAttachMode = false;
        $collection = $this->abortIfNotOwner($collectionId);

        $this->attachToCollectionId = $collection->id;
        $this->attachToCollectionName = $collection->name;
        $this->drawerPerPage = 12;

        $this->workspaceOptions = Workspace::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->attachedWorkspaceIds = $this->attachmentService
            ->getAttachedWorkspaceIdsForCollection($collection->id);

        $this->selectedWorkspaceIds = [];

        $this->attachToDrawerOpen = true;
    }

    public function closeAttachToDrawer(): void
    {
        $this->attachToDrawerOpen = false;
        $this->attachToCollectionId = null;
        $this->attachToCollectionName = null;
        $this->workspaceOptions = [];
        $this->attachedWorkspaceIds = [];
        $this->selectedWorkspaceIds = [];
        $this->workspaceSearch = '';
        $this->drawerPerPage = 12;
    }

    public function attachToSelectedWorkspaces(): void
    {
        if (empty($this->selectedWorkspaceIds)) {

            $this->dispatch('toast', message: 'Please select at least 1 workspace.', type: 'error');

            return;
        }

        if ($this->attachToCollectionId) {
            $this->attachmentService->attachCollectionToWorkspaces(
                $this->attachToCollectionId,
                $this->selectedWorkspaceIds
            );

            $this->attachedWorkspaceIds = $this->attachmentService
                ->getAttachedWorkspaceIdsForCollection($this->attachToCollectionId);

            $message = 'Collection attached to workspace(s).';
        } else {
            $this->attachmentService->bulkAttachCollectionsToWorkspaces(
                $this->selected,
                $this->selectedWorkspaceIds
            );

            $this->selected = [];
            $this->closeAttachToDrawer();

            $message = 'Selected collections attached to workspace(s).';
        }

        $this->selectedWorkspaceIds = [];
        $this->workspaceSearch = '';

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDetachFromWorkspace(int $workspaceId): void
    {
        $this->detachCollectionFromWorkspace($workspaceId);
    }

    public function detachCollectionFromWorkspace(int $workspaceId): void
    {
        if (! $this->attachToCollectionId) {
            return;
        }

        $this->attachmentService->detachCollectionFromWorkspace(
            $this->attachToCollectionId,
            $workspaceId
        );

        $this->attachedWorkspaceIds = $this->attachmentService
            ->getAttachedWorkspaceIdsForCollection($this->attachToCollectionId);

        $this->dispatch('toast', message: 'Collection detached from workspace.', type: 'success');
    }
    public function openBulkAttachToDrawer(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $this->isBulkAttachMode = true;
        $this->attachToCollectionId = null;
        $this->attachToCollectionName = 'Attach ' . count($this->selected) . ' Collections To Workspace';

        $this->workspaceOptions = Workspace::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->attachedWorkspaceIds = [];
        $this->selectedWorkspaceIds = [];

        $this->attachToDrawerOpen = true;
    }
    public function copyCollectionUrl(int $collectionId): void
    {
        $collection = $this->collectionService->findById($collectionId);

        $url = url(
            '/u/' .
                $collection->user->username .
                '/collections/' .
                $collection->slug
        );

        $this->dispatch('copy-to-clipboard', text: $url);
        $this->dispatch('toast', message: 'Collection link copied.', type: 'success');
    }

    public function duplicateCollection(int $collectionId): void
    {
        $this->dispatch('toast', message: 'Duplicate collection feature coming soon.', type: 'info');
    }

    public function collectionStatistics(int $collectionId): void
    {
        $this->dispatch('toast', message: 'Collection statistics coming soon.', type: 'info');
    }

    public function collectionSettings(int $collectionId): void
    {
        $this->dispatch('toast', message: 'Collection settings coming soon.', type: 'info');
    }
    public function openShareDrawer(int $collectionId): void
    {
        $this->sharingCollection = $this->abortIfNotOwner($collectionId);

        $this->shareDrawerOpen = true;
        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();
    }

    public function updatedShareSearch(): void
    {
        if (! $this->sharingCollection) {
            return;
        }

        $this->shareSearchResults = $this->collectionShareService
            ->searchUsers($this->sharingCollection, $this->shareSearch)
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
        if (! $this->sharingCollection) {
            return;
        }

        $this->collectionShareService->shareWithUser(
            $this->sharingCollection,
            $userId
        );

        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();

        $this->dispatch('toast', message: 'Collection shared successfully.', type: 'success');
    }

    public function removeSharedUser(int $userId): void
    {
        if (! $this->sharingCollection) {
            return;
        }

        $this->collectionShareService->removeShare(
            $this->sharingCollection,
            $userId
        );

        $this->loadSharedUsers();

        $this->dispatch('toast', message: 'Collection share removed.', type: 'success');
    }

    public function closeShareDrawer(): void
    {
        $this->shareDrawerOpen = false;
        $this->sharingCollection = null;
        $this->shareSearch = '';
        $this->shareSearchResults = [];
        $this->sharedUsers = [];
    }

    private function loadSharedUsers(): void
    {
        if (! $this->sharingCollection) {
            $this->sharedUsers = [];
            return;
        }

        $this->sharedUsers = $this->collectionShareService
            ->getSharedUsers($this->sharingCollection)
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->values()
            ->toArray();
    }
    public function openDetachFromDrawer(int $collectionId): void
    {
        $collection = $this->abortIfNotOwner($collectionId);

        $this->detachFromCollectionId = $collection->id;
        $this->detachFromCollectionName = $collection->name;
        $this->drawerPerPage = 12;

        $attachedWorkspaceIds = $this->attachmentService
            ->getAttachedWorkspaceIdsForCollection($collection->id);

        $this->detachWorkspaceOptions = Workspace::query()
            ->where('user_id', auth()->id())
            ->whereIn('id', $attachedWorkspaceIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->selectedDetachWorkspaceIds = [];
        $this->detachWorkspaceSearch = '';
        $this->detachFromDrawerOpen = true;
    }

    public function closeDetachFromDrawer(): void
    {
        $this->detachFromDrawerOpen = false;
        $this->detachFromCollectionId = null;
        $this->detachFromCollectionName = null;
        $this->detachWorkspaceOptions = [];
        $this->selectedDetachWorkspaceIds = [];
        $this->detachWorkspaceSearch = '';
        $this->drawerPerPage = 12;
    }

    public function detachWorkspaceFromDrawer(int $workspaceId): void
    {
        if (! $this->detachFromCollectionId) {
            return;
        }

        $this->attachmentService->detachCollectionFromWorkspace(
            $this->detachFromCollectionId,
            $workspaceId
        );

        $this->detachWorkspaceOptions = collect($this->detachWorkspaceOptions)
            ->reject(fn($workspace) => (int) $workspace['id'] === $workspaceId)
            ->values()
            ->toArray();

        $this->selectedDetachWorkspaceIds = array_values(
            array_diff($this->selectedDetachWorkspaceIds, [$workspaceId])
        );

        $this->dispatch('toast', message: 'Collection detached from workspace.', type: 'success');
    }

    public function detachSelectedWorkspaces(): void
    {
        if (! $this->detachFromCollectionId) {
            return;
        }

        if (empty($this->selectedDetachWorkspaceIds)) {
            $this->dispatch('toast', message: 'Please select at least 1 workspace.', type: 'error');
            return;
        }

        foreach ($this->selectedDetachWorkspaceIds as $workspaceId) {
            $this->attachmentService->detachCollectionFromWorkspace(
                $this->detachFromCollectionId,
                (int) $workspaceId
            );
        }

        $this->detachWorkspaceOptions = collect($this->detachWorkspaceOptions)
            ->whereNotIn('id', $this->selectedDetachWorkspaceIds)
            ->values()
            ->toArray();

        $this->selectedDetachWorkspaceIds = [];

        $this->dispatch('toast', message: 'Selected workspace(s) detached from collection.', type: 'success');
    }
    public function render()
    {
        return view('livewire.collection.index', [
            'collections' => $this->collectionService->getPaginatedCollections(
                auth()->id(),
                $this->search,
                $this->perPage,
                $this->filter
            ),
            'view' => $this->view,
        ])->layout('layouts.app');
    }
}
