<div class="p-6 space-y-6" x-data="{ activeMenu: null }">
    @if (session('success'))
        <div class="rounded-lg bg-green-100 p-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

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

        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search Workspaces..."
            class="w-full rounded-lg border border-gray-400 px-4 py-2 text-base focus:border-blue-600 focus:ring-blue-600">

    </div>

    {{-- Filters + View Switcher --}}
    <div class="flex items-center justify-between">

        <div class="flex gap-2">
            <x-ui.badge :active="$filter === 'recent'" wire:click="$set('filter', 'recent')">
                Recent
            </x-ui.badge>

            <x-ui.badge :active="$filter === 'favorites'" wire:click="$set('filter', 'favorites')">
                Favorites
            </x-ui.badge>
            <x-ui.badge :active="$filter === 'attached'">
                Attached
            </x-ui.badge>
            <x-ui.badge :active="$filter === 'unattached'">
                Unattached
            </x-ui.badge>
        </div>

        <div class="flex gap-2">
            <button title="Table" wire:click="setView('table')"
                class="text-xl {{ $view === 'table' ? 'text-blue-600 font-bold' : 'text-gray-600' }}">
                ☰
            </button>
            <button title="Card" wire:click="setView('card')"
                class="text-xl {{ $view === 'card' ? 'text-blue-600 font-bold' : 'text-gray-600' }}">
                ▣
            </button>
            <button title="Masonry" wire:click="setView('masonry')"
                class="text-xl {{ $view === 'masonry' ? 'text-blue-600 font-bold' : 'text-gray-600' }}">
                ▦
            </button>
        </div>
    </div>
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
    {{-- <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(300px,1fr))]">

        @forelse($workspaces as $workspace)
            <div wire:key="workspace-{{ $workspace->id }}"
                class="shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <x-workspace-card :workspace="$workspace" :selected="$selected" />
            </div>

        @empty

            <div>
                No workspaces found.
            </div>
        @endforelse

    </div> --}}

    @if ($view === 'table')

        <div class="relative rounded-xl border bg-white overflow-visible">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left"></th>

                        <th class="p-3 text-left w-[35%]">
                            Workspace
                        </th>

                        <th class="p-3 text-center w-[10%]">
                            Collections
                        </th>

                        <th class="p-3 text-center w-[10%]">
                            Movies
                        </th>

                        <th class="p-3 text-center w-[10%]">
                            Books
                        </th>

                        <th class="p-3 text-center w-[15%]">
                            Status
                        </th>

                        <th class="p-3 text-right w-[15%]">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($workspaces as $workspace)
                        @php
                            $isOwner = auth()->id() === $workspace->user_id;
                            $workspaceUrl = url('/u/' . $workspace->user->username . '/workspaces/' . $workspace->slug);
                        @endphp

                        <tr wire:key="workspace-table-{{ $workspace->id }}" class="border-t hover:bg-gray-50">

                            {{-- Select --}}
                            <td class="p-3">
                                @if ($isOwner)
                                    <input type="checkbox" value="{{ $workspace->id }}" wire:model.live="selected">
                                @endif
                            </td>

                            {{-- Workspace --}}
                            <td class="p-3 font-medium">
                                {{ $workspace->name }}
                            </td>

                            {{-- Collections --}}
                            <td class="p-3 text-center">
                                0
                            </td>

                            {{-- Movies --}}
                            <td class="p-3 text-center">
                                0
                            </td>

                            {{-- Books --}}
                            <td class="p-3 text-center">
                                0
                            </td>

                            {{-- Status --}}
                            <td class="p-3">
                                <div class="flex items-center justify-center gap-3">

                                    @if ($workspace->visibility === 'public')
                                        <span title="Public"> 🌍 </span>
                                    @elseif (($workspace->shares_count ?? 0) > 0)
                                        <span title="Shared"> 👥 </span>
                                    @else
                                        <span title="Private"> 🔒 </span>
                                    @endif

                                    <span title="Likes">
                                        ❤️ 0
                                    </span>

                                    @if ($workspace->visibility === 'public')
                                        <button type="button" title="Copy link"
                                            wire:click="copyShareLink({{ $workspace->id }})">
                                            🔗
                                        </button>
                                    @elseif($isOwner)
                                        <button type="button" title="Share privately with user(s)"
                                            wire:click="openShareDrawer({{ $workspace->id }})">
                                            🤝 {{ $workspace->shares_count }}
                                        </button>
                                    @else
                                        <span title="Shared count">
                                            🤝 {{ $workspace->shares_count ?? 0 }}
                                        </span>
                                    @endif
                                    <a href="{{ $workspaceUrl }}" target="_blank" title="Open link">
                                        ↗️
                                    </a>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="p-3">
                                <div class="flex justify-end items-center gap-3">
                                    @if ($isOwner)
                                        <button
                                            title="{{ $workspace->is_favorite ? 'Remove Favorite' : 'Add Favorite' }}"
                                            wire:click="toggleFavorite({{ $workspace->id }})">
                                            @if ($workspace->is_favorite)
                                                ⭐
                                            @else
                                                <span class="text-2xl">☆</span>
                                            @endif
                                        </button>
                                        <button title="Edit" wire:click="editWorkspace({{ $workspace->id }})">
                                            ✏️
                                        </button>
                                        <button title="Move to Trash" wire:click="confirmDelete({{ $workspace->id }})">
                                            🗑️
                                        </button>
                                        <div class="relative">
                                            <button
                                                @click.stop="activeMenu = activeMenu === {{ $workspace->id }} ? null : {{ $workspace->id }}"
                                                class="rounded
                                                p-1 hover:bg-gray-100"
                                                title="More actions">
                                                ⋮
                                            </button>

                                            <div x-show="activeMenu === {{ $workspace->id }}"
                                                @click.outside="activeMenu = null" x-transition
                                                class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">

                                                {{-- Duplicate --}}
                                                <button type="button"
                                                    wire:click="duplicateWorkspace({{ $workspace->id }})"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                                    <span>📋</span>
                                                    <span>Duplicate</span>
                                                </button>

                                                {{-- Copy Link --}}
                                                <button type="button"
                                                    wire:click="copyWorkspaceUrl({{ $workspace->id }})"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                                    <span>🔗</span>
                                                    <span>Copy link</span>
                                                </button>

                                                {{-- Statistics --}}
                                                <button type="button"
                                                    wire:click="workspaceStatistics({{ $workspace->id }})"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                                    <span>📊</span>
                                                    <span>Statistics</span>
                                                </button>

                                                {{-- Settings --}}
                                                <button type="button"
                                                    wire:click="workspaceSettings({{ $workspace->id }})"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                                    <span>⚙️</span>
                                                    <span>Settings</span>
                                                </button>

                                            </div>
                                        </div>
                                    @else
                                        <span class="rounded bg-blue-50 px-2 py-1 text-xs text-blue-700">
                                            View only
                                        </span>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center">
                                No workspaces found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($view === 'masonry')
        <div class="columns-1 md:columns-2 xl:columns-3 gap-4 space-y-4">
            @forelse($workspaces as $workspace)
                <div wire:key="workspace-masonry-{{ $workspace->id }}"
                    class="break-inside-avoid mb-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <x-workspace-card :workspace="$workspace" :selected="$selected" />
                </div>
            @empty
                <div>No workspaces found.</div>
            @endforelse
        </div>
    @else
        <div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
            @forelse($workspaces as $workspace)
                <div wire:key="workspace-card-{{ $workspace->id }}"
                    class="shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <x-workspace-card :workspace="$workspace" :selected="$selected" />
                </div>
            @empty
                <div>No workspaces found.</div>
            @endforelse
        </div>

    @endif
    {{-- Pagination --}}
    <div>

        {{ $workspaces->links() }}

    </div>
    @include('livewire.workspace.partials.drawer')
    @include('livewire.workspace.partials.delete-modal')
    @include('livewire.workspace.partials.share-drawer')
</div>
@script
    <script>
        $wire.on('copy-to-clipboard', (event) => {
            navigator.clipboard.writeText(event.text);
        });
    </script>
@endscript
