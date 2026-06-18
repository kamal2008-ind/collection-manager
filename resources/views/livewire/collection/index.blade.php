<div class="p-6 space-y-6" x-data="{ activeMenu: null }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">
            Collections
        </h1>

        <x-ui.button variant="primary" wire:click="openDrawer">
            + New Collection
        </x-ui.button>
    </div>

    <div>
        <x-ui.search-input model="search" placeholder="Search Collections..." />
    </div>

    <div class="flex items-center justify-between">
        <div class="flex gap-2">
            <x-ui.badge :active="$filter === 'recent'" wire:click="$set('filter', 'recent')">
                Recent
            </x-ui.badge>

            <x-ui.badge :active="$filter === 'favorites'" wire:click="$set('filter', 'favorites')">
                Favorites
            </x-ui.badge>

            <x-ui.badge :active="$filter === 'attached'" wire:click="$set('filter', 'attached')">
                Attached
            </x-ui.badge>

            <x-ui.badge :active="$filter === 'unattached'" wire:click="$set('filter', 'unattached')">
                Unattached
            </x-ui.badge>
        </div>

        <div class="flex items-center gap-6">
            <div title="Pagination mode" class="flex items-center gap-2 rounded-lg border bg-white p-1">
                <div class="flex gap-2">
                    <button title="Page pagination" wire:click="setPaginationMode('pages')"
                        class="rounded-md px-1 text-md {{ $paginationMode === 'pages' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-700' }}">
                        📄
                    </button>

                    <button title="Infinite scroll" wire:click="setPaginationMode('lazy')"
                        class="rounded-md px-1 text-md {{ $paginationMode === 'lazy' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-700' }}">
                        ♾️
                    </button>
                </div>
            </div>

            <div class="h-5 w-px bg-blue-300"></div>

            <div title="View mode" class="flex items-center gap-2 rounded-lg border bg-white p-1">
                <div class="flex gap-2">
                    <button title="Table view" wire:click="setView('table')"
                        class="rounded-md px-1 text-md {{ $view === 'table' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-700' }}">
                        ☰
                    </button>

                    <button title="Card view" wire:click="setView('card')"
                        class="rounded-md px-1 text-md {{ $view === 'card' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-700' }}">
                        ▣
                    </button>

                    <button title="Masonry view" wire:click="setView('masonry')"
                        class="rounded-md px-1 text-md {{ $view === 'masonry' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-700' }}">
                        ▦
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (count($selected))
        <div class="sticky top-20 z-30 flex items-center justify-between rounded-xl px-4 py-2 shadow-md"
            style="background: linear-gradient(to right, #f48fb1, #b388ff, #4298e1);">
            <span class="font-semibold text-white">
                {{ count($selected) }} collection(s) selected
            </span>

            <div class="flex gap-2">
                <x-ui.button variant="secondary" wire:click="bulkFavorite">
                    ⭐ Add to favorite
                </x-ui.button>

                <x-ui.button variant="secondary" wire:click="openBulkAttachToDrawer">
                    📎 Attach To
                </x-ui.button>

                <x-ui.button variant="danger" wire:click="confirmBulkDelete">
                    🗑 Move to trash
                </x-ui.button>
            </div>
        </div>
    @endif

    @if ($view === 'table')
        @include('livewire.collection.partials.table-view')
    @elseif($view === 'masonry')
        @include('livewire.collection.partials.masonry-view')
    @else
        @include('livewire.collection.partials.card-view')
    @endif

    <div>
        @if ($paginationMode === 'pages')
            {{ $collections->links() }}
        @else
            @if ($collections->hasMorePages())
                <div x-data x-intersect="$wire.loadMore()" class="flex justify-center py-6">
                    <div wire:loading wire:target="loadMore" class="flex items-center gap-2 text-sm text-gray-500">
                        <span
                            class="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-blue-600"></span>
                        Loading more collections...
                    </div>

                    <div wire:loading.remove wire:target="loadMore" class="text-sm text-gray-400">
                        Scroll to load more
                    </div>
                </div>
            @else
                <div class="py-6 text-center text-sm text-gray-500">
                    No more collections.
                </div>
            @endif
        @endif
    </div>

    @include('livewire.collection.partials.drawer')
    @include('livewire.collection.partials.delete-modal')
    @include('livewire.collection.partials.share-drawer')
    @include('livewire.collection.partials.attach-to-drawer')
    @include('livewire.collection.partials.detach-from-drawer')
</div>
@script
    <script>
        $wire.on('copy-to-clipboard', (event) => {
            navigator.clipboard.writeText(event.text);
        });
    </script>
@endscript
