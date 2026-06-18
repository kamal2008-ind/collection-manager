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
                <div class="relative">
                    <button type="button"
                        @click.stop="activeMenu = activeMenu === 'collection-{{ $collection->id }}' ? null : 'collection-{{ $collection->id }}'"
                        class="rounded p-1 hover:bg-gray-100" title="More actions">
                        ⋮
                    </button>

                    <div x-show="activeMenu === 'collection-{{ $collection->id }}'" @click.outside="activeMenu = null"
                        x-transition
                        class="absolute right-0 z-[9999] mt-2 w-56 overflow-hidden rounded-xl border bg-white shadow-lg">
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
    <div class="mt-4 space-y-2 text-sm">
        <div>
            Workspaces ({{ $collection->workspaces_count ?? 0 }})
        </div>
        <div>
            Movies (0)
        </div>
        <div>
            Books (0)
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 flex items-center justify-between text-sm">
        <div>
            @if ($collection->visibility === 'public')
                🌍 <span class="rounded bg-green-100 px-2 py-1 text-xs text-green-700">Public</span>
            @elseif (($collection->shares_count ?? 0) > 0)
                👥 <span class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-700">Shared</span>
            @else
                🔒 <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">Private</span>
            @endif

            <span class="text-xs text-gray-500" title="Owner">
                👤
                {{ $collection->user_id === auth()->id() ? 'Me' : '@' . $collection->user->username }}
            </span>
        </div>

        <div class="flex gap-2">
            <span title="Likes">
                ❤️ 0
            </span>
            @if ($collection->visibility === 'public')
                <button type="button" title="Copy Link" wire:click="copyShareLink({{ $collection->id }})">
                    🔗
                </button>
            @elseif($isOwner)
                <button type="button" title="Share privately with user(s)"
                    wire:click="openShareDrawer({{ $collection->id }})">
                    🤝 {{ $collection->shares_count }}
                </button>
            @else
                <span title="Shared count">
                    🤝 {{ $collection->shares_count ?? 0 }}
                </span>
            @endif
            <a href="{{ $collectionUrl }}" target="_blank" title="Open link">
                ↗️
            </a>
        </div>
    </div>
</div>
