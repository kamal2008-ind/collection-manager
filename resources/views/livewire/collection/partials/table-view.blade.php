<div class="relative rounded-xl border bg-white overflow-visible">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left"></th>
                <th class="p-3 text-left w-[30%]">Collection</th>
                <th class="p-3 text-center w-[10%]">Owner</th>
                <th class="p-3 text-center w-[10%]">Workspaces</th>
                <th class="p-3 text-center w-[10%]">Movies</th>
                <th class="p-3 text-center w-[10%]">Books</th>
                <th class="p-3 text-center w-[15%]">Status</th>
                <th class="p-3 text-right w-[15%]">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($collections as $collection)
                @php
                    $isOwner = auth()->id() === $collection->user_id;
                    $collectionUrl = url('/u/' . $collection->user->username . '/collections/' . $collection->slug);
                @endphp
                <tr wire:key="collection-table-{{ $collection->id }}" class="border-t hover:bg-gray-50">
                    <td class="p-3">
                        @if ($isOwner)
                            <input type="checkbox" value="{{ $collection->id }}" wire:model.live="selected">
                        @endif
                    </td>
                    <td class="p-3 font-medium" title="{{ $collection->name }}">
                        {{ $collection->name }}
                    </td>
                    {{-- Owner --}}
                    <td class="p-3 text-center">
                        <x-owner-badge :userid="$collection->user_id" :username="$collection->user->username" :view="$view" />
                    </td>
                    {{-- Workspace --}}
                    <td class="p-3 text-center">
                        {{ $collection->workspaces_count ?? 0 }}
                    </td>
                    {{-- Movies --}}
                    <td class="p-3 text-center">
                        {{ $collection->movies_count ?? 0 }}
                    </td>
                    {{-- Books --}}
                    <td class="p-3 text-center">
                        {{ $collection->books_count ?? 0 }}
                    </td>
                    {{-- Status --}}
                    <td class="p-3">
                        <div class="flex items-center justify-center gap-3">
                            <x-status-badge :visibility="$collection->visibility" :shared="($collection->shares_count ?? 0) > 0" :view="$view" />

                            <x-card-footer-meta :visibility="$collection->visibility" :assetId="$collection->id" :isOwner="$isOwner" :likeCount="$collection->likes_count ?? 0"
                                :likedByUser="$collection->isLikedBy(auth()->user())" :shareCount="$collection->shares_count" :assetUrl="$collectionUrl" />
                        </div>
                    </td>

                    <td class="p-3">
                        <div class="flex justify-end items-center gap-3">
                            @if ($isOwner)
                                <button title="{{ $collection->is_favorite ? 'Remove Favorite' : 'Add Favorite' }}"
                                    wire:click="toggleFavorite({{ $collection->id }})">
                                    @if ($collection->is_favorite)
                                        ⭐
                                    @else
                                        <span class="text-2xl">☆</span>
                                    @endif
                                </button>
                                <button title="Edit" wire:click="editCollection({{ $collection->id }})">
                                    ✏️
                                </button>
                                <button title="Move to Trash" wire:click="confirmDelete({{ $collection->id }})">
                                    🗑️
                                </button>
                                <div class="relative" x-on:close-more-menu.window="activeMenu = null">
                                    <button type="button"
                                        @click.stop="activeMenu = activeMenu === 'collection-{{ $collection->id }}' ? null : 'collection-{{ $collection->id }}'"
                                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                                        ⋮
                                    </button>

                                    <div x-show="activeMenu === 'collection-{{ $collection->id }}'"
                                        @click.outside="activeMenu = null" x-transition
                                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">
                                        <div class="my-1 border-t"></div>
                                        <button type="button" wire:click="openAddItemsDrawer({{ $collection->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>➕</span>
                                            <span>Add Items</span>
                                        </button>
                                        <button type="button"
                                            wire:click="openRemoveItemsDrawer({{ $collection->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-red-50 text-red-600">
                                            <span>➖</span>
                                            <span>Remove Items</span>
                                        </button>
                                        <div class="my-1 border-t"></div>
                                        <button type="button" wire:click="openAttachToDrawer({{ $collection->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>📎</span>
                                            <span>Attach To Workspace</span>
                                        </button>
                                        <button type="button" wire:click="openDetachFromDrawer({{ $collection->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>⛓️‍💥</span>
                                            <span>Detach From Workspace</span>
                                        </button>
                                        <div class="my-1 border-t"></div>
                                        {{-- Duplicate --}}
                                        <button type="button" wire:click="duplicateCollection({{ $collection->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>📋</span>
                                            <span>Duplicate</span>
                                        </button>
                                        {{-- Copy Link --}}
                                        <button type="button" wire:click="copyCollectionUrl({{ $collection->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                                            <span>🔗</span>
                                            <span>Copy link</span>
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
                    <td colspan="8" class="p-6 text-center">
                        <x-empty-state icon="🎬" title="No collections found"
                            message="Try changing your search/filter/access mode or create a new collection." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
