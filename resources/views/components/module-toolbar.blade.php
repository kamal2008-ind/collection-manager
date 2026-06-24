<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
            <x-ui.badge :active="$filter === 'recent'" wire:click="$set('filter', 'recent')">
                Recent
            </x-ui.badge>

            @if ($accessMode === 'owned')
                <x-ui.badge :active="$filter === 'favorites'" wire:click="$set('filter', 'favorites')">
                    Favorites
                </x-ui.badge>

                <x-ui.badge :active="$filter === 'attached'" wire:click="$set('filter', 'attached')">
                    Attached
                </x-ui.badge>

                <x-ui.badge :active="$filter === 'unattached'" wire:click="$set('filter', 'unattached')">
                    Unattached
                </x-ui.badge>
            @else
                <span title="Only available in Owned mode" class="cursor-not-allowed opacity-40">
                    <x-ui.badge :active="false">
                        Favorites
                    </x-ui.badge>
                </span>

                <span title="Only available in Owned mode" class="cursor-not-allowed opacity-40">
                    <x-ui.badge :active="false">
                        Attached
                    </x-ui.badge>
                </span>

                <span title="Only available in Owned mode" class="cursor-not-allowed opacity-40">
                    <x-ui.badge :active="false">
                        Unattached
                    </x-ui.badge>
                </span>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
            <div title="Access mode" class="flex items-center">
                <x-ui.badge :active="$accessMode === 'owned'" wire:click="setAccessMode('owned')">
                    👤 Owned
                </x-ui.badge>

                <x-ui.badge :active="$accessMode === 'shared'" wire:click="setAccessMode('shared')">
                    🤝 Shared
                </x-ui.badge>

                <x-ui.badge :active="$accessMode === 'public'" wire:click="setAccessMode('public')">
                    🌍 Public
                </x-ui.badge>
            </div>
            <div class="hidden md:flex h-6 w-px bg-gray-300"></div>
            <div title="Pagination mode" class="hidden md:flex lg:flex items-center">
                <x-ui.badge :active="$paginationMode === 'pages'" wire:click="setPaginationMode('pages')">
                    📄 Pages
                </x-ui.badge>
                <x-ui.badge :active="$paginationMode === 'lazy'" wire:click="setPaginationMode('lazy')">
                    ♾️ Infinite
                </x-ui.badge>
            </div>

            <div class="hidden md:flex h-6 w-px bg-gray-300"></div>

            <div title="View mode" class="hidden md:flex lg:flex items-center">
                <x-ui.badge :active="$view === 'table'" wire:click="setView('table')">
                    ☰ Table
                </x-ui.badge>
                <x-ui.badge :active="$view === 'card'" wire:click="setView('card')">
                    ▣ Card
                </x-ui.badge>
                <x-ui.badge :active="$view === 'masonry'" wire:click="setView('masonry')">
                    ▦ Masonry
                </x-ui.badge>
            </div>
        </div>
    </div>
