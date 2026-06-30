<?php

namespace App\Livewire\Book;

use App\Models\Collection;
use App\Models\Book;
use App\Models\Workspace;
use App\Services\AttachmentService;
use App\Services\BookService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Services\BookShareService;
use App\Services\LikeService;
use App\Livewire\Concerns\HasUserViewPreferences;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasUserViewPreferences;

    public string $search = '';
    public string $filter = 'recent';
    public string $accessMode = 'owned';

    public int $perPage = 12;
    public string $view = 'card';
    public string $paginationMode = 'pages';
    public bool $showDrawer = false;
    public string $drawerMode = 'create';
    public ?int $bookId = null;
    public string $title = '';
    public ?int $year = null;
    public string $description = '';
    public ?string $author = null;
    public ?string $publisher = null;
    public $image;
    public ?string $currentCover = null;
    public bool $removeCover = false;
    public string $visibility = 'private';
    public array $selected = [];
    public bool $showDeleteModal = false;
    public ?int $deleteBookId = null;
    public ?string $deleteBookTitle = null;
    public int $drawerPerPage = 12;
    public bool $attachToDrawerOpen = false;
    public bool $isBulkAttachMode = false;
    public ?int $attachToBookId = null;
    public ?string $attachToBookName = null;
    public string $attachTargetType = 'workspace';
    public string $attachSearch = '';
    public array $workspaceOptions = [];
    public array $collectionOptions = [];
    public array $attachedWorkspaceIds = [];
    public array $attachedCollectionIds = [];
    public array $selectedWorkspaceIds = [];
    public array $selectedCollectionIds = [];
    public bool $detachFromDrawerOpen = false;
    public ?int $detachFromBookId = null;
    public ?string $detachFromBookName = null;
    public string $detachTargetType = 'workspace';
    public string $detachSearch = '';
    public array $detachWorkspaceOptions = [];
    public array $detachCollectionOptions = [];
    public array $selectedDetachWorkspaceIds = [];
    public array $selectedDetachCollectionIds = [];
    public bool $shareDrawerOpen = false;
    public ?Book $sharingBook = null;
    public string $shareSearch = '';
    public array $shareSearchResults = [];
    public array $sharedUsers = [];

    protected BookService $bookService;
    protected AttachmentService $attachmentService;
    protected BookShareService $bookShareService;
    protected LikeService $likeService;

    public function boot(
        BookService $bookService,
        AttachmentService $attachmentService,
        BookShareService $bookShareService,
        LikeService $likeService
    ): void {
        $this->bookService = $bookService;
        $this->attachmentService = $attachmentService;
        $this->bookShareService = $bookShareService;
        $this->likeService = $likeService;
    }
    public function mount(): void
    {
        $this->shareSearchResults = [];
        $this->sharedUsers = [];
        $this->loadUserViewPreferences();
    }
    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'min:2',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Book::query()
                        ->where('user_id', auth()->id())
                        ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($value))])
                        ->where(function ($query) {
                            if ($this->year === null) {
                                $query->whereNull('year');
                            } else {
                                $query->where('year', $this->year);
                            }
                        })
                        ->when($this->bookId, function ($query) {
                            $query->where('id', '!=', $this->bookId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('This book already exists for the selected year.');
                    }
                },
            ],
            'year' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . now()->year,
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'author' => ['nullable', 'string', 'max:100'],
            'publisher' => ['nullable', 'string', 'max:100'],
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

        $coverPath = $this->currentCover;

        if ($this->drawerMode === 'create') {
            $coverPath = $this->image
                ? $this->image->store('books', 'public')
                : null;

            $this->bookService->create($this->bookPayload($coverPath));

            $message = 'Book created successfully.';
        } else {
            $book = $this->abortIfNotOwner((int) $this->bookId);

            if ($this->removeCover) {
                $coverPath = null;
            }

            if ($this->image) {
                $coverPath = $this->image->store('books', 'public');
            }

            $this->bookService->update(
                $book,
                $this->bookPayload($coverPath)
            );

            $message = 'Book updated successfully.';
        }

        $this->closeDrawer();

        $this->dispatch('toast', message: $message, type: 'success');
    }

    private function bookPayload(?string $coverPath): array
    {
        return [
            'user_id' => auth()->id(),
            'title' => trim($this->title),
            'year' => $this->year,
            'description' => trim($this->description),
            'cover_image' => $coverPath,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'visibility' => $this->visibility,
        ];
    }

    public function editBook(int $bookId): void
    {
        $book = $this->abortIfNotOwner($bookId);

        $this->bookId = $book->id;
        $this->title = trim($book->title);
        $this->year = $book->year;
        $this->description = $book->description ?? '';
        $this->author = $book->author;
        $this->publisher = $book->publisher;
        $this->visibility = $book->visibility;
        $this->currentCover = $book->cover_image;
        $this->drawerMode = 'edit';
        $this->showDrawer = true;
    }

    public function removeCurrentCover(): void
    {
        $this->removeCover = true;
        $this->currentCover = null;
    }

    public function confirmDelete(int $bookId): void
    {
        $book = $this->abortIfNotOwner($bookId);

        $this->deleteBookId = $book->id;
        $this->deleteBookTitle = $book->title;
        $this->showDeleteModal = true;
    }

    public function deleteBook(): void
    {
        if (! empty($this->selected) && $this->deleteBookId === null) {
            if (method_exists($this->bookService, 'bulkDelete')) {
                $this->bookService->bulkDelete($this->selected);
            } else {
                Book::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('id', $this->selected)
                    ->delete();
            }

            $this->selected = [];
            $message = 'Book(s) moved to trash successfully.';
        } else {
            $book = $this->abortIfNotOwner((int) $this->deleteBookId);

            if (method_exists($this->bookService, 'delete')) {
                $this->bookService->delete($book);
            } else {
                $book->delete();
            }

            $message = 'Book moved to trash successfully.';
        }

        $this->showDeleteModal = false;
        $this->deleteBookId = null;
        $this->deleteBookTitle = null;

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleFavorite(int $bookId): void
    {
        $book = $this->getViewableBook($bookId);

        if ($book->user_id !== auth()->id() && $book->visibility !== 'public') {
            abort(403);
        }

        if ($book->user_id === auth()->id() && method_exists($this->bookService, 'toggleFavorite')) {
            $this->bookService->toggleFavorite($book);
            return;
        }

        $book->update([
            'is_favorite' => ! $book->is_favorite,
        ]);
    }

    public function bulkFavorite(): void
    {
        if (empty($this->selected)) {
            return;
        }

        if (method_exists($this->bookService, 'bulkFavorite')) {
            $this->bookService->bulkFavorite($this->selected);
        } else {
            Book::query()
                ->where('user_id', auth()->id())
                ->whereIn('id', $this->selected)
                ->update(['is_favorite' => true]);
        }

        $this->selected = [];

        $this->dispatch('toast', message: 'Selected books marked as favorite.', type: 'success');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->deleteBookId = null;
        $this->deleteBookTitle = count($this->selected) . ' selected books';
        $this->showDeleteModal = true;
    }

    // public function setView(string $view): void
    // {
    //     if (! in_array($view, ['table', 'card', 'masonry'], true)) {
    //         return;
    //     }

    //     $this->view = $view;
    //     $this->selected = [];
    //     $this->resetPage();
    // }

    // public function setPaginationMode(string $mode): void
    // {
    //     if (! in_array($mode, ['pages', 'lazy'], true)) {
    //         return;
    //     }

    //     $this->paginationMode = $mode;
    //     $this->selected = [];
    //     $this->perPage = 12;
    //     $this->resetPage();
    // }

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
        $this->bookId = null;
        $this->title = '';
        $this->year = null;
        $this->description = '';
        $this->author = null;
        $this->publisher = null;
        $this->visibility = 'private';
        $this->image = null;
        $this->currentCover = null;
        $this->removeCover = false;
        $this->resetValidation();
    }

    private function abortIfNotOwner(int $bookId): Book
    {
        $book = Book::findOrFail($bookId);

        abort_if($book->user_id !== auth()->id(), 403);

        return $book;
    }

    private function getViewableBook(int $bookId): Book
    {
        $book = Book::with('user')->findOrFail($bookId);

        abort_if(
            $book->user_id !== auth()->id() && $book->visibility !== 'public',
            403
        );

        return $book;
    }

    public function openAttachToDrawer(int $bookId): void
    {
        $this->isBulkAttachMode = false;
        $book = $this->abortIfNotOwner($bookId);

        $this->attachToBookId = $book->id;
        $this->attachToBookName = $book->title;
        $this->attachTargetType = 'workspace';
        $this->drawerPerPage = 12;

        $this->loadAttachOptions($book->id);

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
        $this->attachToBookId = null;
        $this->attachToBookName = 'Attach ' . count($this->selected) . ' Books To';
        $this->attachTargetType = 'workspace';
        $this->drawerPerPage = 12;

        $this->loadAttachOptions();

        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];
        $this->attachSearch = '';

        $this->attachToDrawerOpen = true;
    }

    private function loadAttachOptions(?int $bookId = null): void
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

        $this->attachedWorkspaceIds = $bookId
            ? $this->attachmentService->getAttachedWorkspaceIdsForBook($bookId)
            : [];

        $this->attachedCollectionIds = $bookId
            ? $this->attachmentService->getAttachedCollectionIdsForBook($bookId)
            : [];
    }

    public function closeAttachToDrawer(): void
    {
        $this->attachToDrawerOpen = false;
        $this->attachToBookId = null;
        $this->attachToBookName = null;
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

        if ($this->attachToBookId) {
            if ($this->attachTargetType === 'collection') {
                $this->attachmentService->attachBookToCollections(
                    $this->attachToBookId,
                    $selectedTargetIds
                );

                $this->attachedCollectionIds = $this->attachmentService
                    ->getAttachedCollectionIdsForBook($this->attachToBookId);

                $message = 'Book attached to collection(s).';
            } else {
                $this->attachmentService->attachBookToWorkspaces(
                    $this->attachToBookId,
                    $selectedTargetIds
                );

                $this->attachedWorkspaceIds = $this->attachmentService
                    ->getAttachedWorkspaceIdsForBook($this->attachToBookId);

                $message = 'Book attached to workspace(s).';
            }
        } else {
            if ($this->attachTargetType === 'collection') {
                $this->attachmentService->bulkAttachBooksToCollections(
                    $this->selected,
                    $selectedTargetIds
                );

                $message = 'Selected books attached to collection(s).';
            } else {
                $this->attachmentService->bulkAttachBooksToWorkspaces(
                    $this->selected,
                    $selectedTargetIds
                );

                $message = 'Selected books attached to workspace(s).';
            }

            $this->selected = [];
            $this->closeAttachToDrawer();
        }

        $this->selectedWorkspaceIds = [];
        $this->selectedCollectionIds = [];
        $this->attachSearch = '';

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function openDetachFromDrawer(int $bookId): void
    {
        $book = $this->abortIfNotOwner($bookId);

        $this->detachFromBookId = $book->id;
        $this->detachFromBookName = $book->title;
        $this->detachTargetType = 'workspace';
        $this->drawerPerPage = 12;

        $this->loadDetachOptions($book->id);

        $this->selectedDetachWorkspaceIds = [];
        $this->selectedDetachCollectionIds = [];
        $this->detachSearch = '';

        $this->detachFromDrawerOpen = true;
    }

    private function loadDetachOptions(int $bookId): void
    {
        $attachedWorkspaceIds = $this->attachmentService
            ->getAttachedWorkspaceIdsForBook($bookId);

        $attachedCollectionIds = $this->attachmentService
            ->getAttachedCollectionIdsForBook($bookId);

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
        $this->detachFromBookId = null;
        $this->detachFromBookName = null;
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
        if (! $this->detachFromBookId) {
            return;
        }

        if ($targetType === 'collection') {
            $this->attachmentService->detachBookFromCollection(
                $this->detachFromBookId,
                $targetId
            );

            $this->detachCollectionOptions = collect($this->detachCollectionOptions)
                ->reject(fn($collection) => (int) $collection['id'] === $targetId)
                ->values()
                ->toArray();

            $this->selectedDetachCollectionIds = array_values(
                array_diff($this->selectedDetachCollectionIds, [$targetId])
            );

            $message = 'Book detached from collection.';
        } else {
            $this->attachmentService->detachBookFromWorkspace(
                $this->detachFromBookId,
                $targetId
            );

            $this->detachWorkspaceOptions = collect($this->detachWorkspaceOptions)
                ->reject(fn($workspace) => (int) $workspace['id'] === $targetId)
                ->values()
                ->toArray();

            $this->selectedDetachWorkspaceIds = array_values(
                array_diff($this->selectedDetachWorkspaceIds, [$targetId])
            );

            $message = 'Book detached from workspace.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function detachSelectedTargets(): void
    {
        if (! $this->detachFromBookId) {
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

        $this->dispatch('toast', message: 'Selected attachment(s) detached from book.', type: 'success');
    }

    public function copyBookUrl(int $bookId): void
    {
        $book = $this->getViewableBook($bookId);

        $url = url(
            '/u/' .
                $book->user->username .
                '/books/' .
                $book->slug
        );

        $this->dispatch('copy-to-clipboard', text: $url);
        $this->dispatch('toast', message: 'Book link copied.', type: 'success');
    }

    public function duplicateBook(int $bookId): void
    {
        $this->dispatch('toast', message: 'Books cannot be duplicated.', type: 'info');
    }

    public function bookStatistics(int $bookId): void
    {
        $this->dispatch('toast', message: 'Book statistics coming soon.', type: 'info');
    }

    public function bookSettings(int $bookId): void
    {
        $this->dispatch('toast', message: 'Book settings coming soon.', type: 'info');
    }
    public function openShareDrawer(int $bookId): void
    {
        $this->sharingBook = $this->abortIfNotOwner($bookId);

        $this->shareDrawerOpen = true;
        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();
    }

    public function updatedShareSearch(): void
    {
        if (! $this->sharingBook) {
            return;
        }

        $this->shareSearchResults = $this->bookShareService
            ->searchUsers($this->sharingBook, $this->shareSearch)
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
        if (! $this->sharingBook) {
            return;
        }

        $this->bookShareService->shareWithUser(
            $this->sharingBook,
            $userId
        );

        $this->shareSearch = '';
        $this->shareSearchResults = [];

        $this->loadSharedUsers();

        $this->dispatch('toast', message: 'Book shared successfully.', type: 'success');
    }

    public function removeSharedUser(int $userId): void
    {
        if (! $this->sharingBook) {
            return;
        }

        $this->bookShareService->removeShare(
            $this->sharingBook,
            $userId
        );

        $this->loadSharedUsers();

        $this->dispatch('toast', message: 'Book share removed.', type: 'success');
    }

    public function closeShareDrawer(): void
    {
        $this->shareDrawerOpen = false;
        $this->sharingBook = null;
        $this->shareSearch = '';
        $this->shareSearchResults = [];
        $this->sharedUsers = [];
    }

    private function loadSharedUsers(): void
    {
        if (! $this->sharingBook) {
            $this->sharedUsers = [];
            return;
        }

        $this->sharedUsers = $this->bookShareService
            ->getSharedUsers($this->sharingBook)
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->values()
            ->toArray();
    }
    public function copyShareLink(int $bookId): void
    {
        $book = $this->bookService->findById($bookId);

        if ($book->visibility !== 'public') {
            // session()->flash('success', 'Make workspace public before sharing.');
            $this->dispatch('toast', message: 'Make book public before sharing.', type: 'success');
            return;
        }

        $url = url('/u/' . $book->user->username . '/books/' . $book->slug);

        $this->dispatch('copy-to-clipboard', text: $url);

        // session()->flash('success', 'Workspace share link copied.');
        $this->dispatch('toast', message: 'Book share link copied.', type: 'success');
    }
    public function toggleLike(int $bookId): void
    {
        $book = Book::with('shares')->findOrFail($bookId);

        $this->likeService->toggle($book);
    }

    public function render()
    {
        return view('livewire.book.index', [
            'books' => $this->bookService->getPaginatedBooks(
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
