<div class="p-6 space-y-6" x-data="{ activeMenu: null }">
    {{-- @if (session('success'))
        <div class="rounded-lg bg-green-100 p-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif --}}

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">
            Workspaces
        </h1>

        <x-ui.button variant="primary" wire:click="openDrawer">
            + New Workspace
        </x-ui.button>
    </div>

    {{-- Search --}}
    <div>
        <x-ui.search-input model="search" placeholder="Search Workspaces..." />
    </div>

    {{-- Filters + View Switcher --}}
    <x-module-toolbar :filter="$filter" :accessMode="$accessMode" :paginationMode="$paginationMode" :view="$view" />
    @if (count($selected))
        <div class="sticky top-20 z-30 flex items-center justify-between
               rounded-xl px-4 py-2 shadow-md"
            style="
            background: linear-gradient(
                to right,
                #f48fb1,
                #b388ff,
                #4298e1
            );
        ">
            <span class="font-semibold text-white">
                {{ count($selected) }} workspace(s) selected
            </span>

            <div class="flex gap-2">
                <x-ui.button variant="secondary" wire:click="bulkFavorite">
                    ⭐ Add to favorite
                </x-ui.button>

                <x-ui.button variant="danger" wire:click="confirmBulkDelete">
                    🗑 Move to trash
                </x-ui.button>
            </div>
        </div>
    @endif
    {{-- Workspace Cards --}}
    @if ($view === 'table')
        @include('livewire.workspace.partials.table-view')
    @elseif($view === 'masonry')
        @include('livewire.workspace.partials.masonry-view')
    @else
        @include('livewire.workspace.partials.card-view')
    @endif
    {{-- Pagination --}}
    <div>
        @if ($paginationMode === 'pages')
            {{ $workspaces->links() }}
        @else
            @if ($workspaces->hasMorePages())
                <div x-data x-intersect="$wire.loadMore()" class="flex justify-center py-6">
                    <div wire:loading wire:target="loadMore" class="flex items-center gap-2 text-sm text-gray-500">
                        <span
                            class="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"></span>
                        Loading more workspaces...
                    </div>

                    <div wire:loading.remove wire:target="loadMore" class="text-sm text-gray-400">
                        Scroll to load more
                    </div>
                </div>
            @else
                <div class="py-6 text-center text-sm text-gray-500">
                    No more workspaces.
                </div>
            @endif
        @endif
    </div>
    @include('livewire.workspace.partials.drawer')
    @include('livewire.workspace.partials.delete-modal')
    @include('livewire.workspace.partials.share-drawer')
    @include('livewire.workspace.partials.add-items-drawer')
    @include('livewire.workspace.partials.remove-items-drawer')
</div>
@script
    <script>
        $wire.on('copy-to-clipboard', (event) => {
            navigator.clipboard.writeText(event.text);
        });
    </script>
@endscript
