<?php

namespace App\Livewire\Workspace;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\WorkspaceService;
use Livewire\WithFileUploads;
use App\Models\Workspace;
use App\Services\WorkspaceShareService;

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

    protected function rules(): array
    {
        return [
            'name' => ['required', 'min:3', 'max:255'],
            'description' => ['nullable', 'max:1000'],
            'visibility' => ['required', 'in:private,public'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected WorkspaceService $workspaceService;
    protected WorkspaceShareService $workspaceShareService;
    public bool $shareDrawerOpen = false;
    public ?Workspace $sharingWorkspace = null;
    public string $shareSearch = '';
    public ?int $sharingWorkspaceId = null;
    public array $shareSearchResults = [];
    public array $sharedUsers = [];
    public string $paginationMode = 'pages';

    public function boot(
        WorkspaceService $workspaceService,
        WorkspaceShareService $workspaceShareService
    ): void {
        $this->workspaceService = $workspaceService;
        $this->workspaceShareService = $workspaceShareService;
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

        session()->flash(
            'success',
            $this->drawerMode === 'create'
                ? 'Workspace created successfully.'
                : 'Workspace updated successfully.'
        );
    }

    public function editWorkspace(
        int $workspaceId
    ): void {
        $this->abortIfNotOwner($workspaceId);

        $workspace = $this->workspaceService
            ->findById($workspaceId);

        $this->workspaceId = $workspace->id;
        $this->name = $workspace->name;
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

        session()->flash(
            'success',
            $deleteWorkspaceMessage
        );
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
        session()->flash('success', 'Selected workspaces marked as favorite.');
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
            session()->flash('success', 'Make workspace public before sharing.');
            return;
        }

        $url = url('/u/' . $workspace->user->username . '/workspaces/' . $workspace->slug);

        $this->dispatch('copy-to-clipboard', text: $url);

        session()->flash('success', 'Workspace share link copied.');
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

        session()->flash('success', 'Workspace shared successfully.');
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

        session()->flash('success', 'Workspace share removed.');
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
        session()->flash(
            'success',
            'Duplicate workspace feature coming soon.'
        );
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

        session()->flash(
            'success',
            'Workspace link copied.'
        );
    }

    public function workspaceStatistics(int $workspaceId): void
    {
        session()->flash(
            'success',
            'Workspace statistics coming soon.'
        );
    }
    public function workspaceSettings(int $workspaceId): void
    {
        session()->flash(
            'success',
            'Workspace settings coming soon.'
        );
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
    public function render()
    {
        return view('livewire.workspace.index', [
            'workspaces' => $this->workspaceService
                ->getPaginatedWorkspaces(
                    auth()->id(),
                    $this->search,
                    $this->perPage,
                    $this->filter
                ),
            'view' => $this->view,
        ])->layout('layouts.app');
    }
}
