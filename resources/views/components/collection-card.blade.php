@props(['collection', 'selected' => []])
<div
    class="
        rounded-xl
        p-4
        shadow-sm
        {{ in_array($collection->id, $selected ?? []) ? 'border-blue-500 ring-2 ring-blue-200' : 'border' }}
        bg-white
    ">
    @php
        $isOwner = auth()->id() === $collection->user_id;
        $collectionUrl = url('/u/' . $collection->user->username . '/collections/' . $collection->slug);
    @endphp
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
            @if ($isOwner)
                <input type="checkbox" value="{{ $collection->id }}" wire:model.live="selected"
                    class="rounded border-gray-400" />
            @endif
            <span title="{{ $collection->name }}"
                class="truncate max-w-[180px] font-medium hover:shadow-md hover:border-gray-300 transition">
                {{ $collection->name }}
            </span>
        </div>

        <div class="flex items-center gap-2">
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
                <button title="Move to trash" wire:click="confirmDelete({{ $collection->id }})">
                    🗑️
                </button>
                <div class="relative" x-on:close-more-menu.window="activeMenu = null">
                    <button type="button"
                        @click.stop="activeMenu = activeMenu === 'collection-{{ $collection->id }}' ? null : 'collection-{{ $collection->id }}'"
                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                        ⋮
                    </button>

                    <div x-show="activeMenu === 'collection-{{ $collection->id }}'" @click.outside="activeMenu = null"
                        x-transition
                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">
                        <div class="my-1 border-t"></div>
                        <button type="button" wire:click="openAddItemsDrawer({{ $collection->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50">
                            <span>➕</span>
                            <span>Add Items</span>
                        </button>
                        <button type="button" wire:click="openRemoveItemsDrawer({{ $collection->id }})"
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
    </div>

    {{-- Body --}}
    <div class="mt-4 space-y-2 text-sm items-center">
        <div>
            🏢 Workspaces ({{ $collection->workspaces_count ?? 0 }})
        </div>
        <div>
            🎬 Movies ({{ $collection->movies_count ?? 0 }})
        </div>
        <div>
            📘 Books (0)
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 flex items-center justify-between text-sm">
        <div>
            <x-status-badge :visibility="$collection->visibility" :shared="($collection->shares_count ?? 0) > 0"/>

            <x-owner-badge :userid="$collection->user_id" :username="$collection->user->username" />
        </div>

        <div class="flex gap-2">
            <x-card-footer-meta :visibility="$collection->visibility" :assetId="$collection->id" :isOwner="$isOwner"
                :shareCount="$collection->shares_count" :assetUrl="$collectionUrl" />
        </div>
    </div>
</div>
